<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carpet extends Model
{
    use HasFactory, Auditable;
    
    protected $fillable = [
        'uniqueid',
        'name',
        'size',
        'price',
        'phone',
        'location',
        'date_received',
        'date_delivered',
        'payment_status',
        'transaction_code',
        'delivered',
        'follow_up_due_at',
        'follow_up_stage',
        'last_notified_at',
        'resolved_at',
    ];

    protected function getAuditTags(): array
    {
        return [
            'service_type' => 'carpet',
            'uniqueid' => $this->uniqueid ?? null,
        ];
    }
}
