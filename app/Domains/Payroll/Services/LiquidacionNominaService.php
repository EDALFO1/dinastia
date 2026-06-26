<?php

namespace App\Domains\Payroll\Services;

use App\Models\Recibo;
use App\Models\Afiliado;
use App\Domains\Payroll\Models\NovedadNomina;

class LiquidacionNominaService
{
    protected Afiliado $afiliado;
    protected Recibo $recibo;
    protected int $empresaId;

    public function __construct(Afiliado $afiliado, Recibo $recibo, int $empresaId)
    {
        $this->afiliado = $afiliado;
        $this->recibo = $recibo;
        $this->empresaId = $empresaId;
    }

    /**
     * Liquidar nómina completa del afiliado
     */
    public function liquidar(): array
    {
        // 1. Calcular ingresos
        $ingresos = $this->calcularIngresos();

        // 2. Calcular base para aportes y retenciones
        $baseCalculoAportes = AportesCalculator::obtenerBaseCalculo(
            $ingresos['total_ingresos'],
            $ingresos['auxilio_transporte']
        );

        // 3. Calcular aportes
        $aportes = (new AportesCalculator($baseCalculoAportes, $this->recibo->id, $this->empresaId))->calcular();

        // 4. Calcular retenciones
        $retenciones = (new RetencionesCalculator($ingresos['total_ingresos'], $this->recibo->id, $this->empresaId))->calcular();

        // 5. Calcular neto a pagar
        $netoPagar = $ingresos['total_ingresos']
            - $aportes['total_aporte_empleado']
            - $retenciones['total_retenciones'];

        return [
            'ingresos' => $ingresos,
            'aportes' => $aportes,
            'retenciones' => $retenciones,
            'neto_pagar' => $netoPagar,
            'total_aportes_empresariales' => $aportes['total_aporte_empleador'],
        ];
    }

    /**
     * Calcular ingresos del período
     */
    private function calcularIngresos(): array
    {
        $salarioOrd = $this->recibo->valor_eps ?? 0; // Usar campo existente como aproximación
        $auxTransporte = 162000; // Auxilio de transporte 2024
        $bonificaciones = 0;
        $otroSalario = 0;

        // Buscar novedades de ingresos en el período
        $novedades = NovedadNomina::where('afiliado_id', $this->afiliado->id)
            ->where('recibo_id', $this->recibo->id)
            ->where('estado', 'aprobada')
            ->get();

        foreach ($novedades as $novedad) {
            if ($novedad->isIncome()) {
                $otroSalario += $novedad->valor_total;
            }
        }

        $totalIngresos = $salarioOrd + $auxTransporte + $bonificaciones + $otroSalario;

        return [
            'salario_ordinario' => $salarioOrd,
            'auxilio_transporte' => $auxTransporte,
            'bonificaciones' => $bonificaciones,
            'otros_ingresos' => $otroSalario,
            'total_ingresos' => $totalIngresos,
        ];
    }

    /**
     * Obtener resumen ejecutivo de la liquidación
     */
    public function obtenerResumen(array $liquidacion): array
    {
        return [
            'afiliado' => $this->afiliado->nombre,
            'periodo' => $this->recibo->fecha->format('m/Y'),
            'total_ingresos' => $liquidacion['ingresos']['total_ingresos'],
            'total_descuentos' => $liquidacion['aportes']['total_aporte_empleado'] + $liquidacion['retenciones']['total_retenciones'],
            'neto_pagar' => $liquidacion['neto_pagar'],
            'aporte_empleador' => $liquidacion['total_aportes_empresariales'],
        ];
    }
}
