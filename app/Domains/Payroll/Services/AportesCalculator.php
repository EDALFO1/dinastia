<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Enums\TipoAporte;
use App\Domains\Payroll\Models\CuotaAportes;

class AportesCalculator
{
    protected float $baseCalculo;
    protected int $reciboId;
    protected int $empresaId;

    public function __construct(float $baseCalculo, int $reciboId, int $empresaId)
    {
        $this->baseCalculo = $baseCalculo;
        $this->reciboId = $reciboId;
        $this->empresaId = $empresaId;
    }

    /**
     * Calcular todos los aportes para el período
     */
    public function calcular(): array
    {
        $aportes = [];
        $aporteTypes = [
            TipoAporte::AFP,
            TipoAporte::EPS,
            TipoAporte::ARL,
            TipoAporte::CAJA_COMPENSACION,
        ];

        $totalAporteEmpleado = 0;
        $totalAporteEmpleador = 0;

        foreach ($aporteTypes as $tipo) {
            $porcentajeEmpleado = $tipo->porcentajeEmpleado();
            $porcentajeEmpleador = $tipo->porcentajeEmpleador();

            $aporteEmpleado = $this->baseCalculo * ($porcentajeEmpleado / 100);
            $aporteEmpleador = $this->baseCalculo * ($porcentajeEmpleador / 100);

            $totalAporte = $aporteEmpleado + $aporteEmpleador;

            $cuota = CuotaAportes::create([
                'recibo_id' => $this->reciboId,
                'tipo_aporte' => $tipo,
                'base_calculo' => $this->baseCalculo,
                'porcentaje_empleado' => $porcentajeEmpleado,
                'aporte_empleado' => $aporteEmpleado,
                'porcentaje_empleador' => $porcentajeEmpleador,
                'aporte_empleador' => $aporteEmpleador,
                'total_aporte' => $totalAporte,
                'empresa_id' => $this->empresaId,
            ]);

            $aportes[] = $cuota;
            $totalAporteEmpleado += $aporteEmpleado;
            $totalAporteEmpleador += $aporteEmpleador;
        }

        return [
            'cuotas' => $aportes,
            'total_aporte_empleado' => $totalAporteEmpleado,
            'total_aporte_empleador' => $totalAporteEmpleador,
            'total_aportes' => $totalAporteEmpleado + $totalAporteEmpleador,
        ];
    }

    /**
     * Obtener base de cálculo para aportes (sueldo - auxilio de transporte)
     */
    public static function obtenerBaseCalculo(float $salario, float $auxTransporte = 0): float
    {
        return $salario - $auxTransporte;
    }
}
