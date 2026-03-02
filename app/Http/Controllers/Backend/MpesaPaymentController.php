<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\MpesaTransaction;
use App\Models\Carpet;
use App\Models\Laundry;
use App\Services\MpesaService;
use Illuminate\Http\Request;

class MpesaPaymentController extends Controller
{
    /**
     * Initiate STK Push payment.
     */
    public function initiatePayment(Request $request, MpesaService $mpesa)
    {
        $validated = $request->validate([
            'service_type' => 'required|in:carpet,laundry,order',
            'service_id' => 'required|integer',
            'phone' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        // Verify the service record exists
        if ($validated['service_type'] === 'order') {
            $record = \App\Models\Order::findOrFail($validated['service_id']);
        } elseif ($validated['service_type'] === 'carpet') {
            $record = Carpet::findOrFail($validated['service_id']);
        } else {
            $record = Laundry::findOrFail($validated['service_id']);
        }

        // Check if already paid
        if ($record->payment_status === 'Paid') {
            return response()->json([
                'success' => false,
                'message' => 'This record is already marked as Paid.',
            ]);
        }

        // Check for pending transactions
        $pending = MpesaTransaction::where('service_type', $validated['service_type'])
            ->where('service_id', $validated['service_id'])
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(2))
            ->first();

        if ($pending) {
            return response()->json([
                'success' => false,
                'message' => 'A payment prompt was already sent. Please wait for the customer to respond.',
                'transaction_id' => $pending->id,
            ]);
        }

        $result = $mpesa->stkPush(
            $validated['phone'],
            $validated['amount'],
            $validated['service_type'],
            $validated['service_id']
        );

        return response()->json($result);
    }

    /**
     * Handle M-Pesa callback (public endpoint, no auth).
     */
    public function callback(Request $request, MpesaService $mpesa)
    {
        $data = $request->all();

        $mpesa->handleCallback($data);

        // Safaricom expects a response
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Check payment status (AJAX polling from frontend).
     */
    public function checkStatus($id)
    {
        $transaction = MpesaTransaction::findOrFail($id);

        return response()->json([
            'status' => $transaction->status,
            'mpesa_receipt_number' => $transaction->mpesa_receipt_number,
            'result_desc' => $transaction->result_desc,
        ]);
    }

    /**
     * View all M-Pesa payment transactions (admin only).
     */
    public function transactionHistory()
    {
        $transactions = MpesaTransaction::orderBy('created_at', 'desc')->paginate(50);

        return view('backend.mpesa.transactions', compact('transactions'));
    }
}
