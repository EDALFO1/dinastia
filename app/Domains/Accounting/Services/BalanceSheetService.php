<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Accounting\Models\ChartOfAccounts;
use Carbon\Carbon;

class BalanceSheetService
{
    /**
     * Generar Balance General (Estado de Situación Financiera)
     */
    public function generateBalanceSheet(int $empresaId, Carbon $fecha): array
    {
        // Obtener saldos de todas las cuentas al cierre del período
        $activos = $this->getAccountsByType($empresaId, 'activo', $fecha);
        $pasivos = $this->getAccountsByType($empresaId, 'pasivo', $fecha);
        $patrimonio = $this->getAccountsByType($empresaId, 'patrimonio', $fecha);

        $totalActivos = $this->calculateTotal($activos);
        $totalPasivos = $this->calculateTotal($pasivos);
        $totalPatrimonio = $this->calculateTotal($patrimonio);

        // Validar ecuación contable: Activos = Pasivos + Patrimonio
        $diferencia = $totalActivos - ($totalPasivos + $totalPatrimonio);
        $balanceado = abs($diferencia) < 0.01;

        return [
            'fecha' => $fecha->format('Y-m-d'),
            'activos' => [
                'cuentas' => $activos,
                'total' => (float) $totalActivos,
            ],
            'pasivos' => [
                'cuentas' => $pasivos,
                'total' => (float) $totalPasivos,
            ],
            'patrimonio' => [
                'cuentas' => $patrimonio,
                'total' => (float) $totalPatrimonio,
            ],
            'ecuacion_contable' => [
                'activos' => (float) $totalActivos,
                'pasivos_patrimonio' => (float) ($totalPasivos + $totalPatrimonio),
                'diferencia' => (float) $diferencia,
                'balanceado' => $balanceado,
            ],
        ];
    }

    /**
     * Obtener cuentas por tipo con sus saldos
     */
    private function getAccountsByType(int $empresaId, string $tipo, Carbon $fecha): array
    {
        $cuentas = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('tipo_cuenta', $tipo)
            ->where('permite_movimiento', true)
            ->orderBy('codigo')
            ->get();

        $resultado = [];
        $totalTipo = 0;

        foreach ($cuentas as $cuenta) {
            // Obtener saldo hasta la fecha
            $saldo = $cuenta->getBalanceByPeriod(
                $cuenta->fecha_vigencia_inicio ?? now()->startOfYear(),
                $fecha
            );

            if (abs($saldo) < 0.01) {
                continue; // No incluir cuentas con saldo cero
            }

            $resultado[] = [
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'saldo' => (float) $saldo,
            ];

            $totalTipo += $saldo;
        }

        return $resultado;
    }

    /**
     * Calcular total de saldos
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
     * Generar análisis vertical (porcentaje sobre total activos)
     */
    public function generateVerticalAnalysis(int $empresaId, Carbon $fecha): array
    {
        $balanceSheet = $this->generateBalanceSheet($empresaId, $fecha);

        $totalActivos = $balanceSheet['activos']['total'];

        if ($totalActivos == 0) {
            return ['error' => 'Total activos es cero'];
        }

        return [
            'fecha' => $fecha->format('Y-m-d'),
            'analisis_vertical' => [
                'activos' => $this->calculatePercentages(
                    $balanceSheet['activos']['cuentas'],
                    $totalActivos
                ),
                'pasivos' => $this->calculatePercentages(
                    $balanceSheet['pasivos']['cuentas'],
                    $totalActivos
                ),
                'patrimonio' => $this->calculatePercentages(
                    $balanceSheet['patrimonio']['cuentas'],
                    $totalActivos
                ),
            ],
        ];
    }

    /**
     * Calcular porcentajes
     */
    private function calculatePercentages(array $cuentas, float $total): array
    {
        return array_map(function ($cuenta) use ($total) {
            return array_merge($cuenta, [
                'porcentaje' => round(($cuenta['saldo'] / $total) * 100, 2),
            ]);
        }, $cuentas);
    }
}
