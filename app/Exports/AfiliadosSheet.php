<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class AfiliadosSheet implements WithHeadings, FromArray
{
    public function headings(): array
    {
        return [
            'empresa_laboral',
            'asesor',
            'tipo_documento',
            'subtipo_cotizante',
            'numero_documento',
            'primer_nombre',
            'segundo_nombre',
            'primer_apellido',
            'segundo_apellido',
            'fecha_nacimiento',
            'sexo',
            'correo',
            'telefono',
            'direccion',
            'ciudad'
        ];
    }

    public function array(): array
    {
        return [
            [
                'Empresa Demo SAS',
                'Juan Pérez',
                'CC',
                'DEPENDIENTE',
                '123456789',
                'Carlos',
                'Andrés',
                'García',
                'López',
                '1990-05-15',
                'M',
                'correo@demo.com',
                '3001234567',
                'Calle 123 #45-67',
                'Cali'
            ]
        ];
    }
}