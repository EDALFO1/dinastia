<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Accounting\Models\ChartOfAccounts;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BankReconciliationService
{
    /**
     * Validar saldo bancario vs contabilidad
     */
    public function validateBankBalance(int $empresaId, ?string $codigoCuenta = null): array
    {
        $codigoCuenta = $codigoCuenta ?? '100501'; // Banco principal

        $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('codigo', $codigoCuenta)
            ->first();

        if (!$cuenta) {
            return [
                'success' => false,
                'error' => 'Cuenta bancaria no encontrada',
            ];
        }

        $saldoContable = $cuenta->getCurrentBalance();

        // Aquí se podría comparar con saldo real del banco
        // Por ahora, validamos que no hay discrepancias internas
        $saldoEsperado = $this->calculateExpectedBalance($cuenta);
        $diferencia = abs($saldoContable - $saldoEsperado);

        $conciliado = $diferencia < 0.01;

        $resultado = [
            'cuenta' => [
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
            ],
            'saldo_contable' => (float) $saldoContable,
            'saldo_esperado' => (float) $saldoEsperado,
            'diferencia' => (float) $diferencia,
            'conciliado' => $conciliado,
            'fecha_validacion' => now()->toIso8601String(),
        ];

        if (!$conciliado) {
            Log::warning('Discrepancia en saldo bancario', $resultado);
        } else {
            Log::info('Saldo bancario conciliado', $resultado);
        }

        return $resultado;
    }

    /**
     * Calcular saldo esperado (basado en movimientos posteados)
     */
    private function calculateExpectedBalance(ChartOfAccounts $cuenta): float
    {
        $balance = (float) $cuenta->saldo_inicial;

        $movimientos = $cuenta->journalLines()
            ->whereHas('journalEntry', function ($q) {
                $q->where('estado', 'posteado');
            })
            ->get();

        foreach ($movimientos as $linea) {
            if ($linea->tipo_movimiento === 'debito') {
                $balance += (float) $linea->valor;
            } else {
                $balance -= (float) $linea->valor;
            }
        }

        return $balance;
    }

    /**
     * Generar reporte de conciliación
     */
    public function generateReconciliationReport(
        int $empresaId,
        Carbon $desde,
        Carbon $hasta,
        ?string $codigoCuenta = null
    ): array {
        $codigoCuenta = $codigoCuenta ?? '100501';

        $cuenta = ChartOfAccounts::where('empresa_id', $empresaId)
            ->where('codigo', $codigoCuenta)
            ->firstOrFail();

        $movimientos = $cuenta->journalLines()
            ->whereHas('journalEntry', function ($q) use ($desde, $hasta) {
                $q->where('estado', 'posteado')
                    ->whereBetween('fecha', [$desde, $hasta]);
            })
            ->with(['journalEntry'])
            ->orderBy('created_at')
            ->get();

        $saldoInicial = (float) $cuenta->saldo_inicial;
        $saldoActual = $saldoInicial;
        $detalles = [];
        $totalDebito = 0;
        $totalCredito = 0;

        foreach ($movimientos as $linea) {
            $monto = (float) $linea->valor;

            if ($linea->tipo_movimiento === 'debito') {
                $saldoActual += $monto;
                $totalDebito += $monto;
            } else {
                $saldoActual -= $monto;
                $totalCredito += $monto;
            }

            $detalles[] = [
                'fecha' => $linea->journalEntry->fecha->format('Y-m-d'),
                'numero_asiento' => $linea->journalEntry->numero_asiento,
                'descripcion' => $linea->journalEntry->descripcion,
                'debito' => $linea->tipo_movimiento === 'debito' ? $monto : 0,
                'credito' => $linea->tipo_movimiento === 'credito' ? $monto : 0,
                'saldo' => $saldoActual,
            ];
        }

        return [
            'cuenta' => [
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
            ],
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'saldo_inicial' => $saldoInicial,
            'movimientos' => $detalles,
            'totales' => [
                'debito' => (float) $totalDebito,
                'credito' => (float) $totalCredito,
                'saldo_final' => (float) $saldoActual,
            ],
            'estado_conciliacion' => [
                'saldo_esperado' => $saldoActual,
                'saldo_real' => $saldoActual, // En producción, obtener del banco
                'diferencia' => 0,
                'conciliado' => true,
            ],
        ];
    }

    /**
     * Detectar transacciones duplicadas
     */
    public function detectDuplicateTransactions(
        int $empresaId,
        Carbon $desde,
        Carbon $hasta
    ): array {
        $duplicados = [];

        $movimientos = \App\Domains\Accounting\Models\JournalLine::where('empresa_id', $empresaId)
            ->whereHas('journalEntry', function ($q) use ($desde, $hasta) {
                $q->where('estado', 'posteado')
                    ->whereBetween('fecha', [$desde, $hasta]);
            })
            ->with(['journalEntry'])
            ->get()
            ->groupBy(function ($item) {
                return "{$item->account_id}:{$item->valor}:{$item->tipo_movimiento}";
            });

        foreach ($movimientos as $group) {
            if ($group->count() > 1) {
                $duplicados[] = [
                    'cuenta_id' => $group->first()->account_id,
                    'valor' => $group->first()->valor,
                    'tipo' => $group->first()->tipo_movimiento,
                    'cantidad' => $group->count(),
                    'asientos' => $group->map(fn ($l) => $l->journalEntry->numero_asiento)->toArray(),
                ];
            }
        }

        return $duplicados;
    }
}
