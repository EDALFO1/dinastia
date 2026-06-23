<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AfiliadoServicio extends BaseModel
{
    protected $table = 'afiliado_servicios';

    protected $fillable = [
        'afiliado_id',
        'servicio_id',
        'valor',
        'estado'
    ];

    public function afiliado()
    {
        return $this->belongsTo(Afiliado::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
    

}