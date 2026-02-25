<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Services\ClassroomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulkActivityController extends Controller
{
    protected ClassroomService $classroomService;

    public function __construct(ClassroomService $classroomService)
    {
        $this->classroomService = $classroomService;
    }

    public function index()
    {
        $classrooms = Classroom::with('subscription')
            ->active()
            ->orderBy('name')
            ->get()
            ->groupBy(fn($c) => $c->subscription->name);

        return view('admin.activities.bulk', compact('classrooms'));
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

        $admin = Auth::guard('admin')->user();
        $count = 0;

        foreach ($validated['classroom_ids'] as $classroomId) {
            $classroom   = Classroom::find($classroomId);
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
