<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomActivity;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ClassroomService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    protected ClassroomService $classroomService;

    public function __construct(ClassroomService $classroomService)
    {
        $this->classroomService = $classroomService;
    }

    public function index(Request $request)
    {
        $query = Classroom::with('subscription')
            ->withCount(['members', 'activities']);

        if ($request->filled('subscription_id')) {
            $query->where('subscription_id', $request->subscription_id);
        }

        $classrooms = $query->orderBy('created_at', 'desc')->paginate(10);
        $subscriptions = Subscription::active()->get();

        // Hitung siswa belum terdaftar dan anggota aktif per kelas
        $classrooms->each(function ($classroom) {
            $existingMemberIds = $classroom->members()->pluck('user_id');

            $classroom->unregistered_count = User::whereHas('userSubscriptions', function ($q) use ($classroom) {
                $q->where('subscription_id', $classroom->subscription_id)
                  ->where('status', 'active')
                  ->where('expires_at', '>', now());
            })
            ->whereNotIn('id', $existingMemberIds)
            ->count();

            // Anggota yang subscriptionnya masih aktif
            $classroom->active_members_count = \App\Models\UserSubscription::where('subscription_id', $classroom->subscription_id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->whereIn('user_id', $existingMemberIds)
                ->count();
        });

        return view('admin.classrooms.index', compact('classrooms', 'subscriptions'));
    }

    public function create()
    {
        $subscriptions = Subscription::active()->get();
        return view('admin.classrooms.create', compact('subscriptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subscription_id' => ['required', 'exists:subscriptions,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ], [
            'subscription_id.required' => 'Pilih kelas terlebih dahulu.',
            'name.required' => 'Nama kelas wajib diisi.',
        ]);

        $classroom = Classroom::create([
            'subscription_id' => $validated['subscription_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.classrooms.show', $classroom)
            ->with('success', 'Kelas berhasil dibuat.');
    }

    public function show(Classroom $classroom)
    {
        $classroom->load('subscription');
        $members = $this->classroomService->getClassroomMembers($classroom);
        $activities = $this->classroomService->getClassroomActivities($classroom);
        $availableStudents = $this->classroomService->getAvailableStudents($classroom);

        // Hitung sisa pertemuan
        $totalMeetings = $classroom->subscription->meetings_count;
        $doneMeetings = $classroom->activities()
            ->whereNotNull('meeting_date')
            ->where('meeting_date', '<=', now()->toDateString())
            ->distinct('meeting_date')
            ->count('meeting_date');
        $remainingMeetings = $totalMeetings ? max(0, $totalMeetings - $doneMeetings) : null;

        // Kelas lain dengan subscription yang sama (untuk fitur pindah siswa)
        $otherClassrooms = Classroom::where('subscription_id', $classroom->subscription_id)
            ->where('id', '!=', $classroom->id)
            ->where('is_active', true)
            ->get();

        return view('admin.classrooms.show', compact(
            'classroom', 'members', 'activities', 'availableStudents',
            'totalMeetings', 'doneMeetings', 'remainingMeetings', 'otherClassrooms'
        ));
    }

    public function edit(Classroom $classroom)
    {
        $subscriptions = Subscription::active()->get();
        return view('admin.classrooms.edit', compact('classroom', 'subscriptions'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'subscription_id' => ['required', 'exists:subscriptions,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $classroom->update([
            'subscription_id' => $validated['subscription_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.classrooms.show', $classroom)
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return redirect()->route('admin.classrooms.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }

    public function addMember(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Check if user is an active subscriber
        $hasSubscription = $user->userSubscriptions()
            ->where('subscription_id', $classroom->subscription_id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();

        if (!$hasSubscription) {
            return back()->withErrors(['user_id' => 'Murid tidak memiliki kelas aktif untuk subscription ini.']);
        }

        // Check if already a member
        if ($classroom->hasMember($user)) {
            return back()->withErrors(['user_id' => 'Murid sudah menjadi anggota kelas ini.']);
        }

        // Check if user is already in another classroom with the same subscription
        if ($this->classroomService->userAlreadyInSubscriptionClassroom($user, $classroom)) {
            return back()->withErrors(['user_id' => 'Murid sudah terdaftar di kelas lain dalam kelas ini.']);
        }

        $admin = Auth::guard('admin')->user();
        $this->classroomService->addMember($classroom, $user, $admin);

        return back()->with('success', 'Murid berhasil ditambahkan ke kelas.');
    }

    public function removeMember(Classroom $classroom, User $user)
    {
        $this->classroomService->removeMember($classroom, $user);

        return back()->with('success', 'Murid berhasil dihapus dari kelas.');
    }

    public function moveMember(Request $request, Classroom $classroom, User $user)
    {
        $validated = $request->validate([
            'target_classroom_id' => ['required', 'exists:classrooms,id', 'different:' . $classroom->id],
            'starts_at'           => ['required', 'date'],
        ], [
            'target_classroom_id.required' => 'Pilih kelas tujuan.',
            'target_classroom_id.different' => 'Kelas tujuan tidak boleh sama dengan kelas saat ini.',
            'starts_at.required'           => 'Tanggal mulai wajib diisi.',
        ]);

        $targetClassroom = Classroom::findOrFail($validated['target_classroom_id']);
        $admin = Auth::guard('admin')->user();

        $this->classroomService->moveMember(
            $classroom,
            $targetClassroom,
            $user,
            $admin,
            Carbon::parse($validated['starts_at'])
        );

        return redirect()->route('admin.classrooms.show', $targetClassroom)
            ->with('success', "{$user->name} berhasil dipindahkan ke {$targetClassroom->name}.");
    }

    public function storeActivity(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:youtube,link,text,announcement'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_pinned' => ['boolean'],
            'meeting_date' => ['nullable', 'date'],
        ], [
            'type.required' => 'Pilih jenis aktivitas.',
            'title.required' => 'Judul aktivitas wajib diisi.',
            'content.required' => 'Konten aktivitas wajib diisi.',
        ]);

        // Validate that the meeting date falls on an allowed day
        if (!empty($validated['meeting_date']) && $classroom->subscription->days && count($classroom->subscription->days) > 0) {
            $dayMap = [
                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            ];
            $selectedDay = $dayMap[date('l', strtotime($validated['meeting_date']))];
            if (!in_array($selectedDay, $classroom->subscription->days)) {
                return back()->withErrors(['meeting_date' => 'Tanggal harus jatuh pada hari: ' . implode(', ', $classroom->subscription->days) . '. Anda memilih hari ' . $selectedDay . '.'])
                    ->withInput();
            }
        }

        $admin = Auth::guard('admin')->user();
        $this->classroomService->createActivity($classroom, $admin, [
            'type' => $validated['type'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_pinned' => $request->boolean('is_pinned'),
            'meeting_date' => $validated['meeting_date'] ?? null,
        ]);

        return back()->with('success', 'Aktivitas berhasil ditambahkan.');
    }

    public function destroyActivity(ClassroomActivity $activity)
    {
        $classroom = $activity->classroom;
        $activity->delete();

        return redirect()->route('admin.classrooms.show', $classroom)
            ->with('success', 'Aktivitas berhasil dihapus.');
    }

    public function togglePinActivity(ClassroomActivity $activity)
    {
        $this->classroomService->togglePin($activity);

        $message = $activity->is_pinned ? 'Aktivitas berhasil di-pin.' : 'Pin aktivitas berhasil dihapus.';
        return back()->with('success', $message);
    }
}
