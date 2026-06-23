<?php

namespace App\Exports;

use App\Models\Afiliado;
use App\Models\Recibo;
use App\Models\Afiliacion;
use App\Http\Controllers\ReciboController;
use Maatwebsite\Excel\Concerns\FromCollection;

class AfiliadosVigentesExport implements FromCollection
{
    public function collection()
{
    $empresaId = session('empresa_id');

    // =========================
    // 🔥 AFILIADOS CON RECIBO ACTIVO
    // =========================
    $idsRecibo = Recibo::where('empresa_id', $empresaId)
        ->whereNull('fecha_retiro')
        ->whereMonth('fecha', now()->month)
        ->whereYear('fecha', now()->year)
        ->pluck('afiliado_id');

    // =========================
    // 🔥 NUEVOS INGRESOS DEL MES ACTUAL
    // =========================
    $idsIngreso = Afiliacion::where('empresa_id', $empresaId)
        ->where('estado', 1)
        ->whereMonth('fecha_afiliacion', now()->month)
        ->whereYear('fecha_afiliacion', now()->year)
        ->pluck('afiliado_id');

    // =========================
    // 🔥 UNIFICAR
    // =========================
    $ids = $idsRecibo
        ->merge($idsIngreso)
        ->unique();

    // =========================
    // 🔥 TRAER AFILIADOS
    // =========================
    $afiliados = Afiliado::with([
            'empresa',
            'subtipoCotizante',
            'empresaLaboral'
        ])
        ->whereIn('id', $ids)
        ->where('estado', 1)
        ->get();

    $reciboController = new ReciboController();

    return $afiliados->map(function ($a) use ($reciboController) {

        $data = $reciboController->calcularRecibo(
             $a->id,
    now(),
    true
        );

        $total = $data['total'] ?? 0;

        $afiliacion = Afiliacion::with(['eps','pension','caja'])
            ->where('afiliado_id', $a->id)
            ->where('estado', 1)
            ->first();

        $nombre = trim(
            ($a->primer_nombre ?? '') . ' ' .
            ($a->segundo_nombre ?? '') . ' ' .
            ($a->primer_apellido ?? '') . ' ' .
            ($a->segundo_apellido ?? '')
        );

        return [
            'Empresa' => $a->empresa->nombre ?? '',
            'Documento' => $a->numero_documento,
            'Nombre completo' => $nombre,
            'Telefono' => $a->telefono,
            'Total Pagar' => $total,
            'Subtipo Cotizante' => $a->subtipoCotizante->nombre ?? '',
            'EPS' => $afiliacion->eps->nombre ?? '',
            'Nivel ARL' => $afiliacion->nivel_arl ?? '',
            'Pensión' => $afiliacion->pension->nombre ?? '',
            'Caja' => $afiliacion->caja->nombre ?? '',
            'Fecha Ingreso' => $afiliacion->fecha_afiliacion ?? '',
            'Empresa Laboral' => $a->empresaLaboral->nombre ?? '',
        ];
    });
}
}