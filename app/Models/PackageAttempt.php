<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'started_at',
        'finished_at',
        'score',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(QuestionPackage::class, 'package_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'attempt_id');
    }

    public function isFinished(): bool
    {
        return $this->finished_at !== null;
    }

    public function isExpired(): bool
    {
        if ($this->isFinished()) {
            return true;
        }

        $endTime = $this->started_at->addMinutes($this->package->duration_minutes);
        return Carbon::now()->gt($endTime);
    }

    public function getRemainingSeconds(): int
    {
        if ($this->isFinished()) {
            return 0;
        }

        $endTime = $this->started_at->addMinutes($this->package->duration_minutes);
        $remaining = Carbon::now()->diffInSeconds($endTime, false);

        return max(0, $remaining);
    }

    public function getEndTime(): Carbon
    {
        return $this->started_at->addMinutes($this->package->duration_minutes);
    }

    public function getAnswerForQuestion(Question $question): ?Answer
    {
        return $this->answers()->where('question_id', $question->id)->first();
    }

    public function calculateScore(): float
    {
        $totalScore = 0;

        foreach ($this->answers as $answer) {
            if ($answer->question->isMultipleChoice() && $answer->is_correct) {
                $totalScore += $answer->question->points;
            } elseif ($answer->question->isEssay() && $answer->score !== null) {
                $totalScore += $answer->score;
            }
        }

        return $totalScore;
    }
}
