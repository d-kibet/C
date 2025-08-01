<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditTrail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'tags' => 'array',
    ];

    /**
     * Get the auditable model that the audit trail belongs to.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the display name for the event.
     */
    public function getEventDisplayAttribute(): string
    {
        return match ($this->event) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'login' => 'Logged In',
            'logout' => 'Logged Out',
            'payment_updated' => 'Payment Updated',
            'delivery_updated' => 'Delivery Updated',
            'status_changed' => 'Status Changed',
            default => ucfirst($this->event),
        };
    }

    /**
     * Get the model type display name.
     */
    public function getModelDisplayAttribute(): string
    {
        return match ($this->auditable_type) {
            'App\Models\Carpet' => 'Carpet',
            'App\Models\Laundry' => 'Laundry',
            'App\Models\User' => 'User',
            'App\Models\Mpesa' => 'M-Pesa Transaction',
            default => class_basename($this->auditable_type),
        };
    }

    /**
     * Get the display ID for the auditable model (uniqueid if available, otherwise ID).
     */
    public function getDisplayIdAttribute(): string
    {
        if ($this->auditable) {
            // For Carpet models, use uniqueid
            if ($this->auditable_type === 'App\Models\Carpet' && isset($this->auditable->uniqueid)) {
                return $this->auditable->uniqueid;
            }
            // For Laundry models, use unique_id
            if ($this->auditable_type === 'App\Models\Laundry' && isset($this->auditable->unique_id)) {
                return $this->auditable->unique_id;
            }
            // For User models, use name or email
            if ($this->auditable_type === 'App\Models\User') {
                return $this->auditable->name ?? $this->auditable->email ?? '#' . $this->auditable_id;
            }
        }
        
        // Fallback to regular ID with # prefix
        return '#' . $this->auditable_id;
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope for filtering by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for filtering by event.
     */
    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope for filtering by model type.
     */
    public function scopeByModel($query, $modelType)
    {
        return $query->where('auditable_type', $modelType);
    }
}