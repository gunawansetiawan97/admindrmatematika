<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'birth_date',
        'city',
        'phone',
        'school',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
    ];

    public function packageAttempts(): HasMany
    {
        return $this->hasMany(PackageAttempt::class);
    }

    public function practiceAttempts(): HasMany
    {
        return $this->hasMany(PracticeAttempt::class);
    }

    public function cart(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function userPackages(): HasMany
    {
        return $this->hasMany(UserPackage::class);
    }

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_members')
            ->withPivot(['added_by', 'joined_at'])
            ->withTimestamps();
    }

    public function classroomMemberships(): HasMany
    {
        return $this->hasMany(ClassroomMember::class);
    }

    public function hasAccessToPackage(QuestionPackage $package): bool
    {
        if ($package->is_free) {
            return true;
        }

        // Check if user has purchased this package
        if ($this->userPackages()->where('question_package_id', $package->id)->exists()) {
            $userPackage = $this->userPackages()->where('question_package_id', $package->id)->first();
            return $userPackage->isActive();
        }

        // Check if user has active subscription
        return $this->userSubscriptions()->active()->exists();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->userSubscriptions()->active()->exists();
    }

    public function hasAttempted(QuestionPackage $package): bool
    {
        return $this->packageAttempts()->where('package_id', $package->id)->exists();
    }

    public function getActiveAttempt(QuestionPackage $package): ?PackageAttempt
    {
        return $this->packageAttempts()
            ->where('package_id', $package->id)
            ->whereNull('finished_at')
            ->first();
    }
}
