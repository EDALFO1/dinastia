<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Accounting\Models\ChartOfAccounts;
use Carbon\Carbon;

class IncomeStatementService
{
    /**
     * Generar Estado de Resultados (Ingresos - Gastos)
     */
    public function generateIncomeStatement(
        int $empresaId,
        Carbon $desde,
        Carbon $hasta
    ): array {
        $ingresos = $this->getAccountsByType($empresaId, 'ingresos', $desde, $hasta);
        $gastos = $this->getAccountsByType($empresaId, 'gastos', $desde, $hasta);
        $costos = $this->getAccountsByType($empresaId, 'costo', $desde, $hasta);

        $totalIngresos = $this->calculateTotal($ingresos);
        $totalGastos = $this->calculateTotal($gastos);
        $totalCostos = $this->calculateTotal($costos);

        // Cálculos
        $utilidadBruta = $totalIngresos - $totalCostos;
        $utilidadOperacional = $utilidadBruta - $totalGastos;

        return [
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'ingresos' => [
                'cuentas' => $ingresos,
                'total' => (float) $totalIngresos,
            ],
            'costo_venta' => [
                'cuentas' => $costos,
                'total' => (float) $totalCostos,
            ],
            'utilidad_bruta' => (float) $utilidadBruta,
            'gastos' => [
                'cuentas' => $gastos,
                'total' => (float) $totalGastos,
            ],
            'utilidad_operacional' => (float) $utilidadOperacional,
            'indicadores' => [
                'margen_bruto' => $totalIngresos != 0
                    ? round(($utilidadBruta / $totalIngresos) * 100, 2)
                    : 0,
                'margen_operacional' => $totalIngresos != 0
                    ? round(($utilidadOperacional / $totalIngresos) * 100, 2)
                    : 0,
                'ratio_gastos' => $totalIngresos != 0
                    ? round(($totalGastos / $totalIngresos) * 100, 2)
                    : 0,
            ],
        ];
    }

    /**
     * Obtener cuentas por tipo con sus saldos en el período
     */
    private function getAccountsByType(
        int $empresaId,
        string $tipo,
        Carbon $desde,
        Carbon $hasta
    ): array {
        $cuentas = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('tipo_cuenta', $tipo)
            ->where('permite_movimiento', true)
            ->orderBy('codigo')
            ->get();

        $resultado = [];

        foreach ($cuentas as $cuenta) {
            $saldo = $cuenta->getBalanceByPeriod($desde, $hasta);

            if (abs($saldo) < 0.01) {
                continue;
            }

            $resultado[] = [
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'saldo' => (float) $saldo,
            ];
        }

        return $resultado;
    }

    /**
     * Calcular total
     */
    private function calculateTotal(array $cuentas): float
    {
        $total = 0;
        foreach ($cuentas as $cuenta) {
            $total += $cuenta['saldo'];
        }
        return $total;
    }

    /**
     * Comparativa de períodos (año a año, mes a mes)
     */
    public function generateComparison(
        int $empresaId,
        Carbon $periodoActual,
        Carbon $periodoPrevio
    ): array {
        // Para simplificar, usar mismos días que el período actual
        $desde = $periodoActual->copy()->startOfMonth();
        $hasta = $periodoActual->copy()->endOfMonth();

        $currentMonth = $this->generateIncomeStatement(
            $empresaId,
            $desde,
            $hasta
        );

        $previousFrom = $periodoPrevio->copy()->startOfMonth();
        $previousTo = $periodoPrevio->copy()->endOfMonth();

        $previousMonth = $this->generateIncomeStatement(
            $empresaId,
            $previousFrom,
            $previousTo
        );

        return [
            'actual' => $currentMonth,
            'previo' => $previousMonth,
            'variacion' => [
                'ingresos' => [
                    'valor' => $currentMonth['ingresos']['total'] - $previousMonth['ingresos']['total'],
                    'porcentaje' => $previousMonth['ingresos']['total'] != 0
                        ? round(
                            (($currentMonth['ingresos']['total'] - $previousMonth['ingresos']['total'])
                                / $previousMonth['ingresos']['total']) * 100,
                            2
                        )
                        : 0,
                ],
                'gastos' => [
                    'valor' => $currentMonth['gastos']['total'] - $previousMonth['gastos']['total'],
                    'porcentaje' => $previousMonth['gastos']['total'] != 0
                        ? round(
                            (($currentMonth['gastos']['total'] - $previousMonth['gastos']['total'])
                                / $previousMonth['gastos']['total']) * 100,
                            2
                        )
                        : 0,
                ],
                'utilidad' => [
                    'valor' => $currentMonth['utilidad_operacional'] - $previousMonth['utilidad_operacional'],
                    'porcentaje' => $previousMonth['utilidad_operacional'] != 0
                        ? round(
                            (($currentMonth['utilidad_operacional'] - $previousMonth['utilidad_operacional'])
                                / $previousMonth['utilidad_operacional']) * 100,
                            2
                        )
                        : 0,
                ],
            ],
        ];
    }
}
