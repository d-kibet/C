<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'phone',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function carpets(): HasMany
    {
        return $this->hasMany(Carpet::class);
    }

    public function laundries(): HasMany
    {
        return $this->hasMany(Laundry::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function mpesas(): HasMany
    {
        return $this->hasMany(Mpesa::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
