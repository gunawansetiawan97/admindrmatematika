<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\PackageAttempt;
use App\Models\PracticeAnswer;
use App\Models\PracticeAttempt;
use App\Models\Question;
use App\Models\QuestionPackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExamService
{
    public function canStartExam(User $user, QuestionPackage $package): array
    {
        if (!$package->isActive()) {
            return ['can_start' => false, 'reason' => 'Paket soal tidak aktif atau sudah berakhir.'];
        }

        if ($user->hasAttempted($package)) {
            $attempt = $user->packageAttempts()->where('package_id', $package->id)->first();
            if ($attempt->isFinished()) {
                return ['can_start' => false, 'reason' => 'Anda sudah menyelesaikan paket soal ini.'];
            }
            if ($attempt->isExpired()) {
                $this->finishAttempt($attempt);
                return ['can_start' => false, 'reason' => 'Waktu pengerjaan sudah habis.'];
            }
            return ['can_start' => true, 'attempt' => $attempt, 'is_continue' => true];
        }

        return ['can_start' => true, 'is_continue' => false];
    }

    public function startExam(User $user, QuestionPackage $package): PackageAttempt
    {
        return DB::transaction(function () use ($user, $package) {
            return PackageAttempt::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'started_at' => Carbon::now(),
            ]);
        });
    }

    public function openQuestion(PackageAttempt $attempt, Question $question): Answer
    {
        return DB::transaction(function () use ($attempt, $question) {
            $answer = $attempt->answers()->where('question_id', $question->id)->first();

            if (!$answer) {
                $answer = Answer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'opened_at' => Carbon::now(),
                ]);
            } elseif (!$answer->opened_at) {
                $answer->update(['opened_at' => Carbon::now()]);
            }

            return $answer;
        });
    }

    public function submitAnswer(PackageAttempt $attempt, Question $question, string $answerText): Answer
    {
        return DB::transaction(function () use ($attempt, $question, $answerText) {
            $answer = $attempt->answers()->where('question_id', $question->id)->first();

            if (!$answer) {
                $answer = Answer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'opened_at' => Carbon::now(),
                ]);
            }

            $isCorrect = null;
            if ($question->isMultipleChoice()) {
                $isCorrect = $question->checkAnswer($answerText);
            }

            $answer->update([
                'answer_text' => $answerText,
                'answered_at' => Carbon::now(),
                'is_correct' => $isCorrect,
            ]);

            return $answer;
        });
    }

    public function finishAttempt(PackageAttempt $attempt): PackageAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $score = $attempt->calculateScore();

            $attempt->update([
                'finished_at' => Carbon::now(),
                'score' => $score,
            ]);

            return $attempt;
        });
    }

    public function canStartPractice(User $user, QuestionPackage $package): array
    {
        if (!$package->isPast()) {
            return ['can_start' => false, 'reason' => 'Paket soal masih aktif. Gunakan mode ujian.'];
        }

        return ['can_start' => true];
    }

    public function startPractice(User $user, QuestionPackage $package): PracticeAttempt
    {
        return DB::transaction(function () use ($user, $package) {
            return PracticeAttempt::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'started_at' => Carbon::now(),
            ]);
        });
    }

    public function openPracticeQuestion(PracticeAttempt $attempt, Question $question): PracticeAnswer
    {
        return DB::transaction(function () use ($attempt, $question) {
            $answer = $attempt->answers()->where('question_id', $question->id)->first();

            if (!$answer) {
                $answer = PracticeAnswer::create([
                    'practice_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'opened_at' => Carbon::now(),
                ]);
            } elseif (!$answer->opened_at) {
                $answer->update(['opened_at' => Carbon::now()]);
            }

            return $answer;
        });
    }

    public function submitPracticeAnswer(PracticeAttempt $attempt, Question $question, string $answerText): PracticeAnswer
    {
        return DB::transaction(function () use ($attempt, $question, $answerText) {
            $answer = $attempt->answers()->where('question_id', $question->id)->first();

            if (!$answer) {
                $answer = PracticeAnswer::create([
                    'practice_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'opened_at' => Carbon::now(),
                ]);
            }

            $answer->update([
                'answer_text' => $answerText,
                'answered_at' => Carbon::now(),
            ]);

            return $answer;
        });
    }

    public function finishPractice(PracticeAttempt $attempt): PracticeAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $attempt->update([
                'finished_at' => Carbon::now(),
            ]);

            return $attempt;
        });
    }
}
