<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EmpresaScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (!auth()->check()) return;

        if (auth()->user()->role == 'admin') return;

        if (session()->has('empresa_id')) {
            $builder->where($model->getTable().'.empresa_id', session('empresa_id'));
        }
    }
}