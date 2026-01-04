<?php

namespace App\Notifications\Channels;

use App\Services\RobermsSmsService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Exception;

class UnifiedSmsChannel
{
    protected $smsService;

    public function __construct(RobermsSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send the given notification with comprehensive error handling
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            // Check if user has SMS enabled in preferences
            if (method_exists($notifiable, 'getNotificationPreferences')) {
                $preferences = $notifiable->getNotificationPreferences();

                if (!$preferences->sms_enabled) {
                    Log::info('SMS notification skipped - user has SMS disabled', [
                        'user_id' => $notifiable->id,
                        'notification' => get_class($notification)
                    ]);
                    return;
                }

                // Check quiet hours
                if ($preferences->isInQuietHours()) {
                    Log::info('SMS notification delayed - user in quiet hours', [
                        'user_id' => $notifiable->id,
                        'notification' => get_class($notification)
                    ]);
                    // TODO: Could reschedule for later
                    return;
                }
            }

            // Get the phone number from the notifiable entity
            $phoneNumber = $notifiable->phone ?? $notifiable->routeNotificationFor('sms');

            if (!$phoneNumber) {
                Log::warning('SMS notification skipped - no phone number', [
                    'user_id' => $notifiable->id ?? null,
                    'notification' => get_class($notification)
                ]);
                return;
            }

            // Get the SMS message - support both toSms() and toRoberSms() methods
            $message = null;
            $uniqueIdentifier = null;

            if (method_exists($notification, 'toSms')) {
                $message = $notification->toSms($notifiable);

                if (method_exists($notification, 'getUniqueIdentifier')) {
                    $uniqueIdentifier = $notification->getUniqueIdentifier();
                }
            } elseif (method_exists($notification, 'toRoberSms')) {
                $data = $notification->toRoberSms($notifiable);
                $message = $data['message'] ?? null;
                $uniqueIdentifier = $data['unique_identifier'] ?? null;
            }

            if (!$message) {
                Log::warning('SMS notification skipped - no message content', [
                    'user_id' => $notifiable->id ?? null,
                    'notification' => get_class($notification)
                ]);
                return;
            }

            // Send the SMS with error handling
            $result = $this->smsService->sendSms(
                $phoneNumber,
                $message,
                $uniqueIdentifier,
                'automated'
            );

            if ($result['success']) {
                Log::info('SMS notification sent successfully', [
                    'user_id' => $notifiable->id ?? null,
                    'phone' => $phoneNumber,
                    'notification' => get_class($notification),
                    'identifier' => $uniqueIdentifier
                ]);
            } else {
                Log::error('SMS notification failed', [
                    'user_id' => $notifiable->id ?? null,
                    'phone' => $phoneNumber,
                    'notification' => get_class($notification),
                    'error' => $result['message']
                ]);

                // Don't throw exception - allow other notification channels to continue
                // The failure is already logged in sms_logs table by RobermsSmsService
            }

        } catch (Exception $e) {
            Log::error('Exception in UnifiedSmsChannel', [
                'user_id' => $notifiable->id ?? null,
                'notification' => get_class($notification),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Don't throw - graceful degradation
            // This prevents one failed SMS from breaking entire notification pipeline
        }
    }
}
