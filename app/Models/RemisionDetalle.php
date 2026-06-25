<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemisionDetalle extends BaseModel
{

    protected $table = 'remision_detalles';

    protected $fillable = [
        'empresa_id',
        'remision_id',
        'concepto',
        'valor'
    ];

    public function remision()
    {
        return $this->belongsTo(Remision::class);
    }

}