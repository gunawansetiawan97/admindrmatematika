<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Collection;

class SubscriptionService
{
    public function getActiveSubscriptions(): Collection
    {
        return Subscription::active()->get();
    }

    public function getUserActiveSubscription(User $user): ?UserSubscription
    {
        return $user->userSubscriptions()
            ->active()
            ->with('subscription')
            ->first();
    }

    public function getUserActiveSubscriptions(User $user): Collection
    {
        return $user->userSubscriptions()
            ->active()
            ->with('subscription')
            ->orderBy('expires_at', 'desc')
            ->get();
    }

    public function hasActiveSubscription(User $user): bool
    {
        return $user->userSubscriptions()->active()->exists();
    }

    public function expireOldSubscriptions(): int
    {
        return UserSubscription::where('status', 'active')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    public function getRemainingDays(UserSubscription $userSubscription): int
    {
        if (!$userSubscription->isActive()) {
            return 0;
        }

        // Calculate remaining days from start of today to expiry date
        $now = now()->startOfDay();
        $expiresAt = $userSubscription->expires_at->startOfDay();

        return max(0, (int) $now->diffInDays($expiresAt, false));
    }
}
