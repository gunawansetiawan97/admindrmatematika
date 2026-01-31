<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\PackageAttempt;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class GradingService
{
    public function gradeEssay(Answer $answer, float $score): Answer
    {
        return DB::transaction(function () use ($answer, $score) {
            $maxScore = $answer->question->points;
            $score = min($score, $maxScore);
            $score = max(0, $score);

            $answer->update([
                'score' => $score,
            ]);

            $this->recalculateAttemptScore($answer->attempt);

            return $answer;
        });
    }

    public function recalculateAttemptScore(PackageAttempt $attempt): float
    {
        $totalScore = 0;

        foreach ($attempt->answers as $answer) {
            if ($answer->question->isMultipleChoice() && $answer->is_correct) {
                $totalScore += $answer->question->points;
            } elseif ($answer->question->isEssay() && $answer->score !== null) {
                $totalScore += $answer->score;
            }
        }

        $attempt->update(['score' => $totalScore]);

        return $totalScore;
    }

    public function getAttemptStatistics(PackageAttempt $attempt): array
    {
        $package = $attempt->package;
        $totalQuestions = $package->getQuestionCount();
        $answeredQuestions = $attempt->answers()->whereNotNull('answered_at')->count();
        $totalPoints = $package->getTotalPoints();

        $multipleChoiceCorrect = 0;
        $multipleChoiceTotal = 0;
        $essayGraded = 0;
        $essayTotal = 0;

        foreach ($attempt->answers as $answer) {
            if ($answer->question->isMultipleChoice()) {
                $multipleChoiceTotal++;
                if ($answer->is_correct) {
                    $multipleChoiceCorrect++;
                }
            } else {
                $essayTotal++;
                if ($answer->score !== null) {
                    $essayGraded++;
                }
            }
        }

        return [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'total_points' => $totalPoints,
            'score' => $attempt->score ?? 0,
            'percentage' => $totalPoints > 0 ? round(($attempt->score ?? 0) / $totalPoints * 100, 2) : 0,
            'multiple_choice' => [
                'total' => $multipleChoiceTotal,
                'correct' => $multipleChoiceCorrect,
            ],
            'essay' => [
                'total' => $essayTotal,
                'graded' => $essayGraded,
            ],
            'time_spent' => $this->calculateTotalTimeSpent($attempt),
        ];
    }

    public function calculateTotalTimeSpent(PackageAttempt $attempt): int
    {
        $totalSeconds = 0;

        foreach ($attempt->answers as $answer) {
            $timeSpent = $answer->getTimeSpentSeconds();
            if ($timeSpent !== null) {
                $totalSeconds += $timeSpent;
            }
        }

        return $totalSeconds;
    }

    public function getQuestionTimeAnalysis(PackageAttempt $attempt): array
    {
        $analysis = [];

        foreach ($attempt->answers as $answer) {
            $analysis[] = [
                'question_id' => $answer->question_id,
                'question_order' => $answer->question->order,
                'question_type' => $answer->question->question_type,
                'opened_at' => $answer->opened_at,
                'answered_at' => $answer->answered_at,
                'time_spent_seconds' => $answer->getTimeSpentSeconds(),
                'is_correct' => $answer->is_correct,
                'score' => $answer->score,
            ];
        }

        return $analysis;
    }
}
