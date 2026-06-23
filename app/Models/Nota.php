<?php

namespace App\Models;

class Nota extends BaseModel
{
    protected $table = 'notas';

    protected $fillable = [
        'empresa_id',
        'creado_por_id',
        'titulo',
        'descripcion',
        'tipo',
        'estado',
        'fecha_vencimiento',
        'fecha_resuelto',
        'resuelto_por_id',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha_resuelto'    => 'datetime',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($model) {
            if (empty($model->creado_por_id)) {
                $model->creado_por_id = auth()->id();
            }
        });
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }

    public function resueltoPor()
    {
        return $this->belongsTo(User::class, 'resuelto_por_id');
    }

    public function estaVencida(): bool
    {
        return $this->fecha_vencimiento !== null
            && $this->fecha_vencimiento->isPast()
            && !in_array($this->estado, ['resuelto', 'cancelado']);
    }

    // ── Constantes de referencia ─────────────────────────────────────────

    public static function tipos(): array
    {
        return [
            'traslado'       => 'Traslado',
            'certificado'    => 'Certificado',
            'cambio_empresa' => 'Cambio de empresa',
            'recordatorio'   => 'Recordatorio',
            'otro'           => 'Otro',
        ];
    }

    public static function estados(): array
    {
        return [
            'pendiente'  => ['label' => 'Pendiente',   'color' => 'warning'],
            'en_proceso' => ['label' => 'En proceso',  'color' => 'primary'],
            'resuelto'   => ['label' => 'Resuelto',    'color' => 'success'],
            'cancelado'  => ['label' => 'Cancelado',   'color' => 'secondary'],
        ];
    }
}
