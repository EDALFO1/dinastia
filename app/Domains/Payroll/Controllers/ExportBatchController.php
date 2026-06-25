<?php

namespace App\Domains\Payroll\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Recibo;
use App\Models\ExportBatch;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

class ExportBatchController extends Controller
{
    public function crearLote(Request $request)
    {
        $empresaId = session('empresa_id');

        if(!$empresaId){
            return back()->with('error','No hay empresa seleccionada');
        }

        // ðŸ”¥ TRAER EMPRESA (ESTO ERA EL BUG)
        $empresa = Empresa::find($empresaId);

        if(!$empresa){
            return back()->with('error','Empresa no encontrada');
        }

        // =========================
        // ðŸ”¥ RECIBOS
        // =========================
        $recibos = Recibo::with('afiliado')
            ->where('empresa_id', $empresaId)
            ->whereNull('export_batch_id')
            ->get();

        if($recibos->isEmpty()){
            return back()->with('error','No hay recibos para exportar');
        }

        // =========================
        // ðŸ”¥ SEPARAR POR CAJA
        // =========================
        $comfiar = collect();
        $otros = collect();

        foreach ($recibos as $r) {

            $afiliacion = \App\Models\Afiliacion::where('afiliado_id', $r->afiliado_id)
                ->where('estado', 1)
                ->first();

            if(!$afiliacion){
                continue; // ðŸ”¥ evita errores
            }

            $caja = strtoupper(trim(optional($afiliacion->caja)->nombre));

            if ($caja === 'COMFIAR') {
                $comfiar->push($r);
            } else {
                $otros->push($r);
            }
        }

        DB::beginTransaction();

        try {

            // =========================
            // ðŸ”¥ CREAR LOTE
            // =========================
            $batch = new ExportBatch();
            $batch->empresa_id = $empresa->id;
            $batch->codigo = 'PILA-'.now()->format('YmHis');
            $batch->periodo = now()->format('Y-m');
            $batch->recibos_count = $recibos->count();
            $batch->total = $recibos->sum('total');
            $batch->save();

            if(!$batch->id){
                throw new \Exception("No se pudo crear el lote");
            }

            // =========================
            // ðŸ”¥ MARCAR RECIBOS
            // =========================
            foreach ($recibos as $r) {
                $r->export_batch_id = $batch->id;
                $r->save();
            }

            // =========================
            // ðŸ”¥ GENERAR ARCHIVOS
            // =========================
            $files = [];

            if($comfiar->count()){
                $contenido = (new \App\Exports\PilaTxtExport($comfiar, $empresa))->generar();
                $ruta = storage_path("app/pila_comfiar_{$batch->id}.txt");
                file_put_contents($ruta, $contenido);
                $files[] = $ruta;
            }

            if($otros->count()){
                $contenido = (new \App\Exports\PilaTxtExport($otros, $empresa))->generar();
                $ruta = storage_path("app/pila_otros_{$batch->id}.txt");
                file_put_contents($ruta, $contenido);
                $files[] = $ruta;
            }

            DB::commit();

            // =========================
            // ðŸ”¥ ZIP
            // =========================
            $zipPath = storage_path("app/pila_{$batch->id}.zip");

            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE);

            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }

            $zip->close();

            return response()->download($zipPath);

        } catch (\Exception $e){

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
    public function index()
{
    $empresaId = session('empresa_id');

    $batches = \App\Models\ExportBatch::where('empresa_id', $empresaId)
        ->latest()
        ->paginate(10);

    return view('modules.exportaciones.index', compact('batches'));
}
public function show($id)
{
    $empresaId = session('empresa_id');

    $batch = \App\Models\ExportBatch::with('recibos.afiliado')
        ->where('empresa_id', $empresaId)
        ->findOrFail($id);

    // ðŸ”¥ CALCULAR TOTALES
    $totales = [
        'eps' => 0,
        'pension' => 0,
        'arl' => 0,
        'caja' => 0,
        'total_general' => 0
    ];

    foreach ($batch->recibos as $r) {

        $totales['eps'] += $r->valor_eps ?? 0;
        $totales['pension'] += $r->valor_pension ?? 0;
        $totales['arl'] += $r->valor_arl ?? 0;
        $totales['caja'] += $r->valor_caja ?? 0;
        $totales['total_general'] += $r->total ?? 0;
    }

    return view('modules.exportaciones.show', compact('batch','totales'));
}
public function descargar($id)
{
    $empresaId = session('empresa_id');

    $batch = \App\Models\ExportBatch::where('empresa_id', $empresaId)
        ->findOrFail($id);

    // ðŸ”¥ RUTA DEL ZIP (igual a cuando lo creaste)
    $zipPath = storage_path("app/pila_{$batch->id}.zip");

    if (!file_exists($zipPath)) {
        return back()->with('error', 'Archivo no encontrado');
    }

    return response()->download($zipPath);
}
public function reversar($id)
{
    $empresaId = session('empresa_id');

    $batch = \App\Models\ExportBatch::with('recibos')
        ->where('empresa_id', $empresaId)
        ->findOrFail($id);

    \DB::beginTransaction();

    try {

        // ðŸ”¥ DESMARCAR RECIBOS
        foreach ($batch->recibos as $r) {
            $r->export_batch_id = null;
            $r->save();
        }

        // ðŸ”¥ BORRAR ARCHIVOS
        $zipPath = storage_path("app/pila_{$batch->id}.zip");

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        // (opcional) borrar txt individuales
        $txt1 = storage_path("app/pila_comfiar_{$batch->id}.txt");
        $txt2 = storage_path("app/pila_otros_{$batch->id}.txt");

        if (file_exists($txt1)) unlink($txt1);
        if (file_exists($txt2)) unlink($txt2);

        // ðŸ”¥ ELIMINAR LOTE
        $batch->delete();

        \DB::commit();

        return redirect()
            ->route('export.index')
            ->with('success', 'Lote reversado correctamente');

    } catch (\Exception $e){

        \DB::rollBack();

        return back()->with('error', $e->getMessage());
    }
}
}

