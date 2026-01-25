<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
class Expense extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'category_id',
        'subcategory',
        'vendor_name',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'transaction_reference',
        'receipt_image',
        'approved_by',
        'approval_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
                    ->whereYear('expense_date', now()->year);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'Approved');
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', 'Pending');
    }

    public function getFormattedAmountAttribute()
    {
        return 'KES ' . number_format($this->amount, 2);
    }

    public function requiresApproval()
    {
        return $this->category && $this->category->requires_approval;
    }

    protected function getAuditTags(): array
    {
        return [
            'module' => 'expense',
            'category' => $this->category->name ?? null,
            'amount' => $this->amount ?? null,
            'vendor' => $this->vendor_name ?? null,
        ];
    }

    public function getReceiptUrlAttribute()
    {
        if (!$this->receipt_image) {
            return null;
        }

        // Always use the route-based serving for consistency across environments
        return route('expense.receipt', ['expense' => $this->id]);
    }

    public function getDirectReceiptUrlAttribute()
    {
        if (!$this->receipt_image) {
            return null;
        }

        return asset('storage/' . $this->receipt_image);
    }

    public function hasValidReceipt()
    {
        if (!$this->receipt_image) {
            return false;
        }

        return Storage::disk('public')->exists($this->receipt_image);
    }
}