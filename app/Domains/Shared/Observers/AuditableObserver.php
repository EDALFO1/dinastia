<?php

namespace App\Domains\Shared\Observers;

use App\Domains\Shared\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    protected AuditLogService $auditService;

    public function __construct(AuditLogService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Ejecutar cuando se crea un modelo
     */
    public function created(Model $model): void
    {
        // Obtener empresa_id del modelo
        $empresaId = $model->empresa_id ?? session('empresa_id');

        if (!$empresaId) {
            return;
        }

        try {
            $this->auditService->logCreated($model, $empresaId);
        } catch (\Exception $e) {
            \Log::error('Error registrando auditoría de creación', [
                'model' => $model::class,
                'id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ejecutar cuando se actualiza un modelo
     */
    public function updated(Model $model): void
    {
        $empresaId = $model->empresa_id ?? session('empresa_id');

        if (!$empresaId) {
            return;
        }

        try {
            $this->auditService->logUpdated($model, $empresaId);
        } catch (\Exception $e) {
            \Log::error('Error registrando auditoría de actualización', [
                'model' => $model::class,
                'id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ejecutar cuando se elimina un modelo
     */
    public function deleted(Model $model): void
    {
        $empresaId = $model->empresa_id ?? session('empresa_id');

        if (!$empresaId) {
            return;
        }

        try {
            $this->auditService->logDeleted($model, $empresaId);
        } catch (\Exception $e) {
            \Log::error('Error registrando auditoría de eliminación', [
                'model' => $model::class,
                'id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ejecutar cuando se restaura un modelo
     */
    public function restored(Model $model): void
    {
        $empresaId = $model->empresa_id ?? session('empresa_id');

        if (!$empresaId) {
            return;
        }

        try {
            $this->auditService->logAction('restored', $model, $empresaId);
        } catch (\Exception $e) {
            \Log::error('Error registrando auditoría de restauración', [
                'model' => $model::class,
                'id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
