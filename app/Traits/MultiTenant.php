<?php

namespace App\Traits;

use App\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * MultiTenant Trait for Eloquent Models
 *
 * Provides multi-tenant functionality with automatic empresa_id filtering
 * and assignment. Can be used as an alternative to extending BaseModel.
 *
 * Usage:
 * ```php
 * class MyModel extends Model
 * {
 *     use MultiTenant;
 * }
 * ```
 */
trait MultiTenant
{
    /**
     * Boot the multi-tenant trait
     *
     * @return void
     */
    public static function bootMultiTenant(): void
    {
        // Apply global scope for empresa filtering
        static::addGlobalScope(new EmpresaScope());

        // Automatically assign empresa_id when creating records
        static::creating(function ($model): void {
            if (session()->has('empresa_id') && empty($model->empresa_id)) {
                $model->empresa_id = session('empresa_id');
            }
        });
    }

    /**
     * Get the current empresa_id from session
     *
     * @return int|null
     */
    public static function getCurrentEmpresaId(): ?int
    {
        return session('empresa_id');
    }

    /**
     * Get current empresa_id or fail if not set
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
     * Query without multi-tenant scope (admin use only)
     *
     * @return Builder
     */
    public static function withoutEmpresaScope(): Builder
    {
        return static::withoutGlobalScope(EmpresaScope::class);
    }

    /**
     * Query for a specific empresa
     *
     * @param int $empresaId
     * @return Builder
     */
    public static function forEmpresa(int $empresaId): Builder
    {
        return static::withoutGlobalScope(EmpresaScope::class)
            ->where('empresa_id', $empresaId);
    }
}
