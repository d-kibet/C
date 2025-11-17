<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DiagnoseNotifications extends Command
{
    protected $signature = 'notifications:diagnose';
    protected $description = 'Diagnose notification system issues';

    public function handle()
    {
        $this->info('Diagnosing notification system...');
        $this->newLine();

        // Check if notifications table exists
        try {
            $this->info('✓ Checking notifications table...');
            $tableExists = DB::select("SHOW TABLES LIKE 'notifications'");
            if (empty($tableExists)) {
                $this->error('✗ Notifications table does not exist!');
                $this->warn('Run: php artisan notifications:table');
                $this->warn('Then: php artisan migrate');
                return 1;
            }
            $this->info('  ✓ Notifications table exists');
        } catch (\Exception $e) {
            $this->error('✗ Error checking notifications table: ' . $e->getMessage());
            return 1;
        }

        // Check User model has Notifiable trait
        try {
            $this->info('✓ Checking User model...');
            $user = User::first();
            if (!$user) {
                $this->warn('  ⚠ No users found in database');
            } else {
                if (method_exists($user, 'notifications')) {
                    $this->info('  ✓ User model has notifications method');

                    // Try to get notifications
                    $notificationCount = $user->notifications()->count();
                    $this->info("  ✓ User has {$notificationCount} notifications");

                    $unreadCount = $user->unreadNotifications()->count();
                    $this->info("  ✓ User has {$unreadCount} unread notifications");
                } else {
                    $this->error('  ✗ User model missing Notifiable trait!');
                    $this->warn('  Add "use Notifiable;" to App\Models\User');
                    return 1;
                }
            }
        } catch (\Exception $e) {
            $this->error('✗ Error checking User model: ' . $e->getMessage());
            $this->error('  ' . $e->getTraceAsString());
            return 1;
        }

        // Check if we can serialize notification data
        try {
            $this->info('✓ Checking notification serialization...');
            if ($user && $user->unreadNotifications->count() > 0) {
                $notification = $user->unreadNotifications->first();
                $data = [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
                $this->info('  ✓ Can serialize notification data');
            } else {
                $this->info('  ⚠ No unread notifications to test serialization');
            }
        } catch (\Exception $e) {
            $this->error('✗ Error serializing notifications: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('✓ All notification system checks passed!');
        return 0;
    }
}
