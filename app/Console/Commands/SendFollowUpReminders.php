<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Carpet;
use App\Models\Laundry;
use App\Models\User;
use App\Notifications\FollowUpReminder;

class SendFollowUpReminders extends Command
{
    protected $signature   = 'reminders:send-followups';
    protected $description = 'Send tiered follow-up reminders for carpets & laundry';

    public function handle()
    {
        $today = Carbon::today()->toDateString();

        // Get all admin users (since role system has specific roles, use all users)
        // TODO: Update to use specific roles when 'carpet_staff' and 'laundry_staff' roles are created
        $allStaff = User::all();

        if ($allStaff->isEmpty()) {
            $this->warn('No users found to send follow-up reminders');
            return;
        }

        // CARPET
        $carpets = Carpet::where('follow_up_due_at', $today)
                         ->where('payment_status','Not Paid')
                         ->whereNull('resolved_at')
                         ->get();

        $carpetCount = 0;
        foreach ($carpets as $c) {
            foreach ($allStaff as $u) {
                $u->notify(new FollowUpReminder($c,'carpet'));
            }
            $this->advanceStage($c);
            $carpetCount++;
        }

        // LAUNDRY
        $laundry = Laundry::where('follow_up_due_at', $today)
                          ->where('payment_status','Not Paid')
                          ->whereNull('resolved_at')
                          ->get();

        $laundryCount = 0;
        foreach ($laundry as $l) {
            foreach ($allStaff as $u) {
                $u->notify(new FollowUpReminder($l,'laundry'));
            }
            $this->advanceStage($l);
            $laundryCount++;
        }

        $this->info("Follow-up reminders sent:");
        $this->info("  Carpets: {$carpetCount} items to {$allStaff->count()} users");
        $this->info("  Laundry: {$laundryCount} items to {$allStaff->count()} users");
        $this->info("  Total notifications: " . (($carpetCount + $laundryCount) * $allStaff->count()));
    }

    protected function advanceStage($rec)
    {
        $stages   = config('followup.stages');
        $next     = $rec->follow_up_stage + 1;
        $interval = $stages[$next] ?? end($stages);
        $rec->update([
            'follow_up_stage'  => $next,
            'last_notified_at' => now(),
            'follow_up_due_at' => now()->addDays($interval)->toDateString(),
        ]);
    }
}
