<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laundry extends Model
{
    use HasFactory, Auditable;
    
    protected $guarded = [];

    protected function getAuditTags(): array
    {
        return [
            'service_type' => 'laundry',
            'uniqueid' => $this->uniqueid ?? null,
        ];
    }
}
