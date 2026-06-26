<?php

namespace App\Domains\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
    use SoftDeletes;

    protected $table = 'audit_logs';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'changes_description',
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación con usuario que realizó la acción
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Obtener el modelo auditado
     */
    public function getAuditableModel()
    {
        if (!$this->auditable_type || !$this->auditable_id) {
            return null;
        }

        return $this->auditable_type::find($this->auditable_id);
    }

    /**
     * Obtener descripción del cambio
     */
    public function getChangesSummary(): string
    {
        if ($this->action === 'created') {
            return 'Registro creado';
        }

        if ($this->action === 'deleted') {
            return 'Registro eliminado';
        }

        if (empty($this->old_values)) {
            return 'Registro actualizado';
        }

        $cambios = [];
        foreach ($this->old_values as $campo => $valorAntiguo) {
            if (isset($this->new_values[$campo]) && $this->new_values[$campo] !== $valorAntiguo) {
                $cambios[] = "{$campo}: {$valorAntiguo} → {$this->new_values[$campo]}";
            }
        }

        return implode('; ', $cambios);
    }

    /**
     * Scopes
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByModel($query, string $model)
    {
        return $query->where('auditable_type', $model);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->whereDate('created_at', '>=', now()->subDays($days));
    }
}
