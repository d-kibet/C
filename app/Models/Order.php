<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $fillable = [
        'order_number',
        'type',
        'name',
        'phone',
        'location',
        'date_received',
        'date_delivered',
        'payment_status',
        'transaction_code',
        'payment_date',
        'subtotal',
        'total',
        'notes',
        'follow_up_due_at',
        'follow_up_stage',
        'last_notified_at',
        'resolved_at',
    ];

    protected $casts = [
        'date_received'   => 'date',
        'date_delivered'  => 'date',
        'payment_date'    => 'date',
        'follow_up_due_at'=> 'date',
        'last_notified_at'=> 'datetime',
        'resolved_at'     => 'datetime',
        'subtotal'        => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('price');
        $this->total    = $this->items()->sum('item_total');
        $this->saveQuietly();
    }

    public function isFullyDelivered(): bool
    {
        return $this->items()->where('delivered', 'Not Delivered')->doesntExist();
    }

    public function isLocked(): bool
    {
        return $this->payment_status === 'Paid' && $this->isFullyDelivered();
    }

    public function deliveredCount(): int
    {
        return $this->items()->where('delivered', 'Delivered')->count();
    }

    public function itemCount(): int
    {
        return $this->items()->count();
    }

    public static function generateOrderNumber(): string
    {
        $today    = now()->format('Ymd');
        $lastOrder = static::whereDate('created_at', today())->orderBy('id', 'desc')->first();
        $sequence  = $lastOrder ? ((int) substr($lastOrder->order_number, -3)) + 1 : 1;
        return 'ORD-' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    protected function getAuditTags(): array
    {
        return [
            'service_type' => 'order',
            'order_number' => $this->order_number,
        ];
    }
}
