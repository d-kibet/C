<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'unique_id',
        'price',
        'discount',
        'item_total',
        'delivered',
        'date_delivered',
        // Carpet fields
        'size',
        'multiplier',
        // Laundry fields
        'quantity',
        'item_description',
        'weight',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'discount'     => 'decimal:2',
        'item_total'   => 'decimal:2',
        'multiplier'   => 'decimal:2',
        'weight'       => 'decimal:2',
        'date_delivered' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isCarpet(): bool
    {
        return $this->order->type === 'carpet';
    }

    public function isLaundry(): bool
    {
        return $this->order->type === 'laundry';
    }
}
