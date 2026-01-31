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
        // Using CAST to ensure proper type comparison for service_id
        $existingNotification = DB::table('notifications')
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', 'App\\Models\\User')
            ->whereNull('read_at')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = ?", [strtolower($serviceType)])
            ->whereRaw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) = ?", [(int)$serviceId])
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
     * Uses batched processing to avoid lock timeouts
     */
    public function cleanupDeliveredNotifications(): array
    {
        $stats = [
            'carpet' => 0,
            'laundry' => 0,
        ];

        // Cleanup carpet notifications in batches
        $deliveredCarpetIds = Carpet::where('delivered', 'Delivered')->pluck('id')->toArray();

        if (!empty($deliveredCarpetIds)) {
            // Process in batches of 50 to avoid lock timeout
            foreach (array_chunk($deliveredCarpetIds, 50) as $batch) {
                $deleted = DB::table('notifications')
                    ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = 'carpet'")
                    ->whereRaw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) IN (" . implode(',', $batch) . ")")
                    ->delete();

                $stats['carpet'] += $deleted;
            }
        }

        // Cleanup laundry notifications in batches
        $deliveredLaundryIds = Laundry::where('delivered', 'Delivered')->pluck('id')->toArray();

        if (!empty($deliveredLaundryIds)) {
            foreach (array_chunk($deliveredLaundryIds, 50) as $batch) {
                $deleted = DB::table('notifications')
                    ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = 'laundry'")
                    ->whereRaw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) IN (" . implode(',', $batch) . ")")
                    ->delete();

                $stats['laundry'] += $deleted;
            }
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
     * Uses batched processing to avoid lock timeouts
     */
    public function cleanupOrphanedNotifications(): array
    {
        $stats = ['carpet' => 0, 'laundry' => 0];

        // Get carpet notification IDs
        $carpetNotificationIds = DB::table('notifications')
            ->select(DB::raw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) as service_id"))
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = 'carpet'")
            ->pluck('service_id')
            ->unique()
            ->filter()
            ->toArray();

        $existingCarpetIds = Carpet::pluck('id')->toArray();
        $orphanedCarpetIds = array_diff($carpetNotificationIds, $existingCarpetIds);

        if (!empty($orphanedCarpetIds)) {
            // Process in batches
            foreach (array_chunk($orphanedCarpetIds, 50) as $batch) {
                $deleted = DB::table('notifications')
                    ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = 'carpet'")
                    ->whereRaw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) IN (" . implode(',', $batch) . ")")
                    ->delete();

                $stats['carpet'] += $deleted;
            }
        }

        // Same for laundry
        $laundryNotificationIds = DB::table('notifications')
            ->select(DB::raw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) as service_id"))
            ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = 'laundry'")
            ->pluck('service_id')
            ->unique()
            ->filter()
            ->toArray();

        $existingLaundryIds = Laundry::pluck('id')->toArray();
        $orphanedLaundryIds = array_diff($laundryNotificationIds, $existingLaundryIds);

        if (!empty($orphanedLaundryIds)) {
            foreach (array_chunk($orphanedLaundryIds, 50) as $batch) {
                $deleted = DB::table('notifications')
                    ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = 'laundry'")
                    ->whereRaw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) IN (" . implode(',', $batch) . ")")
                    ->delete();

                $stats['laundry'] += $deleted;
            }
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

    /**
     * Consolidate duplicate notifications - keeps only the most recent per item per user
     * This is a cleanup method to fix existing duplicates
     */
    public function consolidateDuplicates(): array
    {
        $stats = ['carpet' => 0, 'laundry' => 0];

        foreach (['carpet', 'laundry'] as $serviceType) {
            // Find all unique combinations of user + service_id with multiple notifications
            $duplicates = DB::table('notifications')
                ->select([
                    'notifiable_id',
                    DB::raw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) as service_id"),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('MAX(created_at) as latest_created')
                ])
                ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = ?", [$serviceType])
                ->groupBy('notifiable_id', DB::raw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED)"))
                ->having('count', '>', 1)
                ->get();

            foreach ($duplicates as $duplicate) {
                // Delete all but the most recent notification for this item/user combo
                $deleted = DB::table('notifications')
                    ->where('type', 'App\\Notifications\\OverdueDeliveryNotification')
                    ->where('notifiable_id', $duplicate->notifiable_id)
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.service_type')) = ?", [$serviceType])
                    ->whereRaw("CAST(JSON_EXTRACT(data, '$.service_id') AS UNSIGNED) = ?", [$duplicate->service_id])
                    ->where('created_at', '<', $duplicate->latest_created)
                    ->delete();

                $stats[$serviceType] += $deleted;
            }
        }

        if ($stats['carpet'] + $stats['laundry'] > 0) {
            Log::info('Consolidated duplicate notifications', $stats);
        }

        return $stats;
    }

    /**
     * Full cleanup - runs all cleanup methods
     */
    public function runFullCleanup(): array
    {
        $stats = [
            'delivered' => $this->cleanupDeliveredNotifications(),
            'expired' => $this->cleanupExpiredNotifications(),
            'orphaned' => $this->cleanupOrphanedNotifications(),
            'duplicates' => $this->consolidateDuplicates(),
        ];

        Log::info('Full notification cleanup completed', $stats);

        return $stats;
    }
}
