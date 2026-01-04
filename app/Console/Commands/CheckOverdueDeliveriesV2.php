<?php

namespace App\Console\Commands;

use App\Models\Carpet;
use App\Models\Laundry;
use App\Models\User;
use App\Services\Notifications\NotificationManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOverdueDeliveriesV2 extends Command
{
    protected $signature = 'deliveries:check-overdue-v2
                            {--days=3 : Number of days after received date to consider overdue}
                            {--notify-after=1 : Days overdue before sending notification}
                            {--batch-size=100 : Number of records to process per batch}
                            {--max-notifications=500 : Maximum notifications to send per run}
                            {--notification-interval=5 : Days between repeated notifications for same item}';

    protected $description = 'V2: Smart overdue delivery check with consolidation, auto-cleanup, and deduplication';

    protected $notificationManager;

    public function __construct(NotificationManager $notificationManager)
    {
        parent::__construct();
        $this->notificationManager = $notificationManager;
    }

    public function handle()
    {
        $gracePeriodDays = (int) $this->option('days');
        $notifyAfterDays = (int) $this->option('notify-after');
        $batchSize = (int) $this->option('batch-size');
        $maxNotifications = (int) $this->option('max-notifications');
        $notificationInterval = (int) $this->option('notification-interval');

        $this->info("🚀 Starting SMART overdue delivery check (V2)...");
        $this->info("Config: Grace={$gracePeriodDays}d, NotifyAfter={$notifyAfterDays}d, NotifyInterval={$notificationInterval}d, Batch={$batchSize}, Max={$maxNotifications}");
        $this->newLine();

        $startTime = microtime(true);

        // PHASE 1: CLEANUP (run BEFORE checking for new notifications)
        $this->info("📋 Phase 1: Cleanup");
        $this->runCleanup();
        $this->newLine();

        // PHASE 2: PROCESS OVERDUE ITEMS
        $this->info("🔍 Phase 2: Processing overdue items");

        $cutoffDate = Carbon::now()->subDays($gracePeriodDays + $notifyAfterDays);
        $adminUsers = $this->getAdminUsers();

        if ($adminUsers->isEmpty()) {
            $this->warn('No users found to notify!');
            return;
        }

        $notificationsSent = 0;
        $carpetStats = $this->processCarpets($cutoffDate, $gracePeriodDays, $adminUsers, $batchSize, $maxNotifications, $notificationInterval, $notificationsSent);
        $laundryStats = $this->processLaundry($cutoffDate, $gracePeriodDays, $adminUsers, $batchSize, $maxNotifications, $notificationInterval, $notificationsSent);

        // PHASE 3: SUMMARY
        $this->newLine();
        $this->info("✅ Overdue delivery check completed!");

        $totalOverdue = $carpetStats['overdue'] + $laundryStats['overdue'];
        $totalProcessed = $carpetStats['processed'] + $laundryStats['processed'];
        $totalUpdated = $carpetStats['updated'] + $laundryStats['updated'];
        $totalCreated = $carpetStats['created'] + $laundryStats['created'];

        $this->table(['Metric', 'Carpets', 'Laundry', 'Total'], [
            ['Processed', $carpetStats['processed'], $laundryStats['processed'], $totalProcessed],
            ['Overdue Found', $carpetStats['overdue'], $laundryStats['overdue'], $totalOverdue],
            ['Notifications Updated', $carpetStats['updated'], $laundryStats['updated'], $totalUpdated],
            ['Notifications Created', $carpetStats['created'], $laundryStats['created'], $totalCreated],
            ['Skipped', $carpetStats['skipped'], $laundryStats['skipped'], $carpetStats['skipped'] + $laundryStats['skipped']],
        ]);

        $duration = round(microtime(true) - $startTime, 2);
        $this->info("⏱️  Execution time: {$duration}s");
        $this->info("👥 Users notified: {$adminUsers->count()}");

        // Get final statistics
        $stats = $this->notificationManager->getStatistics();
        $this->newLine();
        $this->info("📊 Current notification statistics:");
        $this->table(['Metric', 'Count'], [
            ['Total notifications', $stats['total']],
            ['Unread', $stats['unread']],
            ['Overdue type', $stats['overdue']],
            ['Expired (will be cleaned)', $stats['expired']],
        ]);

        Log::info("Smart overdue delivery check completed (V2)", [
            'duration_seconds' => $duration,
            'total_processed' => $totalProcessed,
            'total_overdue' => $totalOverdue,
            'notifications_updated' => $totalUpdated,
            'notifications_created' => $totalCreated,
            'admin_users_count' => $adminUsers->count()
        ]);
    }

    protected function runCleanup(): void
    {
        // Clean delivered items
        $deliveredStats = $this->notificationManager->cleanupDeliveredNotifications();
        $this->line("  ✓ Delivered items cleaned: Carpets={$deliveredStats['carpet']}, Laundry={$deliveredStats['laundry']}");

        // Clean expired
        $expired = $this->notificationManager->cleanupExpiredNotifications();
        $this->line("  ✓ Expired notifications cleaned: {$expired}");

        // Clean orphaned
        $orphanedStats = $this->notificationManager->cleanupOrphanedNotifications();
        $this->line("  ✓ Orphaned notifications cleaned: Carpets={$orphanedStats['carpet']}, Laundry={$orphanedStats['laundry']}");
    }

    protected function processCarpets($cutoffDate, $gracePeriodDays, $adminUsers, $batchSize, $maxNotifications, $notificationInterval, &$notificationsSent): array
    {
        $stats = ['processed' => 0, 'overdue' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0];
        $shouldStop = false;

        $this->line("  Processing carpets...");

        Carpet::select(['id', 'uniqueid', 'phone', 'location', 'date_received', 'last_overdue_notification_at'])
            ->where('delivered', 'Not Delivered')
            ->whereNotNull('date_received')
            ->whereDate('date_received', '<=', $cutoffDate)
            ->orderBy('date_received')
            ->chunk($batchSize, function ($carpets) use ($gracePeriodDays, $adminUsers, $maxNotifications, $notificationInterval, &$notificationsSent, &$stats, &$shouldStop) {
                foreach ($carpets as $carpet) {
                    $stats['processed']++;

                    if ($notificationsSent >= $maxNotifications) {
                        $this->warn("    Reached max notifications limit");
                        $shouldStop = true;
                        break;
                    }

                    $daysOverdue = $this->calculateOverdueDays($carpet->date_received, $gracePeriodDays);

                    if ($daysOverdue > 0) {
                        $stats['overdue']++;

                        // Check interval
                        if ($this->shouldSendNotification($carpet->last_overdue_notification_at, $notificationInterval)) {
                            $createdCount = 0;

                            foreach ($adminUsers as $admin) {
                                // Use smart manager - it will update OR create
                                $beforeCount = \DB::table('notifications')->count();
                                $this->notificationManager->sendOrUpdateOverdueNotification($admin, $carpet, $daysOverdue);
                                $afterCount = \DB::table('notifications')->count();

                                if ($afterCount > $beforeCount) {
                                    $createdCount++;
                                    $stats['created']++;
                                } else {
                                    $stats['updated']++;
                                }

                                $notificationsSent++;
                            }

                            // Update carpet timestamp
                            $carpet->update(['last_overdue_notification_at' => now()]);

                            if ($createdCount > 0) {
                                $this->line("    Carpet {$carpet->uniqueid} - {$daysOverdue}d overdue (created {$createdCount} new)");
                            }
                        } else {
                            $stats['skipped']++;
                        }
                    }
                }

                return !$shouldStop;
            });

        return $stats;
    }

    protected function processLaundry($cutoffDate, $gracePeriodDays, $adminUsers, $batchSize, $maxNotifications, $notificationInterval, &$notificationsSent): array
    {
        $stats = ['processed' => 0, 'overdue' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0];
        $shouldStop = false;

        if ($notificationsSent >= $this->option('max-notifications')) {
            $this->line("  Skipping laundry - notification limit reached");
            return $stats;
        }

        $this->line("  Processing laundry...");

        Laundry::select(['id', 'unique_id', 'phone', 'location', 'date_received', 'last_overdue_notification_at'])
            ->where('delivered', 'Not Delivered')
            ->whereNotNull('date_received')
            ->whereDate('date_received', '<=', $cutoffDate)
            ->orderBy('date_received')
            ->chunk($batchSize, function ($laundryItems) use ($gracePeriodDays, $adminUsers, $notificationInterval, &$notificationsSent, &$stats, &$shouldStop) {
                foreach ($laundryItems as $laundry) {
                    $stats['processed']++;

                    if ($notificationsSent >= $this->option('max-notifications')) {
                        $shouldStop = true;
                        break;
                    }

                    $daysOverdue = $this->calculateOverdueDays($laundry->date_received, $gracePeriodDays);

                    if ($daysOverdue > 0) {
                        $stats['overdue']++;

                        if ($this->shouldSendNotification($laundry->last_overdue_notification_at, $notificationInterval)) {
                            $createdCount = 0;

                            foreach ($adminUsers as $admin) {
                                $beforeCount = \DB::table('notifications')->count();
                                $this->notificationManager->sendOrUpdateOverdueNotification($admin, $laundry, $daysOverdue);
                                $afterCount = \DB::table('notifications')->count();

                                if ($afterCount > $beforeCount) {
                                    $createdCount++;
                                    $stats['created']++;
                                } else {
                                    $stats['updated']++;
                                }

                                $notificationsSent++;
                            }

                            $laundry->update(['last_overdue_notification_at' => now()]);

                            if ($createdCount > 0) {
                                $this->line("    Laundry {$laundry->unique_id} - {$daysOverdue}d overdue (created {$createdCount} new)");
                            }
                        } else {
                            $stats['skipped']++;
                        }
                    }
                }

                return !$shouldStop;
            });

        return $stats;
    }

    protected function getAdminUsers()
    {
        return User::select(['id', 'name', 'email'])->get();
    }

    protected function calculateOverdueDays($dateReceived, $gracePeriodDays)
    {
        $expectedDeliveryDate = Carbon::parse($dateReceived)->addDays($gracePeriodDays);
        return max(0, Carbon::now()->diffInDays($expectedDeliveryDate, false) * -1);
    }

    protected function shouldSendNotification($lastNotificationAt, $intervalDays)
    {
        if (is_null($lastNotificationAt)) {
            return true;
        }

        $daysSinceLastNotification = Carbon::parse($lastNotificationAt)->diffInDays(Carbon::now());
        return $daysSinceLastNotification >= $intervalDays;
    }
}
