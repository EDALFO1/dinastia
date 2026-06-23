<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ModuloService
{
    private array $cache = [];

    public function puedeAcceder(string $slug): bool
    {
        return $this->modulosActivos()->contains($slug);
    }

    public function modulosActivos(): Collection
    {
        if (! auth()->check()) {
            return collect();
        }

        $empresaId = session('empresa_id');
        $rolId     = auth()->user()->rol_id;
        $key       = "{$rolId}:{$empresaId}";

        if (! isset($this->cache[$key])) {
            if (! $empresaId) {
                // Sin empresa activa: sólo los módulos que no requieren empresa
                $this->cache[$key] = DB::table('modulos as m')
                    ->join('rol_modulo as rm', 'rm.modulo_id', '=', 'm.id')
                    ->where('rm.rol_id', $rolId)
                    ->where('m.activo', true)
                    ->whereIn('m.slug', ['dashboard'])
                    ->pluck('m.slug');
            } else {
                $this->cache[$key] = DB::table('modulos as m')
                    ->join('rol_modulo as rm', 'rm.modulo_id', '=', 'm.id')
                    ->join('empresa_modulo as em', 'em.modulo_id', '=', 'm.id')
                    ->where('rm.rol_id', $rolId)
                    ->where('em.empresa_id', $empresaId)
                    ->where('m.activo', true)
                    ->pluck('m.slug');
            }
        }

        return collect($this->cache[$key]);
    }

    public function modulosActivosConDatos(): Collection
    {
        if (! auth()->check()) {
            return collect();
        }

        $empresaId = session('empresa_id');
        $rolId     = auth()->user()->rol_id;

        if (! $empresaId) {
            return collect();
        }

        return DB::table('modulos as m')
            ->join('rol_modulo as rm', 'rm.modulo_id', '=', 'm.id')
            ->join('empresa_modulo as em', 'em.modulo_id', '=', 'm.id')
            ->where('rm.rol_id', $rolId)
            ->where('em.empresa_id', $empresaId)
            ->where('m.activo', true)
            ->orderBy('m.grupo')
            ->orderBy('m.orden')
            ->select('m.*')
            ->get();
    }
}
