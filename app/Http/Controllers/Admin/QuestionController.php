<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function create(QuestionPackage $package)
    {
        $nextOrder = $package->questions()->max('order') + 1;
        return view('admin.questions.create', compact('package', 'nextOrder'));
    }

    public function store(Request $request, QuestionPackage $package)
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:multiple_choice,essay'],
            'points' => ['required', 'integer', 'min:1'],
            'order' => ['required', 'integer', 'min:0'],
            'correct_answer' => ['required_if:question_type,multiple_choice', 'nullable', 'string', 'max:1'],
            'options' => ['required_if:question_type,multiple_choice', 'array'],
            'options.*.label' => ['required_with:options', 'string', 'max:1'],
            'options.*.text' => ['required_with:options', 'string'],
        ], [
            'question_text.required' => 'Teks soal wajib diisi.',
            'question_type.required' => 'Tipe soal wajib dipilih.',
            'points.required' => 'Poin wajib diisi.',
            'points.min' => 'Poin minimal 1.',
            'correct_answer.required_if' => 'Jawaban benar wajib diisi untuk soal pilihan ganda.',
            'options.required_if' => 'Opsi jawaban wajib diisi untuk soal pilihan ganda.',
        ]);

        DB::transaction(function () use ($validated, $package, $request) {
            $question = Question::create([
                'package_id' => $package->id,
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'correct_answer' => $validated['correct_answer'] ?? null,
                'points' => $validated['points'],
                'order' => $validated['order'],
            ]);

            if ($validated['question_type'] === 'multiple_choice' && !empty($request->options)) {
                foreach ($request->options as $option) {
                    if (!empty($option['label']) && !empty($option['text'])) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_label' => strtoupper($option['label']),
                            'option_text' => $option['text'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.packages.show', $package)
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit(QuestionPackage $package, Question $question)
    {
        $question->load('options');
        return view('admin.questions.edit', compact('package', 'question'));
    }

    public function update(Request $request, QuestionPackage $package, Question $question)
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:multiple_choice,essay'],
            'points' => ['required', 'integer', 'min:1'],
            'order' => ['required', 'integer', 'min:0'],
            'correct_answer' => ['required_if:question_type,multiple_choice', 'nullable', 'string', 'max:1'],
            'options' => ['required_if:question_type,multiple_choice', 'array'],
            'options.*.label' => ['required_with:options', 'string', 'max:1'],
            'options.*.text' => ['required_with:options', 'string'],
        ]);

        DB::transaction(function () use ($validated, $question, $request) {
            $question->update([
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'correct_answer' => $validated['correct_answer'] ?? null,
                'points' => $validated['points'],
                'order' => $validated['order'],
            ]);

            $question->options()->delete();

            if ($validated['question_type'] === 'multiple_choice' && !empty($request->options)) {
                foreach ($request->options as $option) {
                    if (!empty($option['label']) && !empty($option['text'])) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_label' => strtoupper($option['label']),
                            'option_text' => $option['text'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.packages.show', $package)
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(QuestionPackage $package, Question $question)
    {
        $question->delete();

        return redirect()->route('admin.packages.show', $package)
            ->with('success', 'Soal berhasil dihapus.');
    }
}
