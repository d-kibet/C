<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_enabled',
        'sms_enabled',
        'database_enabled',
        'overdue_notifications',
        'payment_reminders',
        'pickup_notifications',
        'followup_reminders',
        'quiet_hours_start',
        'quiet_hours_end',
        'overdue_notification_interval',
        'max_notifications_per_day',
        'daily_digest',
        'daily_digest_time',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'database_enabled' => 'boolean',
        'overdue_notifications' => 'boolean',
        'payment_reminders' => 'boolean',
        'pickup_notifications' => 'boolean',
        'followup_reminders' => 'boolean',
        'daily_digest' => 'boolean',
    ];

    /**
     * Get the user that owns the preferences
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user is in quiet hours (do not disturb)
     */
    public function isInQuietHours(): bool
    {
        if (!$this->quiet_hours_start || !$this->quiet_hours_end) {
            return false;
        }

        $now = Carbon::now()->format('H:i:s');
        $start = Carbon::parse($this->quiet_hours_start)->format('H:i:s');
        $end = Carbon::parse($this->quiet_hours_end)->format('H:i:s');

        // Handle quiet hours that span midnight (e.g., 22:00 to 08:00)
        if ($start > $end) {
            return $now >= $start || $now <= $end;
        }

        return $now >= $start && $now <= $end;
    }

    /**
     * Check if notification type is enabled
     */
    public function isNotificationTypeEnabled(string $type): bool
    {
        $mapping = [
            'overdue' => 'overdue_notifications',
            'payment' => 'payment_reminders',
            'pickup' => 'pickup_notifications',
            'followup' => 'followup_reminders',
        ];

        $field = $mapping[$type] ?? null;

        return $field ? $this->$field : true;
    }

    /**
     * Check if channel is enabled
     */
    public function isChannelEnabled(string $channel): bool
    {
        $field = $channel . '_enabled';

        return property_exists($this, $field) ? $this->$field : true;
    }

    /**
     * Get channels that user has enabled
     */
    public function getEnabledChannels(): array
    {
        $channels = [];

        if ($this->database_enabled) {
            $channels[] = 'database';
        }

        if ($this->email_enabled) {
            $channels[] = 'mail';
        }

        if ($this->sms_enabled) {
            $channels[] = 'sms';
        }

        return $channels;
    }

    /**
     * Create default preferences for a user
     */
    public static function createDefaults(User $user): self
    {
        return self::create([
            'user_id' => $user->id,
            'email_enabled' => true,
            'sms_enabled' => true,
            'database_enabled' => true,
            'overdue_notifications' => true,
            'payment_reminders' => true,
            'pickup_notifications' => true,
            'followup_reminders' => true,
            'overdue_notification_interval' => 5,
            'max_notifications_per_day' => 50,
            'daily_digest' => false,
        ]);
    }
}
