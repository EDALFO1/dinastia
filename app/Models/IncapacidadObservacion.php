<?php

namespace App\Models;

class IncapacidadObservacion extends BaseModel
{
    protected $table = 'incapacidad_observaciones';

    protected $fillable = [
        'empresa_id',
        'incapacidad_id',
        'user_id',
        'nota',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($model) {
            if (empty($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function incapacidad()
    {
        return $this->belongsTo(Incapacidad::class);
    }
}
