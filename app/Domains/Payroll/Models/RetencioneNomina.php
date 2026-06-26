<?php

namespace App\Domains\Payroll\Models;

use App\Models\BaseModel;
use App\Domains\Payroll\Enums\TipoRetencion;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RetencioneNomina extends BaseModel
{
    use HasFactory;

    protected $table = 'retenciones_nomina';

    protected $fillable = [
        'empresa_id',
        'recibo_id',
        'tipo_retencion',
        'base_calculo',
        'porcentaje',
        'valor_retencion',
        'concepto',
    ];

    protected $casts = [
        'tipo_retencion' => TipoRetencion::class,
        'base_calculo' => 'decimal:2',
        'porcentaje' => 'decimal:4',
        'valor_retencion' => 'decimal:2',
    ];

    public function recibo()
    {
        return $this->belongsTo(\App\Models\Recibo::class);
    }

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }

    /**
     * Calcular retenciones automáticamente
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->base_calculo && $model->porcentaje) {
                $model->valor_retencion = $model->base_calculo * ($model->porcentaje / 100);
            }

            if (session()->has('empresa_id')) {
                $model->empresa_id = session('empresa_id');
            }
        });

        static::updating(function ($model) {
            if ($model->base_calculo && $model->porcentaje) {
                $model->valor_retencion = $model->base_calculo * ($model->porcentaje / 100);
            }
        });
    }

    /**
     * Calcular renta según tabla colombiana (UVT 2024)
     * Aproximado a tarifa 2024
     */
    public static function calcularRenta($salario): float
    {
        // Simplificado - usar tabla real en producción
        if ($salario <= 1300000) {
            return 0;
        } elseif ($salario <= 2300000) {
            return ($salario - 1300000) * 0.19;
        } elseif ($salario <= 4100000) {
            return 190000 + ($salario - 2300000) * 0.28;
        } else {
            return 694000 + ($salario - 4100000) * 0.33;
        }
    }
}
