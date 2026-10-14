<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mpesa extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope());
    }

    protected $fillable = [
        'branch_id',
        'date',
        'cash',
        'float',
        'working',
        'account',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
