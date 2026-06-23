<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoAfiliado extends BaseModel
{

    protected $table = 'periodo_afiliados';

    protected $fillable = [
        'empresa_id',
        'afiliado_id',
        'periodo',
        'estado',
        'recibo_id'
    ];

}