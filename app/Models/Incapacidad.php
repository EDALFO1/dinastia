<?php

namespace App\Models;

use Carbon\Carbon;

class Incapacidad extends BaseModel
{
    protected $table = 'incapacidades';

    protected $fillable = [
        'empresa_id',
        'afiliado_id',
        'documento',
        'nombre',
        'empresa_laboral_id',
        'empresa_externa_id',
        'entidad_tipo',
        'eps_id',
        'arl_id',
        'entidad_nombre',
        'fecha_inicio',
        'fecha_fin',
        'dias_solicitados',
        'fecha_radicacion',
        'estado',
        'fecha_pago',
    ];

    protected $casts = [
        'fecha_inicio'     => 'date',
        'fecha_fin'        => 'date',
        'fecha_radicacion' => 'date',
        'fecha_pago'       => 'date',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────

    public function afiliado()
    {
        return $this->belongsTo(Afiliado::class);
    }

    public function empresaLaboral()
    {
        return $this->belongsTo(EmpresaLaboral::class);
    }

    public function eps()
    {
        return $this->belongsTo(Eps::class);
    }

    public function arl()
    {
        return $this->belongsTo(Arl::class);
    }

    public function observaciones()
    {
        return $this->hasMany(IncapacidadObservacion::class)->latest();
    }

    // ── Catálogos ────────────────────────────────────────────────────────

    public static function estados(): array
    {
        return [
            'transcrita'       => ['label' => 'Transcrita',        'color' => 'secondary'],
            'pendiente_radicar'=> ['label' => 'Pendiente radicar',  'color' => 'warning'],
            'radicada'         => ['label' => 'Radicada',           'color' => 'primary'],
            'aprobada'         => ['label' => 'Aprobada',           'color' => 'info'],
            'liquidada'        => ['label' => 'Liquidada',          'color' => 'dark'],
            'rechazada'        => ['label' => 'Rechazada',          'color' => 'danger'],
            'pagada'           => ['label' => 'Pagada',             'color' => 'success'],
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function estadoInfo(): array
    {
        return static::estados()[$this->estado] ?? ['label' => $this->estado, 'color' => 'secondary'];
    }

    public static function calcularDias(string $inicio, string $fin): int
    {
        return (int) Carbon::parse($inicio)->diffInDays(Carbon::parse($fin)) + 1;
    }
}
