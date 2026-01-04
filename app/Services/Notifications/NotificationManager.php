<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\Carpet;
use App\Models\Laundry;
use App\Notifications\OverdueDeliveryNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationManager
{
    /**
     * Send or update overdue notification with smart consolidation
     *
     * This is the KEY method that eliminates duplicates by:
     * 1. Finding existing unread notification for same item
     * 2. Updating it instead of creating new one
     * 3. Tracking update count and timestamps
     */
    public function sendOrUpdateOverdueNotification(User $user, $service, int $daysOverdue): void
    {
        $serviceType = class_basename($service);
        $serviceId = $service->id;

        // Check user preferences
        $preferences = $user->getNotificationPreferences();

        if (!$preferences->isNotificationTypeEnabled('overdue')) {
            Log::info('Overdue notification skipped - user preference disabled', [
                'user_id' => $user->id,
                'service' => $serviceType,
                'service_id' => $serviceId
            ]);
            return;
        }

        // Check quiet hours
        if ($preferences->isInQuietHours()) {
            Log::info('Overdue notification skipped - quiet hours', [
                'user_id' => $user->id,
                'service' => $serviceType,
                'service_id' => $serviceId
            ]);
            return;
        }

        // SMART CONSOLIDATION: Find existing unread notification for this item
        $existingNotification = DB::table('notifications')
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', 'App\\Models\\User')
            ->whereNull('read_at')
            ->whereRaw("JSON_EXTRACT(data, '$.service_type') = ?", [strtolower($serviceType)])
            ->whereRaw("JSON_EXTRACT(data, '$.service_id') = ?", [$serviceId])
            ->first();

        if ($existingNotification) {
            // UPDATE existing notification instead of creating new one
            $this->updateExistingNotification($existingNotification, $daysOverdue);

            Log::info('Overdue notification updated (consolidated)', [
                'user_id' => $user->id,
                'service' => $serviceType,
                'service_id' => $serviceId,
                'days_overdue' => $daysOverdue,
                'notification_id' => $existingNotification->id
            ]);
        } else {
            // No existing notification - create new one
            $user->notify(new OverdueDeliveryNotification($service, $daysOverdue));

            Log::info('New overdue notification created', [
                'user_id' => $user->id,
                'service' => $serviceType,
                'service_id' => $serviceId,
                'days_overdue' => $daysOverdue
            ]);
        }
    }

    /**
     * Update existing notification with new data
     */
    protected function updateExistingNotification($notification, int $daysOverdue): void
    {
        $data = json_decode($notification->data, true);

        // Update critical fields
        $data['days_overdue'] = $daysOverdue;
        $data['last_updated'] = now()->toDateTimeString();

        // Track update count
        $updateCount = ($notification->update_count ?? 0) + 1;
        $data['update_count'] = $updateCount;

        // Update priority based on new days overdue
        if ($daysOverdue >= 14) {
            $priority = 'urgent';
        } elseif ($daysOverdue >= 7) {
            $priority = 'high';
        } elseif ($daysOverdue >= 3) {
            $priority = 'normal';
        } else {
            $priority = 'low';
        }

        $data['priority'] = $priority;

        // Update message
        $serviceType = ucfirst($data['service_type']);
        $uniqueId = $data['service_uniqueid'];
        $data['message'] = "{$serviceType} #{$uniqueId} is {$daysOverdue} days overdue for delivery (updated {$updateCount}x)";

        DB::table('notifications')
            ->where('id', $notification->id)
            ->update([
                'data' => json_encode($data),
                'updated_at' => now(),
                'last_updated_at' => now(),
                'update_count' => $updateCount,
                'priority' => $priority,
            ]);
    }

    /**
     * Clean up notifications for delivered items
     * This runs BEFORE creating new notifications to keep database clean
     */
    public function cleanupDeliveredNotifications(): array
    {
        $stats = [
            'carpet' => 0,
            'laundry' => 0,
        ];

        // Cleanup carpet notifications
        $deliveredCarpetIds = Carpet::where('delivered', 'Delivered')->pluck('id')->toArray();

        if (!empty($deliveredCarpetIds)) {
            $deleted = DB::table('notifications')
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereRaw("JSON_EXTRACT(data, '$.service_type') = 'carpet'")
                ->where(function($query) use ($deliveredCarpetIds) {
                    foreach ($deliveredCarpetIds as $carpetId) {
                        $query->orWhereRaw("JSON_EXTRACT(data, '$.service_id') = ?", [$carpetId]);
                    }
                })
                ->delete();

            $stats['carpet'] = $deleted;
        }

        // Cleanup laundry notifications
        $deliveredLaundryIds = Laundry::where('delivered', 'Delivered')->pluck('id')->toArray();

        if (!empty($deliveredLaundryIds)) {
            $deleted = DB::table('notifications')
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereRaw("JSON_EXTRACT(data, '$.service_type') = 'laundry'")
                ->where(function($query) use ($deliveredLaundryIds) {
                    foreach ($deliveredLaundryIds as $laundryId) {
                        $query->orWhereRaw("JSON_EXTRACT(data, '$.service_id') = ?", [$laundryId]);
                    }
                })
                ->delete();

            $stats['laundry'] = $deleted;
        }

        if ($stats['carpet'] + $stats['laundry'] > 0) {
            Log::info('Cleaned up notifications for delivered items', $stats);
        }

        return $stats;
    }

    /**
     * Clean up expired notifications (TTL)
     */
    public function cleanupExpiredNotifications(): int
    {
        $deleted = DB::table('notifications')
            ->where('expires_at', '<', now())
            ->whereNotNull('expires_at')
            ->delete();

        if ($deleted > 0) {
            Log::info('Cleaned up expired notifications', ['count' => $deleted]);
        }

        return $deleted;
    }

    /**
     * Check if notification already sent today (deduplication)
     */
    public function wasNotifiedToday(User $user, string $serviceType, int $serviceId): bool
    {
        return DB::table('notifications')
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->where('notifiable_id', $user->id)
            ->whereRaw("JSON_EXTRACT(data, '$.service_type') = ?", [$serviceType])
            ->whereRaw("JSON_EXTRACT(data, '$.service_id') = ?", [$serviceId])
            ->whereDate('created_at', today())
            ->exists();
    }

    /**
     * Check if user exceeded daily notification limit
     */
    public function hasExceededDailyLimit(User $user): bool
    {
        $preferences = $user->getNotificationPreferences();
        $maxPerDay = $preferences->max_notifications_per_day;

        $todayCount = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        return $todayCount >= $maxPerDay;
    }

    /**
     * Remove orphaned notifications (items no longer exist)
     */
    public function cleanupOrphanedNotifications(): array
    {
        $stats = ['carpet' => 0, 'laundry' => 0];

        // Get carpet notification IDs using select with alias, then pluck
        $carpetNotificationIds = DB::table('notifications')
            ->select(DB::raw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) as service_id"))
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->whereRaw("JSON_EXTRACT(data, '$.service_type') = 'carpet'")
            ->pluck('service_id')
            ->unique()
            ->filter() // Remove nulls
            ->toArray();

        $existingCarpetIds = Carpet::pluck('id')->toArray();
        $orphanedCarpetIds = array_diff($carpetNotificationIds, $existingCarpetIds);

        if (!empty($orphanedCarpetIds)) {
            $deleted = DB::table('notifications')
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereRaw("JSON_EXTRACT(data, '$.service_type') = 'carpet'")
                ->where(function($query) use ($orphanedCarpetIds) {
                    foreach ($orphanedCarpetIds as $orphanedId) {
                        $query->orWhereRaw("JSON_EXTRACT(data, '$.service_id') = ?", [$orphanedId]);
                    }
                })
                ->delete();

            $stats['carpet'] = $deleted;
        }

        // Same for laundry
        $laundryNotificationIds = DB::table('notifications')
            ->select(DB::raw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) as service_id"))
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->whereRaw("JSON_EXTRACT(data, '$.service_type') = 'laundry'")
            ->pluck('service_id')
            ->unique()
            ->filter() // Remove nulls
            ->toArray();

        $existingLaundryIds = Laundry::pluck('id')->toArray();
        $orphanedLaundryIds = array_diff($laundryNotificationIds, $existingLaundryIds);

        if (!empty($orphanedLaundryIds)) {
            $deleted = DB::table('notifications')
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereRaw("JSON_EXTRACT(data, '$.service_type') = 'laundry'")
                ->where(function($query) use ($orphanedLaundryIds) {
                    foreach ($orphanedLaundryIds as $orphanedId) {
                        $query->orWhereRaw("JSON_EXTRACT(data, '$.service_id') = ?", [$orphanedId]);
                    }
                })
                ->delete();

            $stats['laundry'] = $deleted;
        }

        if ($stats['carpet'] + $stats['laundry'] > 0) {
            Log::info('Cleaned up orphaned notifications', $stats);
        }

        return $stats;
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => DB::table('notifications')->count(),
            'unread' => DB::table('notifications')->whereNull('read_at')->count(),
            'overdue' => DB::table('notifications')
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->count(),
            'expired' => DB::table('notifications')
                ->where('expires_at', '<', now())
                ->whereNotNull('expires_at')
                ->count(),
            'by_priority' => [
                'urgent' => DB::table('notifications')->where('priority', 'urgent')->count(),
                'high' => DB::table('notifications')->where('priority', 'high')->count(),
                'normal' => DB::table('notifications')->where('priority', 'normal')->count(),
                'low' => DB::table('notifications')->where('priority', 'low')->count(),
            ],
        ];
    }
}
