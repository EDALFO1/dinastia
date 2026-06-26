<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Accounting\Models\ChartOfAccounts;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    /**
     * Crear presupuesto para período
     */
    public function createBudget(
        int $empresaId,
        string $nombre,
        Carbon $desde,
        Carbon $hasta,
        array $presupuestosPorCuenta
    ): array {
        $budgets = [];

        foreach ($presupuestosPorCuenta as $codigoCuenta => $montoPresupuestado) {
            $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
                ->where('codigo', $codigoCuenta)
                ->first();

            if (!$cuenta) {
                continue;
            }

            $budgets[] = [
                'cuenta_id' => $cuenta->id,
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'presupuestado' => (float) $montoPresupuestado,
                'real' => 0,
                'variacion' => 0,
                'porcentaje_ejecucion' => 0,
            ];
        }

        return [
            'presupuesto' => [
                'nombre' => $nombre,
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'detalles' => $budgets,
            'total_presupuestado' => array_sum(array_column($budgets, 'presupuestado')),
        ];
    }

    /**
     * Comparar presupuesto vs real
     */
    public function compareBudgetVsActual(
        int $empresaId,
        Carbon $desde,
        Carbon $hasta,
        array $presupuestos
    ): array {
        $comparacion = [];
        $totalPresupuestado = 0;
        $totalReal = 0;
        $totalVariacion = 0;

        foreach ($presupuestos as $codigoCuenta => $montoPresupuestado) {
            $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
                ->where('codigo', $codigoCuenta)
                ->first();

            if (!$cuenta) {
                continue;
            }

            $montoReal = $cuenta->getBalanceByPeriod($desde, $hasta);
            $variacion = $montoReal - $montoPresupuestado;
            $porcentajeEjecucion = $montoPresupuestado != 0
                ? round(($montoReal / $montoPresupuestado) * 100, 2)
                : 0;

            $totalPresupuestado += $montoPresupuestado;
            $totalReal += $montoReal;
            $totalVariacion += $variacion;

            $estado = $this->evaluateVariation($variacion, $montoPresupuestado);

            $comparacion[] = [
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'tipo' => $cuenta->tipo_cuenta->value,
                'presupuestado' => (float) $montoPresupuestado,
                'real' => (float) $montoReal,
                'variacion' => [
                    'valor' => (float) $variacion,
                    'porcentaje' => $porcentajeEjecucion - 100,
                    'estado' => $estado,
                ],
                'ejecucion' => $porcentajeEjecucion,
            ];
        }

        // Ordenar por variación (mayor desviación primero)
        usort($comparacion, fn ($a, $b) =>
            abs($b['variacion']['valor']) <=> abs($a['variacion']['valor'])
        );

        $variacionPorcentaje = $totalPresupuestado != 0
            ? round((($totalReal - $totalPresupuestado) / $totalPresupuestado) * 100, 2)
            : 0;

        return [
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'detalles' => $comparacion,
            'resumen' => [
                'total_presupuestado' => (float) $totalPresupuestado,
                'total_real' => (float) $totalReal,
                'total_variacion' => [
                    'valor' => (float) $totalVariacion,
                    'porcentaje' => $variacionPorcentaje,
                ],
                'porcentaje_ejecucion' => $totalPresupuestado != 0
                    ? round(($totalReal / $totalPresupuestado) * 100, 2)
                    : 0,
                'cuentas_en_presupuesto' => count(array_filter($comparacion, fn ($c) =>
                    $c['variacion']['estado'] === 'dentro_presupuesto'
                )),
                'cuentas_sobre_presupuesto' => count(array_filter($comparacion, fn ($c) =>
                    $c['variacion']['estado'] === 'sobre_presupuesto'
                )),
                'cuentas_bajo_presupuesto' => count(array_filter($comparacion, fn ($c) =>
                    $c['variacion']['estado'] === 'bajo_presupuesto'
                )),
            ],
        ];
    }

    /**
     * Evaluar variación vs presupuesto
     */
    private function evaluateVariation(float $variacion, float $presupuesto): string
    {
        if ($presupuesto == 0) {
            return 'no_presupuestado';
        }

        $porcentaje = ($variacion / $presupuesto) * 100;

        if (abs($porcentaje) <= 5) {
            return 'dentro_presupuesto';
        } elseif ($porcentaje > 5) {
            return 'sobre_presupuesto';
        } else {
            return 'bajo_presupuesto';
        }
    }
}
