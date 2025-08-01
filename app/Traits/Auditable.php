<?php

namespace App\Traits;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->auditActivity('created');
        });

        static::updated(function ($model) {
            $model->auditActivity('updated');
        });

        static::deleted(function ($model) {
            $model->auditActivity('deleted');
        });
    }

    /**
     * Log audit activity for the model.
     */
    public function auditActivity(string $event, array $customValues = [])
    {
        $oldValues = [];
        $newValues = [];

        if ($event === 'updated') {
            $oldValues = $this->getOriginal();
            $newValues = $this->getAttributes();
            
            // Remove unchanged values
            $changes = array_diff_assoc($newValues, $oldValues);
            if (empty($changes)) {
                return; // No actual changes
            }
            
            $newValues = $changes;
            $oldValues = array_intersect_key($oldValues, $changes);
        } elseif ($event === 'created') {
            $newValues = $this->getAttributes();
        } elseif ($event === 'deleted') {
            $oldValues = $this->getAttributes();
        }

        // Merge custom values if provided
        if (!empty($customValues)) {
            $newValues = array_merge($newValues, $customValues);
        }

        AuditTrail::create([
            'user_type' => Auth::check() ? get_class(Auth::user()) : null,
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'old_values' => !empty($oldValues) ? $this->filterSensitiveData($oldValues) : null,
            'new_values' => !empty($newValues) ? $this->filterSensitiveData($newValues) : null,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'tags' => $this->getAuditTags(),
        ]);
    }

    /**
     * Log a custom audit event.
     */
    public function logAuditEvent(string $event, array $data = [])
    {
        $this->auditActivity($event, $data);
    }

    /**
     * Filter out sensitive data from audit logs.
     */
    protected function filterSensitiveData(array $data): array
    {
        $sensitiveFields = ['password', 'remember_token', 'api_token'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[FILTERED]';
            }
        }

        return $data;
    }

    /**
     * Get audit tags for the model.
     */
    protected function getAuditTags(): array
    {
        return [];
    }

    /**
     * Get all audit trails for this model.
     */
    public function auditTrails()
    {
        return $this->morphMany(AuditTrail::class, 'auditable');
    }
}