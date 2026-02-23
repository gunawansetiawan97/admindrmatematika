<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Classroom;
use App\Models\ClassroomActivity;
use App\Models\ClassroomMember;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Collection;

class ClassroomService
{
    public function getClassroomsForUser(User $user): Collection
    {
        return Classroom::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['subscription'])
            ->active()
            ->get()
            ->map(function ($classroom) use ($user) {
                // Count accessible activities for this user
                $classroom->accessible_activities_count = $this->getAccessibleActivitiesForUser($classroom, $user)->count();
                // Get latest 3 accessible activities
                $classroom->recent_activities = $this->getAccessibleActivitiesForUser($classroom, $user)->take(3);
                return $classroom;
            });
    }

    public function getClassroomActivities(Classroom $classroom): Collection
    {
        return $classroom->activities()
            ->with('admin')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get activities that are accessible to a specific user based on their subscription periods
     */
    public function getAccessibleActivitiesForUser(Classroom $classroom, User $user): Collection
    {
        // Get all subscription periods for this user and this classroom's subscription
        $subscriptionPeriods = UserSubscription::where('user_id', $user->id)
            ->where('subscription_id', $classroom->subscription_id)
            ->get();

        if ($subscriptionPeriods->isEmpty()) {
            return collect();
        }

        // Get all activities
        $activities = $classroom->activities()
            ->with('admin')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        // Filter activities based on subscription periods
        return $activities->filter(function ($activity) use ($subscriptionPeriods) {
            foreach ($subscriptionPeriods as $subscription) {
                // Activity is accessible if it was created during any subscription period
                if ($activity->created_at >= $subscription->starts_at &&
                    $activity->created_at <= $subscription->expires_at) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Check if a specific activity is accessible to a user
     */
    public function isActivityAccessibleToUser(ClassroomActivity $activity, User $user): bool
    {
        $classroom = $activity->classroom;

        $subscriptionPeriods = UserSubscription::where('user_id', $user->id)
            ->where('subscription_id', $classroom->subscription_id)
            ->get();

        foreach ($subscriptionPeriods as $subscription) {
            if ($activity->created_at >= $subscription->starts_at &&
                $activity->created_at <= $subscription->expires_at) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any subscription (active or expired) for this classroom's subscription type
     */
    public function userHasAnySubscription(User $user, Classroom $classroom): bool
    {
        return UserSubscription::where('user_id', $user->id)
            ->where('subscription_id', $classroom->subscription_id)
            ->exists();
    }

    /**
     * Check if user has active subscription for this classroom
     */
    public function userHasActiveSubscription(User $user, Classroom $classroom): bool
    {
        return UserSubscription::where('user_id', $user->id)
            ->where('subscription_id', $classroom->subscription_id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function addMember(Classroom $classroom, User $user, Admin $admin): ClassroomMember
    {
        return ClassroomMember::create([
            'classroom_id' => $classroom->id,
            'user_id' => $user->id,
            'added_by' => $admin->id,
            'joined_at' => now(),
        ]);
    }

    public function removeMember(Classroom $classroom, User $user): bool
    {
        return ClassroomMember::where('classroom_id', $classroom->id)
            ->where('user_id', $user->id)
            ->delete() > 0;
    }

    public function getAvailableStudents(Classroom $classroom): Collection
    {
        // Get user IDs yang sudah terdaftar di kelas manapun dalam subscription yang sama
        $existingMemberIds = ClassroomMember::whereHas('classroom', function ($query) use ($classroom) {
            $query->where('subscription_id', $classroom->subscription_id);
        })->pluck('user_id');

        return User::whereHas('userSubscriptions', function ($query) use ($classroom) {
            $query->where('subscription_id', $classroom->subscription_id)
                ->where('status', 'active')
                ->where('expires_at', '>', now());
        })
            ->whereNotIn('id', $existingMemberIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if user is already a member of any classroom in the same subscription
     */
    public function userAlreadyInSubscriptionClassroom(User $user, Classroom $classroom): bool
    {
        return ClassroomMember::whereHas('classroom', function ($query) use ($classroom) {
            $query->where('subscription_id', $classroom->subscription_id);
        })
            ->where('user_id', $user->id)
            ->exists();
    }

    public function getClassroomMembers(Classroom $classroom): Collection
    {
        $members = $classroom->members()
            ->with(['user', 'addedBy'])
            ->orderBy('joined_at', 'desc')
            ->get();

        $meetingsCount = $classroom->subscription->meetings_count;
        $today = now()->toDateString();

        // Attach subscription info and meeting stats for each member
        return $members->map(function ($member) use ($classroom, $meetingsCount, $today) {
            $subscriptionPeriods = UserSubscription::where('user_id', $member->user_id)
                ->where('subscription_id', $classroom->subscription_id)
                ->get();

            $member->userSubscription = $subscriptionPeriods->sortByDesc('expires_at')->first();

            // total = meetings_count dari paket, done = pertemuan yg sudah terjadi dlm periode member
            $done = 0;
            if ($meetingsCount && $subscriptionPeriods->isNotEmpty()) {
                $done = $classroom->activities()
                    ->whereNotNull('meeting_date')
                    ->where('meeting_date', '<=', $today)
                    ->where(function ($q) use ($subscriptionPeriods) {
                        foreach ($subscriptionPeriods as $i => $period) {
                            $method = $i === 0 ? 'where' : 'orWhere';
                            $q->$method(function ($inner) use ($period) {
                                $inner->where('meeting_date', '>=', $period->starts_at->toDateString())
                                      ->where('meeting_date', '<=', $period->expires_at->toDateString());
                            });
                        }
                    })
                    ->distinct('meeting_date')
                    ->count('meeting_date');
            }

            $member->meeting_total     = $meetingsCount;
            $member->meeting_done      = $done;
            $member->meeting_remaining = $meetingsCount ? max(0, $meetingsCount - $done) : null;

            return $member;
        });
    }

    public function createActivity(Classroom $classroom, Admin $admin, array $data): ClassroomActivity
    {
        return ClassroomActivity::create([
            'classroom_id' => $classroom->id,
            'admin_id' => $admin->id,
            'type' => $data['type'],
            'title' => $data['title'],
            'content' => $data['content'],
            'is_pinned' => $data['is_pinned'] ?? false,
            'meeting_date' => $data['meeting_date'] ?? null,
        ]);
    }

    public function togglePin(ClassroomActivity $activity): bool
    {
        $activity->is_pinned = !$activity->is_pinned;
        return $activity->save();
    }

    public function userCanAccessClassroom(User $user, Classroom $classroom): bool
    {
        // User can access classroom if they are a member (regardless of subscription status)
        return $classroom->hasMember($user);
    }
}
