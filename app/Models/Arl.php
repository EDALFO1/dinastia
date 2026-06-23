<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arl extends Model
{
    protected $table = 'arls';

    protected $fillable = [
        'nombre',
        'codigo',
        'nivel',
        'porcentaje',
        'actividad_economica',
    ];

    public function arl()
{
    return $this->belongsTo(Arl::class);
}

    public static function rules($id = null)
{
    return [

        'nombre' => [
            'required',
            'regex:/^[A-Za-zÁÉÍÓÚÜáéíóúüÑñ\s\.\(\)]+$/u',
            'max:255',
           
        ],

        'codigo' => [
            'required',
            'alpha_num',
            'max:20'
        ],

        'nivel' => [
            'required',
            'integer',
            'between:1,5'
        ],

        'porcentaje' => [
            'required',
            'numeric',
            'regex:/^\d+(\.\d{1,4})?$/'
        ],

        'actividad_economica' => [
            'nullable',
            'string',
            'max:7'
        ],

        // 🔥 VALIDACIÓN COMPUESTA
        'codigo_nivel' => [
            function ($attribute, $value, $fail) use ($id) {

                $existe = \App\Models\Arl::where('codigo', request('codigo'))
                    ->where('nivel', request('nivel'))
                    ->when($id, function ($query) use ($id) {
                        $query->where('id', '!=', $id);
                    })
                    ->exists();

                if ($existe) {
                    $fail('El código y nivel ya fueron registrados.');
                }
            }
        ]
    ];
}
}