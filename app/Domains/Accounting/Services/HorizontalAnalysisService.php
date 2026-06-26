<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Accounting\Models\ChartOfAccounts;
use Carbon\Carbon;

class HorizontalAnalysisService
{
    /**
     * Análisis horizontal: comparar variación año a año o período a período
     */
    public function analyzeHorizontal(
        int $empresaId,
        Carbon $periodo1Start,
        Carbon $periodo1End,
        Carbon $periodo2Start,
        Carbon $periodo2End
    ): array {
        $cuentas = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('permite_movimiento', true)
            ->orderBy('codigo')
            ->get();

        $analisis = [];

        foreach ($cuentas as $cuenta) {
            $saldo1 = $cuenta->getBalanceByPeriod($periodo1Start, $periodo1End);
            $saldo2 = $cuenta->getBalanceByPeriod($periodo2Start, $periodo2End);

            if ($saldo1 == 0 && $saldo2 == 0) {
                continue; // Omitir cuentas sin movimiento
            }

            $variacion = $saldo2 - $saldo1;
            $variacionPorcentaje = $saldo1 != 0
                ? round(($variacion / $saldo1) * 100, 2)
                : ($saldo2 != 0 ? 100 : 0);

            $tendencia = $this->determineTendency($variacionPorcentaje);

            $analisis[] = [
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'tipo' => $cuenta->tipo_cuenta->value,
                'periodo_1' => [
                    'fecha_inicio' => $periodo1Start->format('Y-m-d'),
                    'fecha_fin' => $periodo1End->format('Y-m-d'),
                    'saldo' => (float) $saldo1,
                ],
                'periodo_2' => [
                    'fecha_inicio' => $periodo2Start->format('Y-m-d'),
                    'fecha_fin' => $periodo2End->format('Y-m-d'),
                    'saldo' => (float) $saldo2,
                ],
                'variacion' => [
                    'valor' => (float) $variacion,
                    'porcentaje' => $variacionPorcentaje,
                    'tendencia' => $tendencia,
                ],
            ];
        }

        // Ordenar por variación absoluta (mayor cambio primero)
        usort($analisis, function ($a, $b) {
            return abs($b['variacion']['valor']) <=> abs($a['variacion']['valor']);
        });

        return [
            'tipo_analisis' => 'horizontal',
            'periodos' => [
                'periodo_1' => [
                    'desde' => $periodo1Start->format('Y-m-d'),
                    'hasta' => $periodo1End->format('Y-m-d'),
                ],
                'periodo_2' => [
                    'desde' => $periodo2Start->format('Y-m-d'),
                    'hasta' => $periodo2End->format('Y-m-d'),
                ],
            ],
            'cuentas' => $analisis,
            'resumen' => [
                'total_cuentas' => count($analisis),
                'cuentas_con_incremento' => count(array_filter($analisis, fn ($a) => $a['variacion']['valor'] > 0)),
                'cuentas_con_decremento' => count(array_filter($analisis, fn ($a) => $a['variacion']['valor'] < 0)),
                'variacion_promedio' => round(
                    array_sum(array_column($analisis, ['variacion', 'porcentaje'])) / max(count($analisis), 1),
                    2
                ),
            ],
        ];
    }

    /**
     * Análisis horizontal de tendencias (últimos N períodos)
     */
    public function analyzeTrends(
        int $empresaId,
        Carbon $fecha,
        int $numeroPerodos = 12
    ): array {
        $cuentas = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('permite_movimiento', true)
            ->orderBy('codigo')
            ->get();

        $tendencias = [];

        foreach ($cuentas as $cuenta) {
            $datos = [];
            $fechaActual = $fecha->copy();

            for ($i = 0; $i < $numeroPerodos; $i++) {
                $mesInicio = $fechaActual->copy()->startOfMonth();
                $mesFin = $fechaActual->copy()->endOfMonth();

                $saldo = $cuenta->getBalanceByPeriod($mesInicio, $mesFin);

                $datos[] = [
                    'mes' => $fechaActual->format('Y-m'),
                    'mes_label' => $fechaActual->format('M Y'),
                    'saldo' => (float) $saldo,
                ];

                $fechaActual->subMonth();
            }

            // Invertir para orden cronológico
            $datos = array_reverse($datos);

            // Calcular tendencia general
            $saldos = array_column($datos, 'saldo');
            if (count($saldos) > 1) {
                $tendencia = $this->calculateTrendDirection($saldos);
            } else {
                $tendencia = 'stable';
            }

            $tendencias[] = [
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'tipo' => $cuenta->tipo_cuenta->value,
                'datos' => $datos,
                'tendencia_general' => $tendencia,
                'saldo_inicial' => (float) $datos[0]['saldo'],
                'saldo_final' => (float) $datos[count($datos) - 1]['saldo'],
            ];
        }

        return [
            'tipo_analisis' => 'tendencias',
            'periodos_analizados' => $numeroPeridades,
            'fecha_hasta' => $fecha->format('Y-m-d'),
            'cuentas' => array_filter($tendencias, fn ($t) => !empty(array_filter($t['datos'], fn ($d) => $d['saldo'] != 0))),
        ];
    }

    /**
     * Determinar tendencia (↑ ↓ →)
     */
    private function determineTendency(float $porcentaje): string
    {
        if ($porcentaje > 10) {
            return 'aumentó_significativamente';
        } elseif ($porcentaje > 0) {
            return 'aumentó';
        } elseif ($porcentaje < -10) {
            return 'disminuyó_significativamente';
        } elseif ($porcentaje < 0) {
            return 'disminuyó';
        } else {
            return 'sin_cambios';
        }
    }

    /**
     * Calcular dirección de tendencia
     */
    private function calculateTrendDirection(array $saldos): string
    {
        if (count($saldos) < 2) {
            return 'insufficient_data';
        }

        // Comparar primero vs último
        $primero = $saldos[0];
        $ultimo = $saldos[count($saldos) - 1];

        if ($ultimo > $primero * 1.1) {
            return 'creciente';
        } elseif ($ultimo < $primero * 0.9) {
            return 'decreciente';
        } else {
            return 'estable';
        }
    }
}
