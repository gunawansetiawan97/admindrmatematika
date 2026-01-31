<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'duration_minutes',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
        'price',
        'is_free',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'is_free' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'package_id')->orderBy('order');
    }

    public function packageAttempts(): HasMany
    {
        return $this->hasMany(PackageAttempt::class, 'package_id');
    }

    public function practiceAttempts(): HasMany
    {
        return $this->hasMany(PracticeAttempt::class, 'package_id');
    }

    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->is_active
            && $now->gte($this->start_date)
            && $now->lte($this->end_date);
    }

    public function isPast(): bool
    {
        return !$this->is_active || Carbon::now()->gt($this->end_date);
    }

    public function isUpcoming(): bool
    {
        return $this->is_active && Carbon::now()->lt($this->start_date);
    }

    public function getTotalPoints(): int
    {
        return $this->questions()->sum('points');
    }

    public function getQuestionCount(): int
    {
        return $this->questions()->count();
    }

    public function userPackages(): HasMany
    {
        return $this->hasMany(UserPackage::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_free', false);
    }
}
