<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\EmpresaScope;

class Afiliacion extends BaseModel
{
    protected $table = 'afiliaciones';

    protected $fillable = [

        'afiliado_id',
        'eps_id',
        'arl_id',
        'pension_id',
        'caja_id',

        'nivel_arl',

        'tipo_ibc',   // âœ… AGREGAR
        'ibc',        // âœ… AGREGAR

        'fecha_afiliacion',
        'fecha_retiro',

        'estado'

    ];

    public function afiliado()
    {
        return $this->belongsTo(Afiliado::class);
    }

    public function eps()
    {
        return $this->belongsTo(Eps::class);
    }

    public function pension()
    {
        return $this->belongsTo(Pension::class);
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }
    protected static function booted(): void
    {
        // Auto asignar empresa
        static::creating(function ($model) {
            if (session()->has('empresa_id')) {
                $model->empresa_id = session('empresa_id');
            }
        });

    }
    public function arlAfiliado()
{
    return $this->hasOne(ArlAfiliado::class, 'afiliado_id', 'afiliado_id');
}

public function arl()
{
    return $this->belongsTo(Arl::class, 'arl_id');
}
}