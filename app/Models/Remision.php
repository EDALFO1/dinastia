<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Remision extends BaseModel
{
    use HasFactory;
    protected $table = 'remisiones';

    protected $fillable = [
        'empresa_id',
        'numero',
        'fecha',
        'afiliado_id',
        'dias_liquidar',
        'mensajeria',
        'intereses',
        'total'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function afiliado()
    {
        return $this->belongsTo(Afiliado::class);
    }
    public function detalles()
{
    return $this->hasMany(RemisionDetalle::class);
}
public function periodoAfiliado()
{
    return $this->belongsTo(PeriodoAfiliado::class);
}
}