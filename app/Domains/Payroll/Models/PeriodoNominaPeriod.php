<?php

namespace App\Domains\Payroll\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PeriodoNominaPeriod extends BaseModel
{
    use HasFactory;

    protected $table = 'periodos_nomina';

    protected $fillable = [
        'empresa_id',
        'numero_periodo',
        'anio',
        'mes',
        'fecha_inicio',
        'fecha_final',
        'fecha_pago',
        'dias_habiles',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_final' => 'date',
        'fecha_pago' => 'date',
        'dias_habiles' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }

    public function recibos()
    {
        return $this->hasMany(\App\Models\Recibo::class);
    }

    /**
     * Crear períodos estándar para un año
     */
    public static function crearPeriodosAnnual($empresaId, $anio)
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $numero = 1;
        foreach ($meses as $mes => $nombre) {
            $fechaInicio = date('Y-m-d', mktime(0, 0, 0, $mes, 1, $anio));
            $fechaFinal = date('Y-m-t', mktime(0, 0, 0, $mes, 1, $anio));
            $fechaPago = date('Y-m-d', mktime(0, 0, 0, $mes, 25, $anio));

            self::create([
                'empresa_id' => $empresaId,
                'numero_periodo' => $numero,
                'anio' => $anio,
                'mes' => $mes,
                'fecha_inicio' => $fechaInicio,
                'fecha_final' => $fechaFinal,
                'fecha_pago' => $fechaPago,
                'dias_habiles' => self::calcularDiasHabiles($fechaInicio, $fechaFinal),
                'estado' => 'abierto',
            ]);

            $numero++;
        }
    }

    /**
     * Calcular días hábiles (excluyendo domingos)
     */
    private static function calcularDiasHabiles($inicio, $final)
    {
        $diasHabiles = 0;
        $fecha = strtotime($inicio);
        $fechaFinal = strtotime($final);

        while ($fecha <= $fechaFinal) {
            $diaSemana = date('w', $fecha); // 0=domingo, 1=lunes...
            if ($diaSemana != 0) { // No es domingo
                $diasHabiles++;
            }
            $fecha = strtotime('+1 day', $fecha);
        }

        return $diasHabiles;
    }

    public function canEdit(): bool
    {
        return $this->estado === 'abierto';
    }

    public function close(): void
    {
        $this->update(['estado' => 'cerrado']);
    }
}
