<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\Notifications\NotificationManager;

class CleanupDuplicateOverdueNotifications extends Command
{
    protected $signature = 'notifications:cleanup-duplicates
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Cleanup duplicate overdue delivery notifications using NotificationManager';

    protected $notificationManager;

    public function __construct(NotificationManager $notificationManager)
    {
        parent::__construct();
        $this->notificationManager = $notificationManager;
    }

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - Showing what would be cleaned');
            $this->showDryRunStats();
            return;
        }

        $this->warn('This will cleanup duplicate notifications from the database!');
        if (!$this->confirm('Are you sure you want to proceed?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Starting full notification cleanup...');
        $startTime = microtime(true);

        // Get stats before
        $statsBefore = $this->notificationManager->getStatistics();
        $this->info("Notifications before cleanup: {$statsBefore['total']} (overdue type: {$statsBefore['overdue']})");

        // Run full cleanup
        $cleanupStats = $this->notificationManager->runFullCleanup();

        // Get stats after
        $statsAfter = $this->notificationManager->getStatistics();

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        // Summary
        $this->newLine();
        $this->info(str_repeat('=', 60));
        $this->info('CLEANUP SUMMARY');
        $this->info(str_repeat('=', 60));

        $this->table(
            ['Category', 'Carpets', 'Laundry', 'Total'],
            [
                ['Delivered items cleaned', $cleanupStats['delivered']['carpet'], $cleanupStats['delivered']['laundry'], $cleanupStats['delivered']['carpet'] + $cleanupStats['delivered']['laundry']],
                ['Orphaned cleaned', $cleanupStats['orphaned']['carpet'], $cleanupStats['orphaned']['laundry'], $cleanupStats['orphaned']['carpet'] + $cleanupStats['orphaned']['laundry']],
                ['Duplicates consolidated', $cleanupStats['duplicates']['carpet'], $cleanupStats['duplicates']['laundry'], $cleanupStats['duplicates']['carpet'] + $cleanupStats['duplicates']['laundry']],
                ['Expired cleaned', '-', '-', $cleanupStats['expired']],
            ]
        );

        $totalCleaned = $statsBefore['total'] - $statsAfter['total'];
        $percentReduced = $statsBefore['total'] > 0 ? round(($totalCleaned / $statsBefore['total']) * 100, 2) : 0;

        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Notifications before', number_format($statsBefore['total'])],
                ['Notifications after', number_format($statsAfter['total'])],
                ['Total cleaned', number_format($totalCleaned)],
                ['Reduction', "{$percentReduced}%"],
                ['Execution time', "{$duration}s"],
            ]
        );

        $this->info("\n✅ Cleanup completed successfully!");
    }

    private function showDryRunStats()
    {
        $this->info("\nAnalyzing notifications...");

        // Count delivered items that would be cleaned
        $deliveredCarpets = $this->countDeliveredNotifications('carpet');
        $deliveredLaundry = $this->countDeliveredNotifications('laundry');

        // Count duplicates
        $duplicateCarpets = $this->countDuplicates('carpet');
        $duplicateLaundry = $this->countDuplicates('laundry');

        // Count expired
        $expired = DB::table('notifications')
            ->where('expires_at', '<', now())
            ->whereNotNull('expires_at')
            ->count();

        // Count orphaned
        $orphanedCarpets = $this->countOrphaned('carpet');
        $orphanedLaundry = $this->countOrphaned('laundry');

        $this->newLine();
        $this->table(
            ['Category', 'Carpets', 'Laundry', 'Total'],
            [
                ['Delivered (to clean)', $deliveredCarpets, $deliveredLaundry, $deliveredCarpets + $deliveredLaundry],
                ['Duplicates (to consolidate)', $duplicateCarpets, $duplicateLaundry, $duplicateCarpets + $duplicateLaundry],
                ['Orphaned (to clean)', $orphanedCarpets, $orphanedLaundry, $orphanedCarpets + $orphanedLaundry],
                ['Expired (to clean)', '-', '-', $expired],
            ]
        );

        $total = $deliveredCarpets + $deliveredLaundry + $duplicateCarpets + $duplicateLaundry +
                 $orphanedCarpets + $orphanedLaundry + $expired;

        $this->info("\nTotal notifications that would be cleaned: {$total}");
        $this->warn("\nRun without --dry-run to actually delete the notifications.");
    }

    private function countDeliveredNotifications($type)
    {
        $table = $type === 'carpet' ? 'carpets' : 'laundries';
        $deliveredIds = DB::table($table)->where('delivered', 'Delivered')->pluck('id')->toArray();

        if (empty($deliveredIds)) {
            return 0;
        }

        // Use a single query with IN clause for better performance
        return DB::table('notifications')
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = ?", [$type])
            ->whereRaw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) IN (" . implode(',', $deliveredIds) . ")")
            ->count();
    }

    private function countDuplicates($type)
    {
        $duplicates = DB::table('notifications')
            ->select([
                'notifiable_id',
                DB::raw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) as service_id"),
                DB::raw('COUNT(*) as count')
            ])
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = ?", [$type])
            ->groupBy('notifiable_id', DB::raw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED)"))
            ->having('count', '>', 1)
            ->get();

        $total = 0;
        foreach ($duplicates as $d) {
            $total += ($d->count - 1); // Count of duplicates (keeping 1)
        }

        return $total;
    }

    private function countOrphaned($type)
    {
        $table = $type === 'carpet' ? 'carpets' : 'laundries';

        $notificationIds = DB::table('notifications')
            ->select(DB::raw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) as service_id"))
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = ?", [$type])
            ->pluck('service_id')
            ->unique()
            ->filter()
            ->toArray();

        $existingIds = DB::table($table)->pluck('id')->toArray();
        $orphanedIds = array_diff($notificationIds, $existingIds);

        if (empty($orphanedIds)) {
            return 0;
        }

        // Use a single query with IN clause for better performance
        return DB::table('notifications')
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = ?", [$type])
            ->whereRaw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) IN (" . implode(',', $orphanedIds) . ")")
            ->count();
    }
}
