<?php

namespace App\Exports;

use App\Models\Empresa;
use App\Models\EmpresaLaboral;
use App\Models\Asesor;
use App\Models\Documento;
use App\Models\SubtipoCotizante;
use Maatwebsite\Excel\Concerns\FromArray;

class ListasSheet implements FromArray
{
    public function array(): array
    {
        $empresas = Empresa::pluck('nombre')->toArray();
        $empresasLaborales = EmpresaLaboral::pluck('nombre')->toArray();
        $asesores = Asesor::pluck('nombre')->toArray();
        $documentos = Documento::pluck('codigo')->toArray();
        $subtipos = SubtipoCotizante::pluck('nombre')->toArray();

        return [
            ['EMPRESAS', ...$empresas],
            ['EMPRESAS_LABORALES', ...$empresasLaborales],
            ['ASESORES', ...$asesores],
            ['DOCUMENTOS', ...$documentos],
            ['SUBTIPOS', ...$subtipos],
            ['SEXO', 'M', 'F', 'Otro'],
        ];
    }
}