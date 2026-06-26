<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Accounting\Models\ChartOfAccounts;
use App\Domains\Accounting\Models\JournalLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LedgerService
{
    /**
     * Generar libro mayor (mayor individual por cuenta)
     */
    public function generateLedger(
        int $empresaId,
        string $codigoCuenta,
        Carbon $desde,
        Carbon $hasta
    ): array {
        $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('codigo', $codigoCuenta)
            ->firstOrFail();

        $saldoInicial = (float) $cuenta->saldo_inicial;

        $movimientos = $cuenta->journalLines()
            ->whereHas('journalEntry', function ($q) use ($desde, $hasta) {
                $q->where('estado', 'posteado')
                    ->whereBetween('fecha', [$desde, $hasta]);
            })
            ->with(['journalEntry'])
            ->orderBy('created_at')
            ->get();

        $saldoActual = $saldoInicial;
        $detalles = [];

        foreach ($movimientos as $movimiento) {
            $monto = (float) $movimiento->valor;

            if ($movimiento->tipo_movimiento === 'debito') {
                $saldoActual += $monto;
            } else {
                $saldoActual -= $monto;
            }

            $detalles[] = [
                'fecha' => $movimiento->journalEntry->fecha->format('Y-m-d'),
                'numero_asiento' => $movimiento->journalEntry->numero_asiento,
                'descripcion' => $movimiento->journalEntry->descripcion,
                'debito' => $movimiento->tipo_movimiento === 'debito' ? $monto : 0,
                'credito' => $movimiento->tipo_movimiento === 'credito' ? $monto : 0,
                'saldo' => $saldoActual,
            ];
        }

        $totalDebito = collect($detalles)->sum('debito');
        $totalCredito = collect($detalles)->sum('credito');

        return [
            'cuenta' => [
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'tipo' => $cuenta->tipo_cuenta->label(),
            ],
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'saldo_inicial' => $saldoInicial,
            'saldo_final' => $saldoActual,
            'detalles' => $detalles,
            'totales' => [
                'debito' => $totalDebito,
                'credito' => $totalCredito,
                'movimientos' => count($detalles),
            ],
        ];
    }

    /**
     * Generar libro mayor consolidado (todas las cuentas)
     */
    public function generateConsolidatedLedger(
        int $empresaId,
        Carbon $desde,
        Carbon $hasta
    ): array {
        $cuentas = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('permite_movimiento', true)
            ->orderBy('codigo')
            ->get();

        $resumen = [];
        $totalDebitoGeneral = 0;
        $totalCreditoGeneral = 0;

        foreach ($cuentas as $cuenta) {
            $movimientos = $cuenta->journalLines()
                ->whereHas('journalEntry', function ($q) use ($desde, $hasta) {
                    $q->where('estado', 'posteado')
                        ->whereBetween('fecha', [$desde, $hasta]);
                })
                ->get();

            if ($movimientos->isEmpty()) {
                continue;
            }

            $saldoFinal = $cuenta->getBalanceByPeriod($desde, $hasta);
            $totalDebito = $movimientos->where('tipo_movimiento', 'debito')->sum('valor');
            $totalCredito = $movimientos->where('tipo_movimiento', 'credito')->sum('valor');

            $totalDebitoGeneral += $totalDebito;
            $totalCreditoGeneral += $totalCredito;

            $resumen[] = [
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'tipo' => $cuenta->tipo_cuenta->value,
                'saldo_inicial' => (float) $cuenta->saldo_inicial,
                'debito' => (float) $totalDebito,
                'credito' => (float) $totalCredito,
                'saldo_final' => (float) $saldoFinal,
            ];
        }

        return [
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'cuentas' => $resumen,
            'totales' => [
                'debito' => (float) $totalDebitoGeneral,
                'credito' => (float) $totalCreditoGeneral,
            ],
        ];
    }
}
