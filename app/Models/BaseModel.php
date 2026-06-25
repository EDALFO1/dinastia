<?php

namespace App\Models;

use App\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Model;

/**
 * Base model for all multi-tenant models
 *
 * Automatically applies EmpresaScope to filter by the current empresa_id
 * and assigns the empresa_id when creating new records.
 *
 * All models that should be scoped by empresa_id should extend this class
 * instead of Eloquent's Model directly.
 *
 * @package App\Models
 */
class BaseModel extends Model
{
    /**
     * Boot the model and add global scopes
     *
     * @return void
     */
    protected static function booted(): void
    {
        // Apply multi-tenant filtering via EmpresaScope
        static::addGlobalScope(new EmpresaScope());

        // Automatically assign empresa_id when creating new records
        static::creating(function (self $model): void {
            if (session()->has('empresa_id') && empty($model->empresa_id)) {
                $model->empresa_id = session('empresa_id');
            }
        });
    }

    /**
     * Get the current empresa_id from the session
     *
     * @return int|null
     */
    public static function getCurrentEmpresaId(): ?int
    {
        return session('empresa_id');
    }

    /**
     * Get the current empresa_id or throw an exception
     *
     * @return int
     * @throws \RuntimeException
     */
    public static function getCurrentEmpresaIdOrFail(): int
    {
        $empresaId = static::getCurrentEmpresaId();

        if ($empresaId === null) {
            throw new \RuntimeException(
                'No empresa_id in session. User must select an empresa first.'
            );
        }

        return $empresaId;
    }

    /**
     * Query records without multi-tenant filtering
     *
     * Use with caution - only for admin operations that need to see all data
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutEmpresaScope()
    {
        return static::withoutGlobalScope(EmpresaScope::class);
    }

    /**
     * Query records for a specific empresa
     *
     * @param int $empresaId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function forEmpresa(int $empresaId)
    {
        return static::withoutGlobalScope(EmpresaScope::class)
            ->where('empresa_id', $empresaId);
    }

}