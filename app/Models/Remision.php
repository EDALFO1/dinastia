<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remision extends BaseModel
{
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