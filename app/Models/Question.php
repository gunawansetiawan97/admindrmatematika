<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'question_text',
        'question_type',
        'correct_answer',
        'points',
        'order',
    ];

    protected $casts = [
        'points' => 'integer',
        'order' => 'integer',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(QuestionPackage::class, 'package_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('option_label');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function practiceAnswers(): HasMany
    {
        return $this->hasMany(PracticeAnswer::class);
    }

    public function isMultipleChoice(): bool
    {
        return $this->question_type === 'multiple_choice';
    }

    public function isEssay(): bool
    {
        return $this->question_type === 'essay';
    }

    public function checkAnswer(string $answer): bool
    {
        if (!$this->isMultipleChoice()) {
            return false;
        }
        return strtoupper(trim($answer)) === strtoupper(trim($this->correct_answer));
    }
}
