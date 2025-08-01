<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:cleanup 
                           {--days=30 : Delete read notifications older than X days}
                           {--keep-unread=90 : Keep unread notifications for X days}
                           {--batch-size=1000 : Number of records to delete per batch}
                           {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Cleanup old notifications to keep database size manageable';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $readDays = (int) $this->option('days');
        $unreadDays = (int) $this->option('keep-unread');
        $batchSize = (int) $this->option('batch-size');
        $dryRun = $this->option('dry-run');

        $this->info("Starting notification cleanup...");
        $this->info("Config: ReadDays={$readDays}, UnreadDays={$unreadDays}, BatchSize={$batchSize}, DryRun=" . ($dryRun ? 'Yes' : 'No'));

        $readCutoff = Carbon::now()->subDays($readDays);
        $unreadCutoff = Carbon::now()->subDays($unreadDays);

        // Count what will be deleted
        $readToDelete = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', $readCutoff)
            ->count();

        $unreadToDelete = DB::table('notifications')
            ->whereNull('read_at')
            ->where('created_at', '<', $unreadCutoff)
            ->count();

        $totalToDelete = $readToDelete + $unreadToDelete;

        $this->table(['Type', 'Count'], [
            ['Read notifications (older than ' . $readDays . ' days)', $readToDelete],
            ['Unread notifications (older than ' . $unreadDays . ' days)', $unreadToDelete],
            ['Total to delete', $totalToDelete]
        ]);

        if ($totalToDelete === 0) {
            $this->info('No notifications need cleanup!');
            return;
        }

        if ($dryRun) {
            $this->info('Dry run complete - no notifications were deleted');
            return;
        }

        if (!$this->confirm("Are you sure you want to delete {$totalToDelete} notifications?")) {
            $this->info('Cleanup cancelled');
            return;
        }

        $startTime = microtime(true);
        $deleted = 0;

        // Delete read notifications in batches
        if ($readToDelete > 0) {
            $this->info("Deleting read notifications...");
            $bar = $this->output->createProgressBar($readToDelete);

            do {
                $batchDeleted = DB::table('notifications')
                    ->whereNotNull('read_at')
                    ->where('created_at', '<', $readCutoff)
                    ->limit($batchSize)
                    ->delete();

                $deleted += $batchDeleted;
                $bar->advance($batchDeleted);

                // Prevent memory issues
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            } while ($batchDeleted > 0);

            $bar->finish();
            $this->newLine();
        }

        // Delete old unread notifications in batches
        if ($unreadToDelete > 0) {
            $this->info("Deleting old unread notifications...");
            $bar = $this->output->createProgressBar($unreadToDelete);

            do {
                $batchDeleted = DB::table('notifications')
                    ->whereNull('read_at')
                    ->where('created_at', '<', $unreadCutoff)
                    ->limit($batchSize)
                    ->delete();

                $deleted += $batchDeleted;
                $bar->advance($batchDeleted);

                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            } while ($batchDeleted > 0);

            $bar->finish();
            $this->newLine();
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->info("Cleanup complete!");
        $this->info("Deleted: {$deleted} notifications in {$duration}s");

        // Show current counts
        $currentTotal = DB::table('notifications')->count();
        $currentUnread = DB::table('notifications')->whereNull('read_at')->count();

        $this->table(['Status', 'Count'], [
            ['Remaining total', $currentTotal],
            ['Remaining unread', $currentUnread],
            ['Remaining read', $currentTotal - $currentUnread]
        ]);
    }
}