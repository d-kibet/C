<?php

namespace App\Console\Commands;

use App\Models\Carpet;
use App\Models\Laundry;
use App\Models\User;
use App\Notifications\OverdueDeliveryNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOverdueDeliveries extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'deliveries:check-overdue 
                            {--days=3 : Number of days after received date to consider overdue}
                            {--notify-after=1 : Days overdue before sending notification}';

    /**
     * The console command description.
     */
    protected $description = 'Check for overdue deliveries and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $gracePeriodDays = (int) $this->option('days');
        $notifyAfterDays = (int) $this->option('notify-after');
        
        $this->info("Checking for deliveries overdue by more than {$notifyAfterDays} days (grace period: {$gracePeriodDays} days)");

        $cutoffDate = Carbon::now()->subDays($gracePeriodDays + $notifyAfterDays);
        
        // Check Carpets
        $overdueCarpets = Carpet::where('delivered', 'No')
            ->whereNotNull('date_received')
            ->whereDate('date_received', '<=', $cutoffDate)
            ->get();

        // Check Laundry
        $overdueLaundry = Laundry::where('delivered', 'No')
            ->whereNotNull('date_received')
            ->whereDate('date_received', '<=', $cutoffDate)
            ->get();

        $totalOverdue = $overdueCarpets->count() + $overdueLaundry->count();
        
        if ($totalOverdue === 0) {
            $this->info('No overdue deliveries found.');
            return;
        }

        $this->info("Found {$totalOverdue} overdue deliveries:");
        $this->info("- {$overdueCarpets->count()} carpets");
        $this->info("- {$overdueLaundry->count()} laundry items");

        // Get admin users to notify
        $adminUsers = User::role(['admin', 'manager'])->get();
        
        if ($adminUsers->isEmpty()) {
            $this->warn('No admin users found to notify!');
            return;
        }

        $notificationsSent = 0;

        // Process overdue carpets
        foreach ($overdueCarpets as $carpet) {
            $daysOverdue = $this->calculateOverdueDays($carpet->date_received, $gracePeriodDays);
            
            // Check if we already sent a notification today for this item
            if ($this->shouldSendNotification($carpet, 'carpet')) {
                foreach ($adminUsers as $admin) {
                    $admin->notify(new OverdueDeliveryNotification($carpet, $daysOverdue));
                }
                $notificationsSent++;
                
                // Log the overdue item for audit
                $carpet->logAuditEvent('overdue_notification_sent', [
                    'days_overdue' => $daysOverdue,
                    'notified_users' => $adminUsers->count()
                ]);
                
                $this->line("✓ Carpet #{$carpet->uniqueid} - {$daysOverdue} days overdue");
            }
        }

        // Process overdue laundry
        foreach ($overdueLaundry as $laundry) {
            $daysOverdue = $this->calculateOverdueDays($laundry->date_received, $gracePeriodDays);
            
            if ($this->shouldSendNotification($laundry, 'laundry')) {
                foreach ($adminUsers as $admin) {
                    $admin->notify(new OverdueDeliveryNotification($laundry, $daysOverdue));
                }
                $notificationsSent++;
                
                // Log the overdue item for audit
                $laundry->logAuditEvent('overdue_notification_sent', [
                    'days_overdue' => $daysOverdue,
                    'notified_users' => $adminUsers->count()
                ]);
                
                $this->line("✓ Laundry #{$laundry->uniqueid} - {$daysOverdue} days overdue");
            }
        }

        $this->info("Sent {$notificationsSent} overdue delivery notifications to {$adminUsers->count()} admin users.");
        
        Log::info("Overdue delivery check completed", [
            'total_overdue' => $totalOverdue,
            'notifications_sent' => $notificationsSent,
            'admin_users_notified' => $adminUsers->count()
        ]);
    }

    /**
     * Calculate how many days overdue an item is
     */
    private function calculateOverdueDays($dateReceived, $gracePeriodDays)
    {
        $expectedDeliveryDate = Carbon::parse($dateReceived)->addDays($gracePeriodDays);
        return max(0, Carbon::now()->diffInDays($expectedDeliveryDate, false) * -1);
    }

    /**
     * Check if we should send a notification for this item
     * Prevents spam by limiting to one notification per day per item
     */
    private function shouldSendNotification($item, $type)
    {
        // Check if notification was already sent today
        $today = Carbon::today();
        $existingNotification = \DB::table('notifications')
            ->where('type', OverdueDeliveryNotification::class)
            ->where('data->service_id', $item->id)
            ->where('data->service_type', $type)
            ->whereDate('created_at', $today)
            ->exists();

        return !$existingNotification;
    }
}