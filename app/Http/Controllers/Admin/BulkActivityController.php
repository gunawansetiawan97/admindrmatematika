<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Services\ClassroomService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulkActivityController extends Controller
{
    protected ClassroomService $classroomService;

    public function __construct(ClassroomService $classroomService)
    {
        $this->classroomService = $classroomService;
    }

    // Map nama hari Indonesia → nomor hari (0=Minggu … 6=Sabtu), sama dgn Carbon & JS getDay()
    private const DAY_MAP = [
        'Minggu'  => 0,
        'Senin'   => 1,
        'Selasa'  => 2,
        'Rabu'    => 3,
        'Kamis'   => 4,
        'Jumat'   => 5,
        'Sabtu'   => 6,
    ];

    public function index()
    {
        $classrooms = Classroom::with('subscription')
            ->active()
            ->orderBy('name')
            ->get()
            ->groupBy(fn($c) => $c->subscription->name);

        // Map classroom_id → array hari yang diizinkan (untuk validasi frontend)
        $classroomDays = $classrooms->flatten()
            ->mapWithKeys(fn($c) => [$c->id => $c->subscription->days ?? []]);

        return view('admin.activities.bulk', compact('classrooms', 'classroomDays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'            => ['required', 'in:youtube,link,text,announcement'],
            'title'           => ['required', 'string', 'max:255'],
            'content'         => ['required', 'string'],
            'classroom_ids'   => ['required', 'array', 'min:1'],
            'classroom_ids.*' => ['exists:classrooms,id'],
            'meeting_dates.*' => ['nullable', 'date'],
        ], [
            'classroom_ids.required' => 'Pilih minimal 1 kelas.',
            'classroom_ids.min'      => 'Pilih minimal 1 kelas.',
            'type.required'          => 'Pilih jenis aktivitas.',
            'title.required'         => 'Judul wajib diisi.',
            'content.required'       => 'Konten wajib diisi.',
        ]);

        // Validasi hari sebelum menyimpan apapun
        $dayNames    = array_flip(self::DAY_MAP); // nomor → nama hari
        $dayErrors   = [];
        $classrooms  = Classroom::with('subscription')
            ->whereIn('id', $validated['classroom_ids'])
            ->get()
            ->keyBy('id');

        foreach ($validated['classroom_ids'] as $classroomId) {
            $meetingDate = $request->input("meeting_dates.{$classroomId}") ?: null;
            if (! $meetingDate) {
                continue;
            }

            $classroom   = $classrooms[$classroomId] ?? null;
            $allowedDays = $classroom?->subscription?->days ?? [];

            if (empty($allowedDays)) {
                continue; // paket tidak punya batasan hari
            }

            $selectedDayNum  = Carbon::parse($meetingDate)->dayOfWeek; // 0=Minggu
            $selectedDayName = $dayNames[$selectedDayNum];

            if (! in_array($selectedDayName, $allowedDays)) {
                $dayErrors[] = "Kelas \"{$classroom->name}\": tanggal {$meetingDate} ({$selectedDayName}) bukan hari jadwal paket ini (" . implode(', ', $allowedDays) . ").";
            }
        }

        if (! empty($dayErrors)) {
            return back()
                ->withErrors($dayErrors)
                ->withInput();
        }

        $admin = Auth::guard('admin')->user();
        $count = 0;

        foreach ($validated['classroom_ids'] as $classroomId) {
            $classroom   = $classrooms[$classroomId];
            $meetingDate = $request->input("meeting_dates.{$classroomId}") ?: null;

            $this->classroomService->createActivity($classroom, $admin, [
                'type'         => $validated['type'],
                'title'        => $validated['title'],
                'content'      => $validated['content'],
                'is_pinned'    => $request->boolean('is_pinned'),
                'meeting_date' => $meetingDate,
            ]);
            $count++;
        }

        return back()->with('success', "Aktivitas berhasil ditambahkan ke {$count} kelas.");
    }
}
