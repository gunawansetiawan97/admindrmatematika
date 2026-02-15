<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'classroom_id',
        'admin_id',
        'type',
        'title',
        'content',
        'is_pinned',
        'meeting_date',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'meeting_date' => 'date',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if ($this->type !== 'youtube') {
            return null;
        }

        $url = $this->content;

        // Handle various YouTube URL formats
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return 'https://www.youtube.com/embed/' . $matches[1];
            }
        }

        return null;
    }

    public function getYoutubeVideoIdAttribute(): ?string
    {
        if ($this->type !== 'youtube') {
            return null;
        }

        $url = $this->content;

        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/',
            '/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'youtube' => 'play-circle',
            'link' => 'link',
            'text' => 'document-text',
            'announcement' => 'speakerphone',
            default => 'document',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'youtube' => 'Video YouTube',
            'link' => 'Link',
            'text' => 'Materi',
            'announcement' => 'Pengumuman',
            default => 'Lainnya',
        };
    }
}
