<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomActivity;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ClassroomService;
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

        return view('admin.classrooms.show', compact('classroom', 'members', 'activities', 'availableStudents'));
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

    public function storeActivity(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:youtube,link,text,announcement'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_pinned' => ['boolean'],
            'meeting_number' => ['nullable', 'integer', 'min:1'],
        ], [
            'type.required' => 'Pilih jenis aktivitas.',
            'title.required' => 'Judul aktivitas wajib diisi.',
            'content.required' => 'Konten aktivitas wajib diisi.',
        ]);

        $admin = Auth::guard('admin')->user();
        $this->classroomService->createActivity($classroom, $admin, [
            'type' => $validated['type'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_pinned' => $request->boolean('is_pinned'),
            'meeting_number' => $validated['meeting_number'] ?? null,
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
