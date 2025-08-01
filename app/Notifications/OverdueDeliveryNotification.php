<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueDeliveryNotification extends Notification
{
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
        ];
    }
}