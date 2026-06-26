<?php

namespace App\Domains\Shared\Controllers;

use App\Domains\Shared\Models\AuditLog;
use App\Domains\Shared\Services\AuditLogService;
use App\Domains\Shared\Services\ImmutabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditController
{
    protected AuditLogService $auditService;
    protected ImmutabilityService $immutabilityService;

    public function __construct(
        AuditLogService $auditService,
        ImmutabilityService $immutabilityService
    ) {
        $this->auditService = $auditService;
        $this->immutabilityService = $immutabilityService;
    }

    /**
     * Obtener logs de auditoría
     * GET /api/v1/audit/logs
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'desde' => 'nullable|date_format:Y-m-d',
            'hasta' => 'nullable|date_format:Y-m-d',
            'action' => 'nullable|in:created,updated,deleted,restored',
            'usuario_id' => 'nullable|integer',
            'modelo' => 'nullable|string',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $query = AuditLog::where('empresa_id', session('empresa_id'))
            ->with('user');

        if ($validated['desde'] ?? null) {
            $query->whereDate('created_at', '>=', $validated['desde']);
        }

        if ($validated['hasta'] ?? null) {
            $query->whereDate('created_at', '<=', $validated['hasta']);
        }

        if ($validated['action'] ?? null) {
            $query->where('action', $validated['action']);
        }

        if ($validated['usuario_id'] ?? null) {
            $query->where('user_id', $validated['usuario_id']);
        }

        if ($validated['modelo'] ?? null) {
            $query->where('auditable_type', 'like', '%' . $validated['modelo'] . '%');
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($validated['per_page'] ?? 25);

        return response()->json([
            'data' => $logs->items(),
            'pagination' => [
                'total' => $logs->total(),
                'count' => $logs->count(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
            ],
        ]);
    }

    /**
     * Obtener auditoría de un modelo específico
     * GET /api/v1/audit/model/{modelType}/{modelId}
     */
    public function modelAudit(string $modelType, int $modelId): JsonResponse
    {
        $logs = AuditLog::where('empresa_id', session('empresa_id'))
            ->where('auditable_type', 'like', '%' . $modelType . '%')
            ->where('auditable_id', $modelId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'fecha' => $log->created_at->format('Y-m-d H:i:s'),
                    'usuario' => $log->user?->nombre ?? 'Sistema',
                    'cambios' => $log->getChangesSummary(),
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'ip' => $log->ip_address,
                ];
            });

        return response()->json([
            'modelo' => $modelType,
            'id' => $modelId,
            'total' => $logs->count(),
            'auditoría' => $logs,
        ]);
    }

    /**
     * Resumen de actividad por período
     * GET /api/v1/audit/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
        ]);

        $desde = Carbon::createFromFormat('Y-m-d', $validated['desde']);
        $hasta = Carbon::createFromFormat('Y-m-d', $validated['hasta']);

        $logs = AuditLog::where('empresa_id', session('empresa_id'))
            ->whereBetween('created_at', [$desde, $hasta])
            ->get();

        $resumen = [
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'estadisticas' => [
                'total' => $logs->count(),
                'creados' => $logs->where('action', 'created')->count(),
                'actualizados' => $logs->where('action', 'updated')->count(),
                'eliminados' => $logs->where('action', 'deleted')->count(),
                'restaurados' => $logs->where('action', 'restored')->count(),
                'intentos_no_autorizados' => $logs->where('action', 'unauthorized_attempted_change')->count(),
            ],
            'por_usuario' => $logs->groupBy('user_id')
                ->map(fn ($group) => [
                    'usuario' => $group->first()->user?->nombre ?? 'Sistema',
                    'total_acciones' => $group->count(),
                ])
                ->values(),
            'por_modelo' => $logs->groupBy('auditable_type')
                ->map(fn ($group) => [
                    'modelo' => class_basename($group[0]->auditable_type),
                    'total' => $group->count(),
                ])
                ->values(),
        ];

        return response()->json($resumen);
    }

    /**
     * Generar certificado de integridad
     * POST /api/v1/audit/integrity-certificate
     */
    public function integrityCertificate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d',
            'modelo' => 'nullable|string',
        ]);

        $desde = Carbon::createFromFormat('Y-m-d', $validated['desde']);
        $hasta = Carbon::createFromFormat('Y-m-d', $validated['hasta']);

        $certificado = $this->immutabilityService->generateIntegrityCertificate(
            session('empresa_id'),
            $desde,
            $hasta,
            $validated['modelo'] ?? 'App\Domains\Accounting\Models\JournalEntry'
        );

        return response()->json($certificado);
    }

    /**
     * Obtener cambios sospechosos
     * GET /api/v1/audit/suspicious
     */
    public function suspicious(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dias' => 'nullable|integer|min:1|max:90',
        ]);

        $dias = $validated['dias'] ?? 7;

        $logs = AuditLog::where('empresa_id', session('empresa_id'))
            ->where(function ($query) {
                $query->where('action', 'unauthorized_attempted_change')
                    ->orWhere('old_values', '!=', null)
                    ->orWhereRaw('DATEDIFF(updated_at, created_at) > 0');
            })
            ->recent($dias)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'periodo_dias' => $dias,
            'total_sospechosas' => $logs->count(),
            'actividades' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'modelo' => class_basename($log->auditable_type),
                    'action' => $log->action,
                    'fecha' => $log->created_at->format('Y-m-d H:i:s'),
                    'usuario' => $log->user?->nombre,
                    'ip' => $log->ip_address,
                    'descripcion' => $log->changes_description,
                ];
            }),
        ]);
    }
}
