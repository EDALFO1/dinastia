<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReciboDetalle extends BaseModel
{
    protected $table = 'recibo_detalles';

    protected $fillable = [
        'recibo_id',
        'concepto',
        'valor'
    ];

    public function recibo()
    {
        return $this->belongsTo(Recibo::class);
    }
}
