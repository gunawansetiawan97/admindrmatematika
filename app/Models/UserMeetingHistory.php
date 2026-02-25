<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMeetingHistory extends Model
{
    protected $fillable = [
        'user_id',
        'classroom_id',
        'classroom_activity_id',
        'meeting_date',
    ];

    protected $casts = [
        'meeting_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function classroomActivity(): BelongsTo
    {
        return $this->belongsTo(ClassroomActivity::class);
    }
}
