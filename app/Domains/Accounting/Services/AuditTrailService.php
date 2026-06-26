<?php

namespace App\Domains\Accounting\Services;

use App\Domains\Accounting\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuditTrailService
{
    /**
     * Generar rastreo completo de un asiento
     */
    public function getEntryAuditTrail(int $entryId): array
    {
        $asiento = JournalEntry::find($entryId);

        if (!$asiento) {
            return ['error' => 'Asiento no encontrado'];
        }

        $historial = [
            'asiento' => [
                'id' => $asiento->id,
                'numero' => $asiento->numero_asiento,
                'estado_actual' => $asiento->estado,
            ],
            'eventos' => [
                [
                    'tipo' => 'creacion',
                    'fecha' => $asiento->created_at->format('Y-m-d H:i:s'),
                    'usuario' => $asiento->usuarioCreacion?->nombre ?? 'Sistema',
                    'detalles' => 'Asiento creado en estado borrador',
                ],
            ],
            'cambios' => [],
        ];

        // Agregar evento de aprobación si existe
        if ($asiento->fecha_aprobacion) {
            $historial['eventos'][] = [
                'tipo' => 'aprobacion',
                'fecha' => $asiento->fecha_aprobacion->format('Y-m-d H:i:s'),
                'usuario' => $asiento->usuarioAprobacion?->nombre ?? 'Sistema',
                'detalles' => 'Asiento aprobado y posteado',
            ];
        }

        // Agregar evento de última actualización
        if ($asiento->updated_at && $asiento->updated_at > $asiento->created_at) {
            $historial['eventos'][] = [
                'tipo' => 'actualizacion',
                'fecha' => $asiento->updated_at->format('Y-m-d H:i:s'),
                'usuario' => $asiento->usuarioCreacion?->nombre ?? 'Sistema',
                'detalles' => 'Asiento actualizado',
            ];
        }

        // Información de líneas
        $historial['lineas'] = $asiento->lines->map(function ($linea) {
            return [
                'id' => $linea->id,
                'cuenta' => [
                    'codigo' => $linea->account->codigo,
                    'nombre' => $linea->account->nombre,
                ],
                'tipo_movimiento' => $linea->tipo_movimiento,
                'valor' => (float) $linea->valor,
                'creado' => $linea->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        return $historial;
    }

    /**
     * Generar reporte de auditoría por usuario
     */
    public function getAuditByUser(int $empresaId, int $usuarioId, Carbon $desde, Carbon $hasta): array
    {
        $asientos = JournalEntry::where('empresa_id', $empresaId)
            ->where('usuario_creacion_id', $usuarioId)
            ->whereBetween('created_at', [$desde, $hasta])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'usuario_id' => $usuarioId,
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'resumen' => [
                'total_asientos_creados' => $asientos->count(),
                'total_aprobados' => $asientos->where('estado', 'posteado')->count(),
                'total_rechazados' => $asientos->where('estado', 'rechazado')->count(),
                'total_en_borrador' => $asientos->where('estado', 'borrador')->count(),
                'valor_total_movido' => (float) $asientos->sum(function ($a) {
                    return $a->lines->sum('valor');
                }),
            ],
            'asientos' => $asientos->map(function ($asiento) {
                return [
                    'id' => $asiento->id,
                    'numero' => $asiento->numero_asiento,
                    'fecha' => $asiento->fecha->format('Y-m-d'),
                    'descripcion' => $asiento->descripcion,
                    'estado' => $asiento->estado,
                    'total_lineas' => $asiento->lines->count(),
                    'monto_total' => (float) $asiento->getTotalDebit(),
                ];
            })->toArray(),
        ];
    }

    /**
     * Generar reporte de auditoría por período
     */
    public function getAuditByPeriod(int $empresaId, Carbon $desde, Carbon $hasta): array
    {
        $asientos = JournalEntry::where('empresa_id', $empresaId)
            ->whereBetween('fecha', [$desde, $hasta])
            ->get();

        $cambios = [];

        foreach ($asientos as $asiento) {
            if ($asiento->updated_at > $asiento->created_at) {
                $cambios[] = [
                    'asiento_id' => $asiento->id,
                    'numero' => $asiento->numero_asiento,
                    'creado' => $asiento->created_at->format('Y-m-d H:i:s'),
                    'modificado' => $asiento->updated_at->format('Y-m-d H:i:s'),
                    'usuario_creacion' => $asiento->usuarioCreacion?->nombre,
                    'estado_actual' => $asiento->estado,
                ];
            }
        }

        return [
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'resumen' => [
                'total_asientos' => $asientos->count(),
                'asientos_posteados' => $asientos->where('estado', 'posteado')->count(),
                'asientos_modificados' => count($cambios),
                'valor_total' => (float) $asientos->sum(function ($a) {
                    return $a->getTotalDebit();
                }),
            ],
            'asientos_modificados' => $cambios,
            'por_estado' => [
                'borrador' => $asientos->where('estado', 'borrador')->count(),
                'posteado' => $asientos->where('estado', 'posteado')->count(),
                'rechazado' => $asientos->where('estado', 'rechazado')->count(),
            ],
        ];
    }

    /**
     * Detectar cambios sospechosos
     */
    public function detectSuspiciousActivity(int $empresaId, Carbon $desde, Carbon $hasta): array
    {
        $actividades = [];

        // Asientos sin usuario de creación
        $sinUsuario = JournalEntry::where('empresa_id', $empresaId)
            ->whereNull('usuario_creacion_id')
            ->whereBetween('created_at', [$desde, $hasta])
            ->count();

        if ($sinUsuario > 0) {
            $actividades[] = [
                'tipo' => 'asientos_sin_usuario',
                'severidad' => 'media',
                'cantidad' => $sinUsuario,
                'descripcion' => 'Asientos creados sin usuario registrado',
            ];
        }

        // Asientos con grandes montos sin aprobación explícita
        $grandesMontos = JournalEntry::where('empresa_id', $empresaId)
            ->where('estado', 'posteado')
            ->whereBetween('created_at', [$desde, $hasta])
            ->get()
            ->filter(function ($a) {
                return $a->getTotalDebit() > 10000000; // > 10 millones
            });

        if ($grandesMontos->count() > 0) {
            $actividades[] = [
                'tipo' => 'montos_elevados',
                'severidad' => 'baja',
                'cantidad' => $grandesMontos->count(),
                'descripcion' => 'Asientos con montos elevados (>10M)',
            ];
        }

        // Múltiples rechazos del mismo usuario
        $rechazos = JournalEntry::where('empresa_id', $empresaId)
            ->where('estado', 'rechazado')
            ->whereBetween('created_at', [$desde, $hasta])
            ->groupBy('usuario_creacion_id')
            ->havingRaw('count(*) > 5')
            ->count();

        if ($rechazos > 0) {
            $actividades[] = [
                'tipo' => 'multiples_rechazos',
                'severidad' => 'baja',
                'cantidad' => $rechazos,
                'descripcion' => 'Usuarios con muchos asientos rechazados',
            ];
        }

        return [
            'periodo' => [
                'desde' => $desde->format('Y-m-d'),
                'hasta' => $hasta->format('Y-m-d'),
            ],
            'actividades_sospechosas' => $actividades,
            'total_alertas' => count($actividades),
        ];
    }
}
