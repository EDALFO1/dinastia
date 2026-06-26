<?php

namespace App\Domains\Accounting\Controllers;

use App\Domains\Accounting\Services\BalanceSheetService;
use App\Domains\Accounting\Services\FinancialRatiosService;
use App\Domains\Accounting\Services\IncomeStatementService;
use App\Domains\Accounting\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinancialReportController
{
    protected LedgerService $ledgerService;
    protected BalanceSheetService $balanceSheetService;
    protected IncomeStatementService $incomeStatementService;
    protected FinancialRatiosService $ratiosService;

    public function __construct(
        LedgerService $ledgerService,
        BalanceSheetService $balanceSheetService,
        IncomeStatementService $incomeStatementService,
        FinancialRatiosService $ratiosService
    ) {
        $this->ledgerService = $ledgerService;
        $this->balanceSheetService = $balanceSheetService;
        $this->incomeStatementService = $incomeStatementService;
        $this->ratiosService = $ratiosService;
    }

    /**
     * Obtener Libro Mayor (por cuenta)
     * GET /api/v1/accounting/reports/ledger?codigo=100501&desde=2026-01-01&hasta=2026-12-31
     */
    public function ledger(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string',
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
        ]);

        try {
            $desde = Carbon::createFromFormat('Y-m-d', $validated['desde']);
            $hasta = Carbon::createFromFormat('Y-m-d', $validated['hasta']);

            $resultado = $this->ledgerService->generateLedger(
                session('empresa_id'),
                $validated['codigo'],
                $desde,
                $hasta
            );

            Log::info('Libro Mayor generado', [
                'empresa_id' => session('empresa_id'),
                'cuenta' => $validated['codigo'],
                'periodo' => "{$desde->toDateString()} - {$hasta->toDateString()}",
            ]);

            return response()->json($resultado);
        } catch (\Exception $e) {
            Log::error('Error generando Libro Mayor', [
                'error' => $e->getMessage(),
                'cuenta' => $validated['codigo'],
            ]);

            return response()->json([
                'message' => 'Error al generar Libro Mayor',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener Libro Mayor Consolidado
     * GET /api/v1/accounting/reports/ledger-consolidated?desde=2026-01-01&hasta=2026-12-31
     */
    public function ledgerConsolidated(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
        ]);

        try {
            $desde = Carbon::createFromFormat('Y-m-d', $validated['desde']);
            $hasta = Carbon::createFromFormat('Y-m-d', $validated['hasta']);

            $resultado = $this->ledgerService->generateConsolidatedLedger(
                session('empresa_id'),
                $desde,
                $hasta
            );

            Log::info('Libro Mayor Consolidado generado', [
                'empresa_id' => session('empresa_id'),
            ]);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar reporte',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener Balance General (Estado de Situación Financiera)
     * GET /api/v1/accounting/reports/balance-sheet?fecha=2026-12-31
     */
    public function balanceSheet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fecha' => 'required|date_format:Y-m-d',
        ]);

        try {
            $fecha = Carbon::createFromFormat('Y-m-d', $validated['fecha']);

            $resultado = $this->balanceSheetService->generateBalanceSheet(
                session('empresa_id'),
                $fecha
            );

            Log::info('Balance General generado', [
                'empresa_id' => session('empresa_id'),
                'fecha' => $fecha->toDateString(),
            ]);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar Balance General',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener análisis vertical del Balance General
     * GET /api/v1/accounting/reports/balance-sheet-vertical?fecha=2026-12-31
     */
    public function balanceSheetVertical(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fecha' => 'required|date_format:Y-m-d',
        ]);

        try {
            $fecha = Carbon::createFromFormat('Y-m-d', $validated['fecha']);

            $resultado = $this->balanceSheetService->generateVerticalAnalysis(
                session('empresa_id'),
                $fecha
            );

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar análisis',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener Estado de Resultados (P&L)
     * GET /api/v1/accounting/reports/income-statement?desde=2026-01-01&hasta=2026-12-31
     */
    public function incomeStatement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
        ]);

        try {
            $desde = Carbon::createFromFormat('Y-m-d', $validated['desde']);
            $hasta = Carbon::createFromFormat('Y-m-d', $validated['hasta']);

            $resultado = $this->incomeStatementService->generateIncomeStatement(
                session('empresa_id'),
                $desde,
                $hasta
            );

            Log::info('Estado de Resultados generado', [
                'empresa_id' => session('empresa_id'),
            ]);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar Estado de Resultados',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener comparativa de períodos
     * GET /api/v1/accounting/reports/income-comparison?actual=2026-12-31&previo=2025-12-31
     */
    public function incomeComparison(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'actual' => 'required|date_format:Y-m-d',
            'previo' => 'required|date_format:Y-m-d',
        ]);

        try {
            $actual = Carbon::createFromFormat('Y-m-d', $validated['actual']);
            $previo = Carbon::createFromFormat('Y-m-d', $validated['previo']);

            $resultado = $this->incomeStatementService->generateComparison(
                session('empresa_id'),
                $actual,
                $previo
            );

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar comparativa',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Obtener Ratios Financieros
     * GET /api/v1/accounting/reports/financial-ratios?fecha=2026-12-31&desde=2026-01-01&hasta=2026-12-31
     */
    public function financialRatios(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fecha' => 'required|date_format:Y-m-d',
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
        ]);

        try {
            $fecha = Carbon::createFromFormat('Y-m-d', $validated['fecha']);
            $desde = Carbon::createFromFormat('Y-m-d', $validated['desde']);
            $hasta = Carbon::createFromFormat('Y-m-d', $validated['hasta']);

            $resultado = $this->ratiosService->calculateRatios(
                session('empresa_id'),
                $fecha,
                $desde,
                $hasta
            );

            Log::info('Ratios Financieros calculados', [
                'empresa_id' => session('empresa_id'),
            ]);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al calcular ratios',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
