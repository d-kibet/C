<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RobermsService
{
    /**
     * Fetch and cache the Roberms access token for 55 minutes.
     */
    public function getAccessToken(): string
    {
        return Cache::remember('roberms_access_token', 55 * 60, function() {
            $response = Http::post(config('services.roberms.token_url'), [
                'consumer_key'      => config('services.roberms.consumer_key'),
                'consumer_password' => config('services.roberms.consumer_pass'),
            ]);

            if (! $response->successful() || empty($response['token'])) {
                throw new \Exception('Failed to get Roberms access token: ' . $response->body());
            }

            return $response['token'];
        });
    }

    /**
     * Send a single SMS via Roberms.
     *
     * @param  string  $to           E.164 phone number (e.g. +2547XXXXXXXX)
     * @param  string  $message      The SMS body
     * @param  string  $uniqueId     A unique identifier for tracking
     * @return \Illuminate\Http\Client\Response
     */
    // in app/Services/RobermsService.php

public function sendSms(string $to, string $message, string $uniqueId)
{
    $token = $this->getAccessToken();

    // Roberms expects no “+”, just country code + number
    $toClean = ltrim($to, '+');

    $response = Http::withHeaders([
            'Authorization' => 'Token ' . $token,
            'Content-Type'  => 'application/json',
        ])
        ->post(config('services.roberms.sms_url'), [
            'message'           => $message,
            'phone_number'      => $toClean,
            'sender_name'       => config('services.roberms.sender_name'),
            'unique_identifier' => $uniqueId,
        ]);

    Log::info('Roberms SMS Response', [
        'to'      => $toClean,
        'status'  => $response->status(),
        'body'    => $response->body(),
    ]);

    return $response;
}

}
