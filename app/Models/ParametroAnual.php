<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametroAnual extends BaseModel
{
    protected $table = 'parametros_anuales';

    protected $fillable = [
        'empresa_id',
        'anio',
        'salario_minimo',
        'administracion'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public static function rules($id = null)
    {
        return [

            'empresa_id' => [
                'required',
                'exists:empresas,id'
            ],

            'anio' => [
                'required',
                'integer',
                'min:2000'
            ],

            'salario_minimo' => [
                'required',
                'numeric'
            ],

            'administracion' => [
                'required',
                'numeric'
            ],

        ];
    }
}