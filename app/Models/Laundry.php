<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laundry extends Model
{
    use HasFactory, Auditable, SoftDeletes;
    
    protected $fillable = [
        'name',
        'phone',
        'location',
        'unique_id',
        'date_received',
        'date_delivered',
        'quantity',
        'item_description',
        'weight',
        'price',
        'discount',
        'total',
        'delivered',
        'payment_status',
        'follow_up_due_at',
        'follow_up_stage',
        'last_notified_at',
        'resolved_at',
    ];

    protected function getAuditTags(): array
    {
        return [
            'service_type' => 'laundry',
            'uniqueid' => $this->uniqueid ?? null,
        ];
    }
}
