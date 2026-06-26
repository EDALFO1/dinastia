<?php

namespace App\Domains\Payroll\Models;

use App\Models\BaseModel;
use App\Domains\Payroll\Enums\TipoNovedad;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NovedadNomina extends BaseModel
{
    use HasFactory;

    protected $table = 'novedades_nomina';

    protected $fillable = [
        'empresa_id',
        'afiliado_id',
        'tipo_novedad',
        'descripcion',
        'fecha_inicio',
        'fecha_final',
        'cantidad',
        'valor_unitario',
        'valor_total',
        'documento_soporte',
        'estado',
    ];

    protected $casts = [
        'tipo_novedad' => TipoNovedad::class,
        'fecha_inicio' => 'date',
        'fecha_final' => 'date',
        'cantidad' => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function afiliado()
    {
        return $this->belongsTo(\App\Models\Afiliado::class);
    }

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }

    /**
     * Calcular valor total basado en cantidad y valor unitario
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->cantidad && $model->valor_unitario) {
                $model->valor_total = $model->cantidad * $model->valor_unitario;
            }

            if (session()->has('empresa_id')) {
                $model->empresa_id = session('empresa_id');
            }
        });

        static::updating(function ($model) {
            if ($model->cantidad && $model->valor_unitario) {
                $model->valor_total = $model->cantidad * $model->valor_unitario;
            }
        });
    }

    public function isIncome(): bool
    {
        return in_array($this->tipo_novedad, [
            TipoNovedad::SALARIO_ORDINARIO,
            TipoNovedad::SALARIO_INTEGRAL,
            TipoNovedad::AUXILIO_TRANSPORTE,
            TipoNovedad::BONIFICACION,
            TipoNovedad::COMISION,
            TipoNovedad::PRIMA,
            TipoNovedad::CESANTIA,
            TipoNovedad::VACACIONES,
        ]);
    }

    public function isDeduction(): bool
    {
        return in_array($this->tipo_novedad, [
            TipoNovedad::DEDUCCION_VOLUNTARIA,
            TipoNovedad::EMBARGO,
            TipoNovedad::CREDITO,
        ]);
    }

    public function isAbsence(): bool
    {
        return in_array($this->tipo_novedad, [
            TipoNovedad::INCAPACIDAD,
            TipoNovedad::LICENCIA_NO_REMUNERADA,
            TipoNovedad::PERMISO,
            TipoNovedad::AUSENCIA,
        ]);
    }
}
