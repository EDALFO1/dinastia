<?php

namespace App\Domains\Accounting\Controllers;

use App\Domains\Accounting\Services\BankReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BankReconciliationController
{
    protected BankReconciliationService $reconciliationService;

    public function __construct(BankReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    /**
     * Validar saldo bancario actual
     * GET /api/v1/accounting/reconciliation/validate-balance
     */
    public function validateBalance(Request $request): JsonResponse
    {
        $codigoCuenta = $request->input('codigo', '100501');

        try {
            $resultado = $this->reconciliationService->validateBankBalance(
                session('empresa_id'),
                $codigoCuenta
            );

            Log::info('Saldo bancario validado', [
                'empresa_id' => session('empresa_id'),
                'cuenta' => $codigoCuenta,
                'conciliado' => $resultado['conciliado'] ?? false,
            ]);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error validando saldo',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Generar reporte de conciliación
     * GET /api/v1/accounting/reconciliation/report
     */
    public function report(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
            'codigo' => 'nullable|string',
        ]);

        try {
            $desde = Carbon::createFromFormat('Y-m-d', $validated['desde']);
            $hasta = Carbon::createFromFormat('Y-m-d', $validated['hasta']);

            $resultado = $this->reconciliationService->generateReconciliationReport(
                session('empresa_id'),
                $desde,
                $hasta,
                $validated['codigo'] ?? null
            );

            Log::info('Reporte de conciliación generado', [
                'empresa_id' => session('empresa_id'),
            ]);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generando reporte',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Detectar transacciones duplicadas
     * GET /api/v1/accounting/reconciliation/duplicates
     */
    public function detectDuplicates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
        ]);

        try {
            $desde = Carbon::createFromFormat('Y-m-d', $validated['desde']);
            $hasta = Carbon::createFromFormat('Y-m-d', $validated['hasta']);

            $duplicados = $this->reconciliationService->detectDuplicateTransactions(
                session('empresa_id'),
                $desde,
                $hasta
            );

            if (!empty($duplicados)) {
                Log::warning('Transacciones duplicadas detectadas', [
                    'empresa_id' => session('empresa_id'),
                    'cantidad' => count($duplicados),
                ]);
            }

            return response()->json([
                'duplicados_encontrados' => count($duplicados),
                'detalle' => $duplicados,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error detectando duplicados',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
