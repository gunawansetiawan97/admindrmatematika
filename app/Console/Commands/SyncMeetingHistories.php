<?php

namespace App\Console\Commands;

use App\Models\ClassroomMember;
use App\Models\UserSubscription;
use App\Services\ClassroomService;
use Illuminate\Console\Command;

class SyncMeetingHistories extends Command
{
    protected $signature   = 'meetings:sync';
    protected $description = 'Sync meeting histories for all active classroom members';

    public function __construct(private ClassroomService $classroomService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $members = ClassroomMember::with(['user', 'classroom.subscription'])->get();

        $total = 0;
        foreach ($members as $member) {
            if (! $member->classroom || ! $member->classroom->subscription) {
                continue;
            }

            $sub = UserSubscription::where('user_id', $member->user_id)
                ->where('subscription_id', $member->classroom->subscription_id)
                ->where('status', 'active')
                ->first();

            if ($sub) {
                $total += $this->classroomService->syncMeetingHistories(
                    $member->user,
                    $member->classroom,
                    $sub
                );
            }
        }

        $this->info("Synced {$total} new meeting history records.");

        return self::SUCCESS;
    }
}
