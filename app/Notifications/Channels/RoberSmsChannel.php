<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Services\RobermsService;

class RoberSmsChannel
{
    protected RobermsService $service;

    public function __construct(RobermsService $service)
    {
        $this->service = $service;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        // If the notification doesn’t implement toRoberSms(), skip.
        if (! method_exists($notification, 'toRoberSms')) {
            return;
        }

        // Grab the data for SMS
        $data = $notification->toRoberSms($notifiable);

        // Send via Roberms
        $response = $this->service->sendSms(
            $notifiable->phone,               // the user's phone number
            $data['message'],                 // the SMS body
            $data['unique_identifier']        // for tracking
        );

        // Optionally, you could log $response->status() or check success
    }
}
