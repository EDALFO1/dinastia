<?php

namespace App\Domains\Shared\Services;

use App\Domains\Shared\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /**
     * Registrar creación de modelo
     */
    public function logCreated(Model $model, int $empresaId, ?array $attributes = null): AuditLog
    {
        $attributes = $attributes ?? $model->getAttributes();

        return $this->createAuditLog(
            $empresaId,
            'created',
            $model,
            [],
            $attributes
        );
    }

    /**
     * Registrar actualización de modelo
     */
    public function logUpdated(Model $model, int $empresaId, ?array $oldValues = null): AuditLog
    {
        $oldValues = $oldValues ?? $model->getOriginal();
        $newValues = $model->getAttributes();

        // Solo registrar cambios reales
        $cambios = array_diff_assoc($newValues, $oldValues);
        if (empty($cambios)) {
            return null;
        }

        return $this->createAuditLog(
            $empresaId,
            'updated',
            $model,
            array_intersect_key($oldValues, $cambios),
            array_intersect_key($newValues, $cambios)
        );
    }

    /**
     * Registrar eliminación de modelo
     */
    public function logDeleted(Model $model, int $empresaId, ?array $attributes = null): AuditLog
    {
        $attributes = $attributes ?? $model->getAttributes();

        return $this->createAuditLog(
            $empresaId,
            'deleted',
            $model,
            $attributes,
            []
        );
    }

    /**
     * Registrar acción personalizada
     */
    public function logAction(
        string $action,
        Model $model,
        int $empresaId,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return $this->createAuditLog(
            $empresaId,
            $action,
            $model,
            $oldValues ?? [],
            $newValues ?? []
        );
    }

    /**
     * Crear registro de auditoría
     */
    private function createAuditLog(
        int $empresaId,
        string $action,
        Model $model,
        array $oldValues,
        array $newValues
    ): AuditLog {
        return AuditLog::create([
            'empresa_id' => $empresaId,
            'user_id' => Auth::id() ?? 1, // Sistema si no hay usuario autenticado
            'auditable_type' => $model::class,
            'auditable_id' => $model->id,
            'action' => $action,
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'changes_description' => $this->generateDescription($action, $oldValues, $newValues),
        ]);
    }

    /**
     * Generar descripción de cambios
     */
    private function generateDescription(string $action, array $oldValues, array $newValues): string
    {
        switch ($action) {
            case 'created':
                return 'Registro creado con ' . count($newValues) . ' campos';
            case 'deleted':
                return 'Registro eliminado';
            case 'updated':
                $cambios = array_diff_assoc($newValues, $oldValues);
                return 'Actualizado ' . count($cambios) . ' campo(s)';
            default:
                return "Acción: {$action}";
        }
    }

    /**
     * Obtener auditoría de un modelo específico
     */
    public function getModelAudit(Model $model, int $empresaId): array
    {
        $logs = AuditLog::where('empresa_id', $empresaId)
            ->where('auditable_type', $model::class)
            ->where('auditable_id', $model->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'fecha' => $log->created_at->format('Y-m-d H:i:s'),
                'usuario' => $log->user?->nombre ?? 'Sistema',
                'cambios' => $log->getChangesSummary(),
                'ip' => $log->ip_address,
            ];
        })->toArray();
    }

    /**
     * Obtener auditoría por rango de fechas
     */
    public function getAuditByDateRange(int $empresaId, \Carbon\Carbon $desde, \Carbon\Carbon $hasta): array
    {
        $logs = AuditLog::where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$desde, $hasta])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('auditable_type');

        return $logs->map(function ($group) {
            return [
                'modelo' => class_basename($group[0]->auditable_type),
                'total' => $group->count(),
                'creados' => $group->where('action', 'created')->count(),
                'actualizados' => $group->where('action', 'updated')->count(),
                'eliminados' => $group->where('action', 'deleted')->count(),
            ];
        })->toArray();
    }
}
