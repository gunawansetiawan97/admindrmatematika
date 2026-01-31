<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\PackageAttempt;
use App\Models\QuestionPackage;
use App\Services\GradingService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    protected GradingService $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    public function index(QuestionPackage $package)
    {
        $attempts = $package->packageAttempts()
            ->with('user')
            ->orderBy('score', 'desc')
            ->paginate(20);

        return view('admin.results.index', compact('package', 'attempts'));
    }

    public function show(PackageAttempt $attempt)
    {
        $attempt->load(['user', 'package', 'answers.question.options']);

        $statistics = $this->gradingService->getAttemptStatistics($attempt);
        $timeAnalysis = $this->gradingService->getQuestionTimeAnalysis($attempt);

        return view('admin.results.show', compact('attempt', 'statistics', 'timeAnalysis'));
    }

    public function gradeEssay(Request $request, Answer $answer)
    {
        $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:' . $answer->question->points],
        ], [
            'score.required' => 'Nilai wajib diisi.',
            'score.min' => 'Nilai tidak boleh negatif.',
            'score.max' => 'Nilai tidak boleh lebih dari ' . $answer->question->points . ' poin.',
        ]);

        $this->gradingService->gradeEssay($answer, $request->score);

        return back()->with('success', 'Nilai essay berhasil disimpan.');
    }
}
