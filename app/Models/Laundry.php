<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laundry extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope());
    }

    protected $fillable = [
        'branch_id',
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
        'transaction_code',
        'follow_up_due_at',
        'follow_up_stage',
        'last_notified_at',
        'resolved_at',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    protected function getAuditTags(): array
    {
        return [
            'service_type' => 'laundry',
            'uniqueid' => $this->uniqueid ?? null,
        ];
    }
}
