<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupNotifications extends Command
{
    protected $signature = 'notifications:cleanup
                            {--days=30 : Delete notifications older than this many days}
                            {--keep-unread=0 : Keep unread notifications for this many extra days beyond --days (0 = keep all unread indefinitely)}
                            {--dry-run : Run in dry-run mode without deleting}';

    protected $description = 'Clean up old notifications to improve performance';

    public function handle()
    {
        $days = (int) $this->option('days');
        $keepUnreadDays = (int) $this->option('keep-unread');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be deleted');
        }

        $this->info("Cleaning up read notifications older than {$days} days...");
        if ($keepUnreadDays > 0) {
            $this->info("Keeping unread notifications younger than {$keepUnreadDays} days.");
        } else {
            $this->info("Keeping all unread notifications.");
        }
        $this->newLine();

        $totalBefore = DB::table('notifications')->count();
        $this->info("Total notifications before cleanup: {$totalBefore}");
        $this->newLine();

        $cutoffDate = now()->subDays($days);

        // Always only delete read notifications; unread are deleted separately below
        $readQuery = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', $cutoffDate);

        $readToDelete = $readQuery->count();
        $this->line("  Read notifications to delete: {$readToDelete}");

        // Unread older than the extended keep-unread window
        $unreadDeleted = 0;
        $unreadToDelete = 0;
        if ($keepUnreadDays > 0) {
            $unreadCutoff = now()->subDays($keepUnreadDays);
            $unreadQuery = DB::table('notifications')
                ->whereNull('read_at')
                ->where('created_at', '<', $unreadCutoff);
            $unreadToDelete = $unreadQuery->count();
            $this->line("  Unread notifications older than {$keepUnreadDays} days to delete: {$unreadToDelete}");
        }

        $totalToDelete = $readToDelete + $unreadToDelete;

        if ($totalToDelete === 0) {
            $this->info('✓ No notifications to clean up');
            return 0;
        }

        $this->warn("Total to delete: {$totalToDelete}");

        if (!$dryRun) {
            $deleted = $readQuery->delete();

            if ($keepUnreadDays > 0 && $unreadToDelete > 0) {
                $unreadCutoff = now()->subDays($keepUnreadDays);
                $unreadDeleted = DB::table('notifications')
                    ->whereNull('read_at')
                    ->where('created_at', '<', $unreadCutoff)
                    ->delete();
            }

            $totalAfter = DB::table('notifications')->count();

            $this->newLine();
            $this->info("✓ Deleted " . ($deleted + $unreadDeleted) . " notifications");
            $this->info("Total notifications after cleanup: {$totalAfter}");
        } else {
            $this->info("Would delete {$totalToDelete} notifications");
        }

        $this->newLine();
        $this->info('✓ Cleanup completed!');

        return 0;
    }
}
