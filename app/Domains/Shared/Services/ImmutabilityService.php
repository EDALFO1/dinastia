<?php

namespace App\Domains\Shared\Services;

use App\Domains\Accounting\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ImmutabilityService
{
    /**
     * Marcas de modelos que no pueden ser editados
     */
    protected array $immutableModels = [
        JournalEntry::class => 'posteado', // JournalEntry no puede editarse si está posteado
    ];

    /**
     * Verificar si un modelo puede ser editado
     */
    public function canEdit(Model $model): bool
    {
        $modelClass = $model::class;

        if (!isset($this->immutableModels[$modelClass])) {
            return true; // Por defecto, sí puede editarse
        }

        $stateField = $this->immutableModels[$modelClass];

        // Si tiene estado posteado, no puede editarse
        if (isset($model->$stateField) && $model->$stateField === 'posteado') {
            return false;
        }

        return true;
    }

    /**
     * Verificar si un modelo puede ser eliminado
     */
    public function canDelete(Model $model): bool
    {
        return $this->canEdit($model);
    }

    /**
     * Prevenir edición si es inmutable (lanzar excepción)
     */
    public function preventEdit(Model $model, string $fieldChanged): void
    {
        if (!$this->canEdit($model)) {
            throw new \Exception("No se puede editar este registro. Estado: {$model->estado}");
        }
    }

    /**
     * Prevenir eliminación si es inmutable
     */
    public function preventDelete(Model $model): void
    {
        if (!$this->canDelete($model)) {
            throw new \Exception("No se puede eliminar este registro. Estado: {$model->estado}");
        }
    }

    /**
     * Crear un snapshot inmutable de un registro
     */
    public function createSnapshot(Model $model, int $empresaId): array
    {
        return [
            'modelo' => $model::class,
            'id' => $model->id,
            'empresa_id' => $empresaId,
            'snapshot' => $model->getAttributes(),
            'timestamp' => now()->getTimestamp(),
            'hash' => $this->calculateHash($model->getAttributes()),
        ];
    }

    /**
     * Verificar integridad del snapshot
     */
    public function verifySnapshot(array $snapshot, Model $model): bool
    {
        $currentHash = $this->calculateHash($model->getAttributes());
        return $snapshot['hash'] === $currentHash;
    }

    /**
     * Calcular hash para verificación de integridad
     */
    protected function calculateHash(array $data): string
    {
        // Excluir campos que cambian (timestamps)
        $datos = collect($data)
            ->except(['created_at', 'updated_at', 'deleted_at'])
            ->toArray();

        return hash('sha256', json_encode($datos));
    }

    /**
     * Generar certificado de integridad para período
     */
    public function generateIntegrityCertificate(
        int $empresaId,
        Carbon $desde,
        Carbon $hasta,
        string $modelType = 'JournalEntry'
    ): array {
        // Obtener todos los registros posteados del período
        $model = $modelType;
        $records = $model::where('empresa_id', $empresaId)
            ->where('estado', 'posteado')
            ->whereBetween('created_at', [$desde, $hasta])
            ->orderBy('id')
            ->get();

        if ($records->isEmpty()) {
            return [
                'error' => 'No hay registros para certificar',
            ];
        }

        $hashes = $records->map(function ($record) {
            return $this->calculateHash($record->getAttributes());
        })->toArray();

        $certificadoHash = hash('sha256', implode('', $hashes));

        return [
            'empresa_id' => $empresaId,
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'modelo' => $modelType,
            'total_registros' => $records->count(),
            'certificado_hash' => $certificadoHash,
            'fecha_certificacion' => now()->toIso8601String(),
            'integridad' => 'verified',
        ];
    }

    /**
     * Registrar cambio no permitido en auditoría
     */
    public function logUnauthorizedChange(Model $model, int $empresaId, string $reason): void
    {
        DB::table('audit_logs')->insert([
            'empresa_id' => $empresaId,
            'user_id' => auth()->id() ?? 1,
            'auditable_type' => $model::class,
            'auditable_id' => $model->id,
            'action' => 'unauthorized_attempted_change',
            'old_values' => json_encode($model->getOriginal()),
            'new_values' => null,
            'changes_description' => "Intento no autorizado de cambio: {$reason}",
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
