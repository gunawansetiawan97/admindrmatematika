<?php

namespace App\Http\Controllers\Admin;

use App\Exports\QuestionTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\QuestionPackageImport;
use App\Models\QuestionPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PackageController extends Controller
{
    public function index()
    {
        $packages = QuestionPackage::with('creator')
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_free' => ['boolean'],
        ], [
            'title.required' => 'Judul paket wajib diisi.',
            'duration_minutes.required' => 'Durasi wajib diisi.',
            'duration_minutes.min' => 'Durasi minimal 1 menit.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        $package = QuestionPackage::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $request->boolean('is_active', true),
            'price' => $validated['price'] ?? 0,
            'is_free' => $request->boolean('is_free', true),
            'created_by' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.packages.show', $package)
            ->with('success', 'Paket soal berhasil dibuat.');
    }

    public function show(QuestionPackage $package)
    {
        $package->load(['questions.options', 'creator']);
        $package->loadCount(['packageAttempts', 'practiceAttempts']);

        return view('admin.packages.show', compact('package'));
    }

    public function edit(QuestionPackage $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, QuestionPackage $package)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_free' => ['boolean'],
        ]);

        $package->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $request->boolean('is_active'),
            'price' => $validated['price'] ?? 0,
            'is_free' => $request->boolean('is_free'),
        ]);

        return redirect()->route('admin.packages.show', $package)
            ->with('success', 'Paket soal berhasil diperbarui.');
    }

    public function destroy(QuestionPackage $package)
    {
        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Paket soal berhasil dihapus.');
    }

    public function import()
    {
        return view('admin.packages.import');
    }

    public function processImport(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'title.required' => 'Judul paket wajib diisi.',
            'duration_minutes.required' => 'Durasi wajib diisi.',
            'duration_minutes.min' => 'Durasi minimal 1 menit.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        // Create the package first
        $package = QuestionPackage::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::guard('admin')->id(),
        ]);

        // Import questions from Excel
        $import = new QuestionPackageImport($package);
        Excel::import($import, $request->file('file'));

        // Check for errors
        if ($import->hasErrors()) {
            // Delete the package if import failed
            $package->delete();

            return redirect()->back()
                ->withInput()
                ->withErrors(['file' => $import->getErrors()]);
        }

        // Check if any questions were imported
        if ($import->getImportedCount() === 0) {
            $package->delete();

            return redirect()->back()
                ->withInput()
                ->withErrors(['file' => 'Tidak ada soal yang berhasil diimpor. Pastikan format file sesuai template.']);
        }

        return redirect()->route('admin.packages.show', $package)
            ->with('success', "Paket soal berhasil dibuat dengan {$import->getImportedCount()} soal.");
    }

    public function downloadTemplate()
    {
        return Excel::download(new QuestionTemplateExport(), 'template_soal.xlsx');
    }
}
