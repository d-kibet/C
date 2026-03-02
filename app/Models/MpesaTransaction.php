<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    protected $fillable = [
        'service_type',
        'service_id',
        'phone',
        'amount',
        'account_reference',
        'checkout_request_id',
        'merchant_request_id',
        'mpesa_receipt_number',
        'result_code',
        'result_desc',
        'status',
    ];

    public function service()
    {
        if ($this->service_type === 'carpet') {
            return $this->belongsTo(Carpet::class, 'service_id');
        }
        return $this->belongsTo(Laundry::class, 'service_id');
    }
}
