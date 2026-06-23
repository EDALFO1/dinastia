<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected static function booted()
    {
        // 🔒 FILTRO AUTOMÁTICO POR EMPRESA
        static::addGlobalScope('empresa', function ($query) {
            if (session()->has('empresa_id')) {
                $query->where('empresa_id', session('empresa_id'));
            }
        });

        // 💾 ASIGNAR EMPRESA AUTOMÁTICAMENTE
        static::creating(function ($model) {
            if (session()->has('empresa_id') && empty($model->empresa_id)) {
                $model->empresa_id = session('empresa_id');
            }
        });
    }
}