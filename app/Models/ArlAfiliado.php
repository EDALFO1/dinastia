<?php

namespace App\Models;

use App\Scopes\EmpresaScope;
use App\Models\EmpresaLaboral;

class ArlAfiliado extends BaseModel
{
    protected $table = 'arl_afiliados';

    protected $fillable = [
        'empresa_id',
        'documento_id',
        'numero',
        'nombre',
        'fecha_ingreso',
        'arl_id',
        'empresa_laboral_id',
        'base_cotizacion',
        'administracion',
        'estado',
        'fecha_retiro',
    ];

    protected $casts = [
        'fecha_ingreso'   => 'date',
        'fecha_retiro'    => 'date',
        'base_cotizacion' => 'decimal:2',
        'administracion'  => 'decimal:2',
        'estado'          => 'boolean',
    ];

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    public function arl()
    {
        return $this->belongsTo(Arl::class);
    }

    public function empresaLaboral()
    {
        return $this->belongsTo(EmpresaLaboral::class, 'empresa_laboral_id');
    }

    public function valorArl(): float
    {
        if (!$this->arl) {
            return 0;
        }
        $raw = (float) $this->base_cotizacion * ((float) $this->arl->porcentaje / 100);
        return ceil($raw / 100) * 100;
    }

    public function totalMensual(): float
    {
        return $this->valorArl() + (float) $this->administracion;
    }

    public static function rules($id = null): array
    {
        return [
            'documento_id'      => ['required', 'exists:documentos,id'],
            'numero'            => ['required', 'string', 'max:50'],
            'nombre'            => ['required', 'string', 'max:255'],
            'fecha_ingreso'     => ['required', 'date_format:Y-m-d'],
            'arl_id'            => ['required', 'exists:arls,id'],
            'empresa_laboral_id'=> ['nullable', 'exists:empresas_laborales,id'],
            'base_cotizacion'   => ['required', 'numeric', 'min:0'],
            'administracion'    => ['required', 'numeric', 'min:0'],
            'estado'            => ['nullable', 'boolean'],
            'fecha_retiro'      => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (session()->has('empresa_id')) {
                $model->empresa_id      = session('empresa_id');
                $model->override_parametros = true;
            }
        });

        static::addGlobalScope(new EmpresaScope);
    }
}
