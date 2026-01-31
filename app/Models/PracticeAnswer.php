<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'practice_attempt_id',
        'question_id',
        'answer_text',
        'opened_at',
        'answered_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'answered_at' => 'datetime',
    ];

    public function practiceAttempt(): BelongsTo
    {
        return $this->belongsTo(PracticeAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function getTimeSpentSeconds(): ?int
    {
        if (!$this->opened_at || !$this->answered_at) {
            return null;
        }

        return $this->opened_at->diffInSeconds($this->answered_at);
    }
}
