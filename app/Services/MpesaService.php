<?php

namespace App\Services;

use App\Models\MpesaTransaction;
use App\Models\Carpet;
use App\Models\Laundry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    protected string $env;
    protected string $consumerKey;
    protected string $consumerSecret;
    protected string $shortcode;
    protected string $passkey;
    protected string $callbackUrl;
    protected array $urls;

    public function __construct()
    {
        $this->env = config('mpesa.env', 'sandbox');
        $this->consumerKey = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->shortcode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
        $this->callbackUrl = config('mpesa.callback_url');
        $this->urls = config("mpesa.{$this->env}");
    }

    /**
     * Get OAuth access token from Safaricom.
     */
    public function getAccessToken(): array
    {
        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get($this->urls['auth_url']);

            if ($response->successful() && $response->json('access_token')) {
                return ['success' => true, 'token' => $response->json('access_token')];
            }

            Log::error('M-Pesa: Failed to get access token', [
                'status' => $response->status(),
                'body' => $response->body(),
                'consumer_key_length' => strlen($this->consumerKey),
                'consumer_key_first5' => substr($this->consumerKey, 0, 5),
                'auth_url' => $this->urls['auth_url'],
            ]);

            $errorMsg = $response->json('errorMessage') ?? $response->json('error_description') ?? $response->body();

            return ['success' => false, 'error' => "Auth failed ({$response->status()}): {$errorMsg}"];
        } catch (\Exception $e) {
            Log::error('M-Pesa: Access token exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Connection error: ' . $e->getMessage()];
        }
    }

    /**
     * Initiate STK Push to customer's phone.
     */
    public function stkPush(string $phone, float $amount, string $serviceType, int $serviceId, string $accountReference = null): array
    {
        $auth = $this->getAccessToken();

        if (!$auth['success']) {
            return ['success' => false, 'message' => $auth['error']];
        }

        $accessToken = $auth['token'];

        // Format phone number: 07XXXXXXXX → 2547XXXXXXXX
        $phone = $this->formatPhone($phone);

        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        $accountReference = $accountReference ?: strtoupper($serviceType) . '-' . $serviceId;

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) ceil($amount),
            'PartyA' => $phone,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => "Payment for {$serviceType} #{$serviceId}",
        ];

        $response = Http::withToken($accessToken)->post($this->urls['stk_url'], $payload);

        if ($response->successful() && $response->json('ResponseCode') === '0') {
            // Save pending transaction
            $transaction = MpesaTransaction::create([
                'service_type' => $serviceType,
                'service_id' => $serviceId,
                'phone' => $phone,
                'amount' => $amount,
                'account_reference' => $accountReference,
                'checkout_request_id' => $response->json('CheckoutRequestID'),
                'merchant_request_id' => $response->json('MerchantRequestID'),
                'status' => 'pending',
            ]);

            return [
                'success' => true,
                'message' => 'STK push sent. Check your phone.',
                'transaction_id' => $transaction->id,
                'checkout_request_id' => $response->json('CheckoutRequestID'),
            ];
        }

        Log::error('M-Pesa: STK Push failed', [
            'status' => $response->status(),
            'body' => $response->json(),
            'shortcode' => $this->shortcode,
            'env' => $this->env,
        ]);

        $errorMsg = $response->json('errorMessage') ?? $response->json('ResponseDescription') ?? 'STK push failed.';

        return [
            'success' => false,
            'message' => "STK Push failed ({$response->status()}): {$errorMsg}",
        ];
    }

    /**
     * Handle the callback from Safaricom after payment.
     */
    public function handleCallback(array $data): void
    {
        $stkCallback = $data['Body']['stkCallback'] ?? null;

        if (!$stkCallback) {
            Log::warning('M-Pesa: Invalid callback data', $data);
            return;
        }

        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;
        $resultDesc = $stkCallback['ResultDesc'] ?? '';

        $transaction = MpesaTransaction::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$transaction) {
            Log::warning('M-Pesa: Transaction not found for callback', ['checkout_request_id' => $checkoutRequestId]);
            return;
        }

        if ($resultCode == 0) {
            // Payment successful — extract receipt number
            $receiptNumber = null;
            $callbackItems = $stkCallback['CallbackMetadata']['Item'] ?? [];

            foreach ($callbackItems as $item) {
                if ($item['Name'] === 'MpesaReceiptNumber') {
                    $receiptNumber = $item['Value'];
                    break;
                }
            }

            $transaction->update([
                'status' => 'completed',
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'mpesa_receipt_number' => $receiptNumber,
            ]);

            // Update the service record
            $this->updateServiceRecord($transaction, $receiptNumber);
        } else {
            // Payment failed or cancelled
            $status = $resultCode == 1032 ? 'cancelled' : 'failed';
            $transaction->update([
                'status' => $status,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
            ]);
        }
    }

    /**
     * Query STK push status from Safaricom.
     */
    public function queryStatus(string $checkoutRequestId): array
    {
        $auth = $this->getAccessToken();

        if (!$auth['success']) {
            return ['success' => false, 'message' => $auth['error']];
        }

        $accessToken = $auth['token'];

        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $response = Http::withToken($accessToken)->post($this->urls['query_url'], [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ]);

        return $response->json() ?? [];
    }

    /**
     * Update the carpet/laundry record after successful payment.
     */
    protected function updateServiceRecord(MpesaTransaction $transaction, ?string $receiptNumber): void
    {
        if ($transaction->service_type === 'order') {
            $record = \App\Models\Order::find($transaction->service_id);
        } elseif ($transaction->service_type === 'carpet') {
            $record = Carpet::find($transaction->service_id);
        } else {
            $record = Laundry::find($transaction->service_id);
        }

        if ($record) {
            $record->update([
                'payment_status'   => 'Paid',
                'transaction_code' => $receiptNumber,
                'payment_date'     => now()->toDateString(),
            ]);
        }
    }

    /**
     * Format phone to 2547XXXXXXXX format.
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);

        if (str_starts_with($phone, '+254')) {
            return substr($phone, 1);
        }

        if (str_starts_with($phone, '0')) {
            return '254' . substr($phone, 1);
        }

        if (str_starts_with($phone, '254')) {
            return $phone;
        }

        return '254' . $phone;
    }
}
