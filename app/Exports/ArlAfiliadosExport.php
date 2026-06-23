<?php

namespace App\Exports;

use App\Models\ArlAfiliado;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArlAfiliadosExport implements FromCollection, WithHeadings
{
    protected array $filtros;

    public function __construct(array $filtros = [])
    {
        $this->filtros = $filtros;
    }

    public function headings(): array
    {
        return [
            'Tipo Doc.',
            'Número',
            'Nombre',
            'ARL',
            'Nivel Riesgo',
            'Empresa Empleadora',
            'Fecha Ingreso',
            'Fecha Retiro',
            'Base Cotización',
            'Valor ARL',
            'Administración',
            'Total Mensual',
            'Estado',
        ];
    }

    public function collection()
    {
        $query = ArlAfiliado::with(['arl', 'documento', 'empresaLaboral'])
            ->where('empresa_id', session('empresa_id'));

        if (($this->filtros['estado'] ?? '') !== '') {
            $query->where('estado', $this->filtros['estado']);
        }

        if (!empty($this->filtros['buscar'])) {
            $b = $this->filtros['buscar'];
            $query->where(function ($q) use ($b) {
                $q->where('nombre', 'like', "%{$b}%")
                  ->orWhere('numero', 'like', "%{$b}%");
            });
        }

        return $query->orderBy('nombre')->get()->map(function ($a) {
            return [
                $a->documento?->nombre ?? '',
                $a->numero,
                $a->nombre,
                $a->arl?->nombre ?? '',
                $a->arl ? 'Riesgo ' . $a->arl->nivel : '',
                $a->empresaLaboral?->nombre ?? '',
                $a->fecha_ingreso?->format('Y-m-d') ?? '',
                $a->fecha_retiro?->format('Y-m-d') ?? '',
                (float) $a->base_cotizacion,
                $a->valorArl(),
                (float) $a->administracion,
                $a->totalMensual(),
                $a->estado ? 'Activo' : 'Inactivo',
            ];
        });
    }
}
