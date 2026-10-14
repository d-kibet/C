<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Console commands (queue worker, scheduler) run without authentication.
        // They must see all branches — e.g. the overdue delivery check runs globally.
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        if ($user->branch_id !== null) {
            // Regular staff: hard-filter to their own branch.
            $builder->where($model->getTable() . '.branch_id', $user->branch_id);
            return;
        }

        // SuperAdmin: apply a branch context if they've selected one in the session.
        // No session key = no filter = they see all branches combined.
        $activeBranchId = session('active_branch_id');
        if ($activeBranchId) {
            $builder->where($model->getTable() . '.branch_id', $activeBranchId);
        }
    }
}
