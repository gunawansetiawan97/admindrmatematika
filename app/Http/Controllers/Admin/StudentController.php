<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('school', 'like', "%{$search}%");
            });
        }

        $students = $query->withCount(['packageAttempts', 'practiceAttempts'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.students.index', compact('students'));
    }

    public function show(User $student)
    {
        $student->load(['packageAttempts.package', 'practiceAttempts.package']);

        $attempts = $student->packageAttempts()
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.students.show', compact('student', 'attempts'));
    }
}
