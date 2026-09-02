<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DataScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check() || $model instanceof \App\Models\User) {
            return;
        }

        $user = auth()->user();

        // Super admin views everything
        if ($user->hasRole('super_admin')) {
            return;
        }

        // Group admin views all companies in their group
        if ($user->hasRole('group_admin')) {
            $groupId = $user->company?->group_id;

            if (! $groupId) {
                if ($user->company_id) {
                    $this->scopeByCompany($builder, $model, [$user->company_id]);
                }
                return;
            }

            $companyIds = \App\Models\Company::where('group_id', $groupId)->pluck('id')->toArray();
            $this->scopeByCompany($builder, $model, $companyIds);
            return;
        }

        // Default: Scope by user's company (if user has no company_id, block access / empty result)
        if ($user->company_id) {
            $this->scopeByCompany($builder, $model, [$user->company_id]);
        } else {
            $builder->whereRaw('1 = 0');
        }
    }

    protected function scopeByCompany(Builder $builder, Model $model, array $companyIds): void
    {
        if ($model instanceof \App\Models\Company) {
            $builder->whereIn('id', $companyIds);
        } elseif (in_array('company_id', $model->getFillable())) {
            $builder->whereIn($model->getTable().'.company_id', $companyIds);
        } elseif (method_exists($model, 'department')) {
            $builder->whereHas('department', function ($q) use ($companyIds) {
                $q->whereIn('company_id', $companyIds);
            });
        }
    }
}
