<?php

namespace App\Exports;

use App\Models\Afiliado;
use Maatwebsite\Excel\Concerns\FromCollection;

class AfiliadosExport implements FromCollection
{
    protected $filtros;

    public function __construct($filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        $query = Afiliado::with([
            'empresaLaboral',
            'documento',
            'asesor',
            'subtipoCotizante'
        ])
        ->where('empresa_id', session('empresa_id'));

        // 🔥 FILTRO ESTADO
        if (($this->filtros['estado'] ?? '') !== '') {
            $query->where('estado', $this->filtros['estado']);
        }

        // 🔍 BUSCADOR
        if (!empty($this->filtros['buscar'])) {

            $buscar = $this->filtros['buscar'];

            $query->where(function ($q) use ($buscar) {

                $q->where('primer_nombre', 'like', "%{$buscar}%")
                  ->orWhere('primer_apellido', 'like', "%{$buscar}%")
                  ->orWhere('numero_documento', 'like', "%{$buscar}%");

            });
        }

        return $query->get()->map(function ($a) {

            return [

                'Documento' =>
                    ($a->documento?->nombre ?? '') . ' ' .
                    $a->numero_documento,

                'Primer Nombre' => $a->primer_nombre,
                'Segundo Nombre' => $a->segundo_nombre,
                'Primer Apellido' => $a->primer_apellido,
                'Segundo Apellido' => $a->segundo_apellido,

                'Fecha Nacimiento' =>
                    optional($a->fecha_nacimiento)->format('Y-m-d'),

                'Sexo' => $a->sexo,

                'Correo' => $a->correo,

                'Teléfono' => $a->telefono,

                'Dirección' => $a->direccion,

                'Ciudad' => $a->ciudad,

                'Empresa Laboral' =>
                    $a->empresaLaboral?->nombre ?? '',

                'Asesor' =>
                    $a->asesor?->nombre ?? '',

                'Subtipo Cotizante' =>
                    $a->subtipoCotizante?->nombre ?? '',

                'Estado' =>
                    $a->estado ? 'Activo' : 'Inactivo',
            ];
        });
    }
}