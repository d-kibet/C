<?php

namespace App\Console\Commands;

use App\Models\Carpet;
use App\Models\Laundry;
use App\Models\User;
use App\Notifications\OverdueDeliveryNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckOverdueDeliveriesOptimized extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'deliveries:check-overdue-optimized 
                            {--days=3 : Number of days after received date to consider overdue}
                            {--notify-after=1 : Days overdue before sending notification}
                            {--batch-size=100 : Number of records to process per batch}
                            {--max-notifications=500 : Maximum notifications to send per run}';

    /**
     * The console command description.
     */
    protected $description = 'Optimized version: Check for overdue deliveries with batch processing for large databases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $gracePeriodDays = (int) $this->option('days');
        $notifyAfterDays = (int) $this->option('notify-after');
        $batchSize = (int) $this->option('batch-size');
        $maxNotifications = (int) $this->option('max-notifications');
        
        $this->info("Starting optimized overdue delivery check...");
        $this->info("Config: Grace={$gracePeriodDays}d, NotifyAfter={$notifyAfterDays}d, BatchSize={$batchSize}, MaxNotifications={$maxNotifications}");

        $cutoffDate = Carbon::now()->subDays($gracePeriodDays + $notifyAfterDays);
        $notificationsSent = 0;
        $startTime = microtime(true);
        
        // Get users once (cached)
        $adminUsers = $this->getAdminUsers();
        if ($adminUsers->isEmpty()) {
            $this->warn('No users found to notify!');
            return;
        }

        // Process carpets in batches
        $carpetStats = $this->processCarpetsInBatches($cutoffDate, $gracePeriodDays, $adminUsers, $batchSize, $maxNotifications, $notificationsSent);
        
        // Process laundry in batches (if we haven't hit the max)
        $laundryStats = $this->processLaundryInBatches($cutoffDate, $gracePeriodDays, $adminUsers, $batchSize, $maxNotifications, $notificationsSent);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        $totalOverdue = $carpetStats['overdue'] + $laundryStats['overdue'];
        $totalProcessed = $carpetStats['processed'] + $laundryStats['processed'];

        // Summary
        $this->info("Overdue delivery check completed!");
        $this->table(['Metric', 'Carpets', 'Laundry', 'Total'], [
            ['Processed', $carpetStats['processed'], $laundryStats['processed'], $totalProcessed],
            ['Overdue Found', $carpetStats['overdue'], $laundryStats['overdue'], $totalOverdue],
            ['Notifications Sent', $carpetStats['notifications'], $laundryStats['notifications'], $notificationsSent],
        ]);
        $this->info("Execution time: {$duration}s");
        $this->info("Users notified: {$adminUsers->count()}");

        // Log summary
        Log::info("Optimized overdue delivery check completed", [
            'duration_seconds' => $duration,
            'total_processed' => $totalProcessed,
            'total_overdue' => $totalOverdue,
            'notifications_sent' => $notificationsSent,
            'admin_users_count' => $adminUsers->count()
        ]);
    }

    private function processCarpetsInBatches($cutoffDate, $gracePeriodDays, $adminUsers, $batchSize, $maxNotifications, &$notificationsSent)
    {
        $stats = ['processed' => 0, 'overdue' => 0, 'notifications' => 0];
        $shouldStop = false;
        
        $this->info("Processing carpets...");
        
        // Use efficient chunking with indexed query
        Carpet::select(['id', 'uniqueid', 'phone', 'location', 'date_received'])
            ->where('delivered', 'Not Delivered')
            ->whereNotNull('date_received')
            ->whereDate('date_received', '<=', $cutoffDate)
            ->orderBy('date_received') // Most overdue first
            ->chunk($batchSize, function ($carpets) use ($gracePeriodDays, $adminUsers, $maxNotifications, &$notificationsSent, &$stats, &$shouldStop) {
                
                $batchNotifications = 0;
                $overdueCarpetIds = [];
                
                foreach ($carpets as $carpet) {
                    $stats['processed']++;
                    
                    if ($notificationsSent >= $maxNotifications) {
                        $this->warn("Reached maximum notifications limit ($maxNotifications)");
                        $shouldStop = true;
                        break; // Break out of foreach loop
                    }
                    
                    $daysOverdue = $this->calculateOverdueDays($carpet->date_received, $gracePeriodDays);
                    
                    if ($daysOverdue > 0) {
                        $stats['overdue']++;
                        
                        // Check if we should send notification (not sent today)
                        if ($this->shouldSendNotification($carpet->id, 'carpet')) {
                            $overdueCarpetIds[] = $carpet->id;
                            
                            // Send to all admin users
                            foreach ($adminUsers as $admin) {
                                $admin->notify(new OverdueDeliveryNotification($carpet, $daysOverdue));
                            }
                            
                            $batchNotifications++;
                            $notificationsSent++;
                            $stats['notifications']++;
                            
                            $this->line("  Carpet {$carpet->uniqueid} - {$daysOverdue} days overdue");
                        }
                    }
                }
                
                // Bulk audit logging for this batch
                if (!empty($overdueCarpetIds)) {
                    $this->logBatchAuditEvents($overdueCarpetIds, 'carpet', $adminUsers->count());
                }
                
                if ($batchNotifications > 0) {
                    $this->info("  Batch complete: {$batchNotifications} notifications sent");
                }
                
                // Memory cleanup
                unset($carpets);
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
                
                // Return false to stop chunking if we've hit the limit
                return !$shouldStop;
            });
            
        return $stats;
    }

    private function processLaundryInBatches($cutoffDate, $gracePeriodDays, $adminUsers, $batchSize, $maxNotifications, &$notificationsSent)
    {
        $stats = ['processed' => 0, 'overdue' => 0, 'notifications' => 0];
        $shouldStop = false;
        
        if ($notificationsSent >= $maxNotifications) {
            $this->warn("Skipping laundry processing - notification limit reached");
            return $stats;
        }
        
        $this->info("Processing laundry...");
        
        Laundry::select(['id', 'unique_id', 'phone', 'location', 'date_received'])
            ->where('delivered', 'Not Delivered')
            ->whereNotNull('date_received')
            ->whereDate('date_received', '<=', $cutoffDate)
            ->orderBy('date_received')
            ->chunk($batchSize, function ($laundryItems) use ($gracePeriodDays, $adminUsers, $maxNotifications, &$notificationsSent, &$stats, &$shouldStop) {
                
                $batchNotifications = 0;
                $overdueLaundryIds = [];
                
                foreach ($laundryItems as $laundry) {
                    $stats['processed']++;
                    
                    if ($notificationsSent >= $maxNotifications) {
                        $this->warn("Reached maximum notifications limit ($maxNotifications)");
                        $shouldStop = true;
                        break; // Break out of foreach loop
                    }
                    
                    $daysOverdue = $this->calculateOverdueDays($laundry->date_received, $gracePeriodDays);
                    
                    if ($daysOverdue > 0) {
                        $stats['overdue']++;
                        
                        if ($this->shouldSendNotification($laundry->id, 'laundry')) {
                            $overdueLaundryIds[] = $laundry->id;
                            
                            foreach ($adminUsers as $admin) {
                                $admin->notify(new OverdueDeliveryNotification($laundry, $daysOverdue));
                            }
                            
                            $batchNotifications++;
                            $notificationsSent++;
                            $stats['notifications']++;
                            
                            $this->line("  Laundry {$laundry->unique_id} - {$daysOverdue} days overdue");
                        }
                    }
                }
                
                // Bulk audit logging
                if (!empty($overdueLaundryIds)) {
                    $this->logBatchAuditEvents($overdueLaundryIds, 'laundry', $adminUsers->count());
                }
                
                if ($batchNotifications > 0) {
                    $this->info("  Batch complete: {$batchNotifications} notifications sent");
                }
                
                // Memory cleanup
                unset($laundryItems);
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
                
                // Return false to stop chunking if we've hit the limit
                return !$shouldStop;
            });
            
        return $stats;
    }

    private function getAdminUsers()
    {
        // Get all users since role system is not implemented yet
        // TODO: Update this to use specific roles when role system is implemented
        return User::select(['id', 'name', 'email'])->get();
    }

    private function calculateOverdueDays($dateReceived, $gracePeriodDays)
    {
        $expectedDeliveryDate = Carbon::parse($dateReceived)->addDays($gracePeriodDays);
        return max(0, Carbon::now()->diffInDays($expectedDeliveryDate, false) * -1);
    }

    private function shouldSendNotification($itemId, $type)
    {
        // Optimized query with specific columns and today's date
        return !DB::table('notifications')
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->where('data->service_id', $itemId)
            ->where('data->service_type', $type)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    private function logBatchAuditEvents($itemIds, $type, $adminCount)
    {
        // Bulk insert audit events for better performance
        $auditData = [];
        $now = Carbon::now();
        
        foreach ($itemIds as $itemId) {
            $auditData[] = [
                'user_id' => null, // System generated
                'event' => 'overdue_notification_sent',
                'auditable_type' => $type === 'carpet' ? 'App\\Models\\Carpet' : 'App\\Models\\Laundry',
                'auditable_id' => $itemId,
                'new_values' => json_encode(['notified_users' => $adminCount]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Laravel Command',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        // Bulk insert for efficiency
        DB::table('audit_trails')->insert($auditData);
    }
}