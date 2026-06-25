<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope for multi-tenant filtering by empresa_id
 *
 * Automatically filters all queries to only return records
 * belonging to the current empresa from the session.
 */
class EmpresaScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply filter if user is authenticated
        if (!auth()->check()) {
            return;
        }

        // Skip filtering for admin users (if needed)
        // Uncomment if admin should see all data across tenants
        // if (auth()->user()->isAdmin()) {
        //     return;
        // }

        // Apply the tenant filter
        $empresaId = session('empresa_id');
        if ($empresaId !== null) {
            $table = $model->getTable();
            $builder->where("{$table}.empresa_id", '=', $empresaId);
        }
    }

    /**
     * Remove the scope from a query builder
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function remove(Builder $builder, Model $model): void
    {
        $column = "{$model->getTable()}.empresa_id";

        $query = $builder->getQuery();

        if ($query->wheres) {
            foreach ($query->wheres as $key => $where) {
                if (isset($where['column']) && $where['column'] === $column) {
                    unset($query->wheres[$key]);
                    $query->wheres = array_values($query->wheres);
                }
            }
        }
    }
}