<?php

namespace App\Domains\Accounting\Controllers;

use App\Domains\Accounting\Services\AuditTrailService;
use App\Domains\Accounting\Services\BudgetService;
use App\Domains\Accounting\Services\HorizontalAnalysisService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdvancedReportController
{
    protected HorizontalAnalysisService $horizontalService;
    protected BudgetService $budgetService;
    protected AuditTrailService $auditService;

    public function __construct(
        HorizontalAnalysisService $horizontalService,
        BudgetService $budgetService,
        AuditTrailService $auditService
    ) {
        $this->horizontalService = $horizontalService;
        $this->budgetService = $budgetService;
        $this->auditService = $auditService;
    }

    /**
     * Análisis Horizontal
     * GET /api/v1/accounting/advanced/horizontal-analysis
     */
    public function horizontalAnalysis(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'periodo1_desde' => 'required|date_format:Y-m-d',
            'periodo1_hasta' => 'required|date_format:Y-m-d',
            'periodo2_desde' => 'required|date_format:Y-m-d',
            'periodo2_hasta' => 'required|date_format:Y-m-d',
        ]);

        try {
            $resultado = $this->horizontalService->analyzeHorizontal(
                session('empresa_id'),
                Carbon::createFromFormat('Y-m-d', $validated['periodo1_desde']),
                Carbon::createFromFormat('Y-m-d', $validated['periodo1_hasta']),
                Carbon::createFromFormat('Y-m-d', $validated['periodo2_desde']),
                Carbon::createFromFormat('Y-m-d', $validated['periodo2_hasta'])
            );

            Log::info('Análisis horizontal generado', [
                'empresa_id' => session('empresa_id'),
            ]);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generando análisis',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Análisis de Tendencias
     * GET /api/v1/accounting/advanced/trends
     */
    public function trends(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fecha' => 'required|date_format:Y-m-d',
            'numero_periodos' => 'nullable|integer|min:3|max:24',
        ]);

        try {
            $resultado = $this->horizontalService->analyzeTrends(
                session('empresa_id'),
                Carbon::createFromFormat('Y-m-d', $validated['fecha']),
                $validated['numero_periodos'] ?? 12
            );

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error analizando tendencias',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Presupuesto vs Real
     * POST /api/v1/accounting/advanced/budget-comparison
     */
    public function budgetComparison(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
            'presupuestos' => 'required|array',
            'presupuestos.*' => 'numeric|min:0',
        ]);

        try {
            $resultado = $this->budgetService->compareBudgetVsActual(
                session('empresa_id'),
                Carbon::createFromFormat('Y-m-d', $validated['desde']),
                Carbon::createFromFormat('Y-m-d', $validated['hasta']),
                $validated['presupuestos']
            );

            Log::info('Análisis presupuesto vs real generado', [
                'empresa_id' => session('empresa_id'),
            ]);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generando análisis presupuestario',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Rastreo de Auditoría - Asiento Específico
     * GET /api/v1/accounting/advanced/audit-trail/{entryId}
     */
    public function entryAuditTrail(int $entryId): JsonResponse
    {
        try {
            $resultado = $this->auditService->getEntryAuditTrail($entryId);

            if (isset($resultado['error'])) {
                return response()->json($resultado, 404);
            }

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error obteniendo rastreo',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Auditoría por Usuario
     * GET /api/v1/accounting/advanced/audit-by-user
     */
    public function auditByUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'usuario_id' => 'required|integer',
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
        ]);

        try {
            $resultado = $this->auditService->getAuditByUser(
                session('empresa_id'),
                $validated['usuario_id'],
                Carbon::createFromFormat('Y-m-d', $validated['desde']),
                Carbon::createFromFormat('Y-m-d', $validated['hasta'])
            );

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error obteniendo auditoría',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Auditoría por Período
     * GET /api/v1/accounting/advanced/audit-by-period
     */
    public function auditByPeriod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
        ]);

        try {
            $resultado = $this->auditService->getAuditByPeriod(
                session('empresa_id'),
                Carbon::createFromFormat('Y-m-d', $validated['desde']),
                Carbon::createFromFormat('Y-m-d', $validated['hasta'])
            );

            Log::info('Reporte de auditoría por período generado', [
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
     * Detectar Actividades Sospechosas
     * GET /api/v1/accounting/advanced/suspicious-activity
     */
    public function suspiciousActivity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
        ]);

        try {
            $resultado = $this->auditService->detectSuspiciousActivity(
                session('empresa_id'),
                Carbon::createFromFormat('Y-m-d', $validated['desde']),
                Carbon::createFromFormat('Y-m-d', $validated['hasta'])
            );

            if (!empty($resultado['actividades_sospechosas'])) {
                Log::warning('Actividades sospechosas detectadas', [
                    'empresa_id' => session('empresa_id'),
                    'alertas' => $resultado['total_alertas'],
                ]);
            }

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error detectando actividades',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
