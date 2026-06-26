<?php

namespace App\Domains\Payroll\Models;

use App\Models\BaseModel;
use App\Domains\Payroll\Enums\TipoAporte;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CuotaAportes extends BaseModel
{
    use HasFactory;

    protected $table = 'cuotas_aportes';

    protected $fillable = [
        'empresa_id',
        'recibo_id',
        'tipo_aporte',
        'base_calculo',
        'porcentaje_empleado',
        'aporte_empleado',
        'porcentaje_empleador',
        'aporte_empleador',
        'total_aporte',
    ];

    protected $casts = [
        'tipo_aporte' => TipoAporte::class,
        'base_calculo' => 'decimal:2',
        'porcentaje_empleado' => 'decimal:4',
        'aporte_empleado' => 'decimal:2',
        'porcentaje_empleador' => 'decimal:4',
        'aporte_empleador' => 'decimal:2',
        'total_aporte' => 'decimal:2',
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
     * Calcular aportes automáticamente
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->base_calculo) {
                $model->aporte_empleado = $model->base_calculo * ($model->porcentaje_empleado / 100);
                $model->aporte_empleador = $model->base_calculo * ($model->porcentaje_empleador / 100);
                $model->total_aporte = $model->aporte_empleado + $model->aporte_empleador;
            }

            if (session()->has('empresa_id')) {
                $model->empresa_id = session('empresa_id');
            }
        });

        static::updating(function ($model) {
            if ($model->base_calculo) {
                $model->aporte_empleado = $model->base_calculo * ($model->porcentaje_empleado / 100);
                $model->aporte_empleador = $model->base_calculo * ($model->porcentaje_empleador / 100);
                $model->total_aporte = $model->aporte_empleado + $model->aporte_empleador;
            }
        });
    }

    /**
     * Crear cuotas estándar para un recibo
     */
    public static function crearCuotasEstandar($reciboId, $baseCalculo, $empresaId)
    {
        $aportes = [
            TipoAporte::AFP,
            TipoAporte::EPS,
            TipoAporte::ARL,
            TipoAporte::CAJA_COMPENSACION,
        ];

        foreach ($aportes as $tipo) {
            self::create([
                'recibo_id' => $reciboId,
                'tipo_aporte' => $tipo,
                'base_calculo' => $baseCalculo,
                'porcentaje_empleado' => $tipo->porcentajeEmpleado(),
                'porcentaje_empleador' => $tipo->porcentajeEmpleador(),
                'empresa_id' => $empresaId,
            ]);
        }
    }
}
