<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class RobermsSmsService
{
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerPassword;
    protected $senderName;

    public function __construct()
    {
        $this->baseUrl = config('sms.roberms.base_url');
        $this->consumerKey = config('sms.roberms.consumer_key');
        $this->consumerPassword = config('sms.roberms.consumer_password');
        $this->senderName = config('sms.roberms.sender_name');
    }

    /**
     * Get access token from Roberms API
     * Token is cached for 50 minutes (expires in 60 minutes)
     */
    public function getAccessToken()
    {
        return Cache::remember('roberms_access_token', 3000, function () {
            try {
                $response = Http::post($this->baseUrl . '/get/access/token', [
                    'consumer_key' => $this->consumerKey,
                    'consumer_password' => $this->consumerPassword,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['token'] ?? $data['access_token'] ?? null;
                }

                Log::error('Roberms: Failed to get access token', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return null;
            } catch (Exception $e) {
                Log::error('Roberms: Exception getting access token', [
                    'message' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    /**
     * Send a simple SMS
     *
     * @param string $phoneNumber - Phone number (format: 254XXXXXXXXX)
     * @param string $message - Message content
     * @param string|null $uniqueIdentifier - Optional unique identifier
     * @param string $type - Type of SMS (manual, automated, bulk, scheduled)
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function sendSms($phoneNumber, $message, $uniqueIdentifier = null, $type = 'manual')
    {
        $identifier     = $uniqueIdentifier ?? uniqid('sms_');
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);
        $logPhone       = $formattedPhone ?? $phoneNumber;

        // PRE-LOG as pending — record exists in reports before anything is sent
        $logEntry = $this->createPendingLog($logPhone, $message, $type, $identifier);

        $token = $this->getAccessToken();

        if (!$token) {
            $this->updateLog($logEntry, 'failed', 'pending', 'Failed to get access token');
            return ['success' => false, 'message' => 'Failed to get access token', 'data' => null];
        }

        if (!$formattedPhone) {
            $this->updateLog($logEntry, 'failed', 'failed', 'Invalid phone number format');
            return ['success' => false, 'message' => 'Invalid phone number format', 'data' => null];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $token,
                'Content-Type'  => 'application/json',
            ])->post($this->baseUrl . '/send/simple/sms', [
                'message'           => $message,
                'phone_number'      => $formattedPhone,
                'sender_name'       => $this->senderName,
                'unique_identifier' => $identifier,
            ]);

            if ($response->successful()) {
                Log::info('Roberms: SMS sent successfully', [
                    'phone'      => $formattedPhone,
                    'identifier' => $identifier,
                ]);
                $this->updateLog($logEntry, 'sent', 'submitted', null, $response->json());
                return ['success' => true, 'message' => 'SMS sent successfully', 'data' => $response->json()];
            }

            Log::error('Roberms: Failed to send SMS', [
                'phone'  => $formattedPhone,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            $this->updateLog($logEntry, 'failed', 'failed', $response->body(), $response->json());
            return ['success' => false, 'message' => 'Failed to send SMS: ' . $response->body(), 'data' => $response->json()];

        } catch (Exception $e) {
            Log::error('Roberms: Exception sending SMS', [
                'phone'   => $formattedPhone,
                'message' => $e->getMessage(),
            ]);
            $this->updateLog($logEntry, 'failed', 'failed', 'Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage(), 'data' => null];
        }
    }

    /**
     * Create a pending log entry before the API call.
     */
    protected function createPendingLog($phone, $message, $type, $identifier)
    {
        try {
            return \App\Models\SmsLog::create([
                'user_id'           => auth()->id(),
                'phone_number'      => $phone,
                'message'           => $message,
                'type'              => $type,
                'status'            => 'pending',
                'delivery_status'   => 'pending',
                'unique_identifier' => $identifier,
                'sent_at'           => null,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to pre-log SMS', ['error' => $e->getMessage(), 'phone' => $phone]);
            return null;
        }
    }

    /**
     * Update a log entry after the API call resolves.
     */
    protected function updateLog($logEntry, $status, $deliveryStatus, $errorMessage = null, $responseData = null)
    {
        if (!$logEntry) return;
        try {
            $logEntry->update([
                'status'          => $status,
                'delivery_status' => $deliveryStatus,
                'error_message'   => $errorMessage,
                'response_data'   => $responseData ? json_encode($responseData) : $logEntry->response_data,
                'sent_at'         => $status === 'sent' ? now() : null,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update SMS log', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send bulk SMS to multiple recipients
     *
     * @param array $recipients - Array of phone numbers
     * @param string $message - Message content
     * @return array ['total' => int, 'sent' => int, 'failed' => int, 'results' => array]
     */
    public function sendBulkSms(array $recipients, $message)
    {
        $results = [
            'total' => count($recipients),
            'sent' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($recipients as $phoneNumber) {
            $result = $this->sendSms($phoneNumber, $message, null, 'bulk');

            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'phone' => $phoneNumber,
                'status' => $result['success'] ? 'sent' : 'failed',
                'message' => $result['message']
            ];

            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 seconds
        }

        return $results;
    }

    /**
     * Get credit balance
     *
     * @return array ['success' => bool, 'balance' => float|null]
     */
    public function getCreditBalance()
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return [
                'success' => false,
                'balance' => null,
                'message' => 'Failed to get access token'
            ];
        }

        try {
            // Balance endpoint is at /sms/v1/credit/balance (not under /roberms/)
            $balanceUrl = 'https://roberms.co.ke/sms/v1/credit/balance';
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $token,
            ])->get($balanceUrl);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'balance' => $data['credit_balance'] ?? $data['balance'] ?? 0,
                    'message' => 'Balance retrieved successfully'
                ];
            }

            return [
                'success' => false,
                'balance' => null,
                'message' => 'Failed to get balance'
            ];
        } catch (Exception $e) {
            Log::error('Roberms: Exception getting balance', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'balance' => null,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to Kenyan format (254XXXXXXXXX)
     *
     * @param string $phoneNumber
     * @return string|null
     */
    protected function formatPhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Handle different formats
        if (strlen($phoneNumber) == 10 && substr($phoneNumber, 0, 1) == '0') {
            // 0712345678 -> 254712345678
            return '254' . substr($phoneNumber, 1);
        } elseif (strlen($phoneNumber) == 9) {
            // 712345678 -> 254712345678
            return '254' . $phoneNumber;
        } elseif (strlen($phoneNumber) == 12 && substr($phoneNumber, 0, 3) == '254') {
            // Already in correct format
            return $phoneNumber;
        }

        // Invalid format
        return null;
    }

    /**
     * Validate phone number
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function validatePhoneNumber($phoneNumber)
    {
        return $this->formatPhoneNumber($phoneNumber) !== null;
    }

    /**
     * Register delivery URL for SMS status callbacks
     *
     * @param string $deliveryUrl
     * @return array
     */
    public function registerDeliveryUrl($deliveryUrl)
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $token,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/register/urls', [
                'delivery_url' => $deliveryUrl,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Delivery URL registered successfully',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to register delivery URL',
                'data' => $response->json()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

}
