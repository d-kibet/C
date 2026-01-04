<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueDeliveryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $service;
    public $daysOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct($service, $daysOverdue)
    {
        $this->service = $service;
        $this->daysOverdue = $daysOverdue;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database']; // Only database notifications
    }


    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $serviceType = class_basename($this->service);

        return [
            'type' => 'overdue_delivery',
            'service_type' => strtolower($serviceType),
            'service_id' => $this->service->id,
            'service_uniqueid' => $this->service->uniqueid ?? $this->service->unique_id,
            'days_overdue' => $this->daysOverdue,
            'customer_phone' => $this->service->phone,
            'location' => $this->service->location,
            'expected_date' => $this->service->date_received ?
                \Carbon\Carbon::parse($this->service->date_received)->addDays(3)->format('Y-m-d') :
                null,
            'message' => "{$serviceType} #" . ($this->service->uniqueid ?? $this->service->unique_id) . " is {$this->daysOverdue} days overdue for delivery",
            'action_url' => "/details/" . strtolower($serviceType) . "/{$this->service->id}",
            'priority' => $this->getPriority(),
            'expires_at' => now()->addDays(30)->toDateTimeString(), // Auto-expire after 30 days
        ];
    }

    /**
     * Determine priority based on days overdue
     */
    protected function getPriority(): string
    {
        if ($this->daysOverdue >= 14) {
            return 'urgent';
        } elseif ($this->daysOverdue >= 7) {
            return 'high';
        } elseif ($this->daysOverdue >= 3) {
            return 'normal';
        }

        return 'low';
    }

    /**
     * Determine if should be queued based on notification preferences
     */
    public function shouldSend($notifiable): bool
    {
        // Check if user has overdue notifications enabled
        if (method_exists($notifiable, 'getNotificationPreferences')) {
            $preferences = $notifiable->getNotificationPreferences();
            return $preferences->isNotificationTypeEnabled('overdue');
        }

        return true;
    }
}