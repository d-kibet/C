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

        // CARPET
        $carpets = Carpet::where('follow_up_due_at', $today)
                         ->where('payment_status','Not Paid')
                         ->whereNull('resolved_at')
                         ->get();
        $staffCarpet = User::role('carpet_staff')->get();
        foreach ($carpets as $c) {
            foreach ($staffCarpet as $u) {
                $u->notify(new FollowUpReminder($c,'carpet'));
            }
            $this->advanceStage($c);
        }

        // LAUNDRY
        $laundry = Laundry::where('follow_up_due_at', $today)
                          ->where('payment_status','Not Paid')
                          ->whereNull('resolved_at')
                          ->get();
        $staffLaundry = User::role('laundry_staff')->get();
        foreach ($laundry as $l) {
            foreach ($staffLaundry as $u) {
                $u->notify(new FollowUpReminder($l,'laundry'));
            }
            $this->advanceStage($l);
        }

        $this->info("Reminders sent: Carpet to {$staffCarpet->count()} users, Laundry to {$staffLaundry->count()} users.");
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
