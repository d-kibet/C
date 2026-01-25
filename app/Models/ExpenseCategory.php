<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'icon_class',
        'color_code',
        'budget_limit',
        'requires_approval',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'budget_limit' => 'decimal:2',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function getCurrentMonthExpenses()
    {
        return $this->expenses()
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
    }

    public function getBudgetUsagePercentage()
    {
        if (!$this->budget_limit) {
            return 0;
        }

        $currentSpent = $this->getCurrentMonthExpenses();
        return min(100, ($currentSpent / $this->budget_limit) * 100);
    }

    protected function getAuditTags(): array
    {
        return [
            'module' => 'expense_category',
            'category_name' => $this->name ?? null,
        ];
    }
}