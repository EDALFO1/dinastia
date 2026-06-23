<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use App\Models\ReciboDetalle;
use App\Models\Afiliado;
use App\Models\Afiliacion;
use App\Models\ParametroAnual;
use App\Models\AfiliadoServicio;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\AfiliadosVigentesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PilaExcelExport;
use App\Exports\PilaRealExport;


class ReciboController extends Controller
{
    public function index()
{
    $empresaId = session('empresa_id');

    // 🔥 PERIODO ACTUAL
    // EJEMPLO:
    // si hoy es junio -> mostrar recibos del periodo mayo
    $periodo = now()->subMonth()->format('Y-m');

    // 🔥 SOLO RECIBOS DEL PERIODO ACTUAL
    $recibos = Recibo::with('afiliado')
        ->where('empresa_id', $empresaId)
        ->whereRaw("
            DATE_FORMAT(
                DATE_SUB(fecha, INTERVAL 1 MONTH),
                '%Y-%m'
            ) = ?
        ", [$periodo])
        ->latest()
        ->paginate(15);

    // 🔥 PENDIENTES SOLO DEL PERIODO ACTUAL
    $pendientes = Recibo::where('empresa_id', $empresaId)
        ->whereNull('export_batch_id')
        ->whereRaw("
            DATE_FORMAT(
                DATE_SUB(fecha, INTERVAL 1 MONTH),
                '%Y-%m'
            ) = ?
        ", [$periodo])
        ->count();

    return view(
        'modules.recibos.index',
        compact('recibos', 'pendientes')
    );
}

    public function create()
    {
        $afiliados = Afiliado::orderBy('primer_apellido')->get();

        return view('modules.recibos.create', compact('afiliados'));
    }

   

    public function edit(Recibo $recibo)
    {
        if($recibo->export_batch_id){
            return back()->with('error','No se puede editar, ya fue exportado');
        }

        $afiliados = Afiliado::orderBy('primer_apellido')->get();

        return view('modules.recibos.edit', compact('recibo','afiliados'));
    }

    // =========================
// 🔥 STORE
// =========================
public function store(Request $request)
{
    $request->validate([
        'afiliado_id' => 'required|exists:afiliados,id',
        'fecha' => 'required|date'
    ]);

    // 🔥 VALIDAR FECHA RETIRO
    if ($request->novedad == 'Retiro') {

        $fechaRetiro = \Carbon\Carbon::parse($request->fecha_retiro);
        $periodo = \Carbon\Carbon::parse($request->fecha)->subMonth();

        if (
            $fechaRetiro->month != $periodo->month ||
            $fechaRetiro->year != $periodo->year
        ) {
            return back()->with('error', 'La fecha de retiro debe ser del mes anterior');
        }
    }

    $empresaId = session('empresa_id');

    $data = $this->calcularRecibo(
        $request->afiliado_id,
        $request->fecha
    );

    if(!$data){
        return back()->with('error','No se pudo calcular el recibo');
    }

    $periodoFecha = Carbon::parse($request->fecha)->subMonth();
    $periodo = $periodoFecha->format('Y-m');

    DB::beginTransaction();

    try {

        $existe = Recibo::where('empresa_id', $empresaId)
            ->where('afiliado_id', $request->afiliado_id)
            ->whereRaw("DATE_FORMAT(DATE_SUB(fecha, INTERVAL 1 MONTH),'%Y-%m') = ?", [$periodo])
            ->exists();

        if($existe){
            return back()->with('error','Ya existe recibo para este período');
        }

        $numero = Recibo::lockForUpdate()
            ->where('empresa_id', $empresaId)
            ->max('numero') + 1;

        // 🔥 TOTAL FINAL
        $totalFinal = $data['total'];

        if ($request->cargos) {
            foreach ($request->cargos as $cargo) {
                $valor = floatval($cargo['valor'] ?? 0);
                if ($valor > 0) {
                    $totalFinal += $valor;
                }
            }
        }

        $recibo = Recibo::create([
            'empresa_id' => $empresaId,
            'numero' => $numero,
            'fecha' => $request->fecha,
            'afiliado_id' => $request->afiliado_id,
            'dias_liquidar' => $data['dias'],
            'ibc' => $data['ibc'],

            'valor_eps' => $data['eps'],
            'valor_arl' => $data['arl'],
            'valor_pension' => $data['pension'],
            'valor_caja' => $data['caja'],

            'valor_admon' => $data['admin'],
            'valor_servicios' => $data['servicios'],

            'total' => $totalFinal,

            'novedad' => $request->novedad,
            'fecha_retiro' => $request->fecha_retiro
        ]);

        // 🔥 DETALLES BASE
        foreach($data['detalles'] as $d){
            ReciboDetalle::create([
                'empresa_id' => $empresaId,
                'recibo_id' => $recibo->id,
                'concepto' => $d['concepto'],
                'valor' => $d['valor']
            ]);
        }

        // 🔥 CARGOS DINÁMICOS
        if ($request->cargos) {
            foreach ($request->cargos as $cargo) {

                $valor = floatval($cargo['valor'] ?? 0);

                if ($valor > 0) {
                    ReciboDetalle::create([
                        'empresa_id' => $empresaId,
                        'recibo_id' => $recibo->id,
                        'concepto' => $cargo['concepto'] ?? 'Cargo',
                        'valor' => $valor
                    ]);
                }
            }
        }

        // 🔥 INACTIVAR AFILIADO
        if ($request->novedad == 'Retiro') {
            Afiliado::where('id', $request->afiliado_id)
                ->update(['estado' => 0]);
        }

        DB::commit();

    } catch (\Exception $e){
        DB::rollBack();
        return back()->with('error','Error al generar recibo');
    }

    return redirect()->route('recibos.index')
        ->with('success','Recibo generado correctamente');
}


// =========================
// 🔥 UPDATE
// =========================
public function update(Request $request, Recibo $recibo)
{
    if($recibo->export_batch_id){
        return back()->with('error','No se puede editar, ya fue exportado');
    }

    $request->validate([
        'afiliado_id' => 'required|exists:afiliados,id',
        'fecha' => 'required|date',
    ]);

    $empresaId = session('empresa_id');

    DB::beginTransaction();

    try {

        $data = $this->calcularRecibo(
            $request->afiliado_id,
            $request->fecha
        );

        if(!$data){
            return back()->with('error','No se pudo recalcular');
        }

        $periodoFecha = Carbon::parse($request->fecha)->subMonth();
        $periodo = $periodoFecha->format('Y-m');

        $existe = Recibo::where('empresa_id', $empresaId)
            ->where('afiliado_id', $request->afiliado_id)
            ->where('id','!=',$recibo->id)
            ->whereRaw("DATE_FORMAT(DATE_SUB(fecha, INTERVAL 1 MONTH),'%Y-%m') = ?", [$periodo])
            ->exists();

        if($existe){
            return back()->with('error','Ya existe recibo para este período');
        }

        // 🔥 TOTAL FINAL
        $totalFinal = $data['total'];

        if ($request->cargos) {
            foreach ($request->cargos as $cargo) {
                $valor = floatval($cargo['valor'] ?? 0);
                if ($valor > 0) {
                    $totalFinal += $valor;
                }
            }
        }

        $recibo->update([
            'afiliado_id' => $request->afiliado_id,
            'fecha' => $request->fecha,
            'dias_liquidar' => $data['dias'],
            'ibc' => $data['ibc'],

            'valor_eps' => $data['eps'],
            'valor_arl' => $data['arl'],
            'valor_pension' => $data['pension'],
            'valor_caja' => $data['caja'],

            'valor_admon' => $data['admin'],
            'valor_servicios' => $data['servicios'],

            'total' => $totalFinal,

            'novedad' => $request->novedad,
            'fecha_retiro' => $request->fecha_retiro
        ]);

        // 🔥 LIMPIAR DETALLES
        $recibo->detalles()->delete();

        // 🔥 BASE
        foreach($data['detalles'] as $d){
            ReciboDetalle::create([
                'empresa_id' => $empresaId,
                'recibo_id' => $recibo->id,
                'concepto' => $d['concepto'],
                'valor' => $d['valor']
            ]);
        }

        // 🔥 DINÁMICOS
        if ($request->cargos) {
            foreach ($request->cargos as $cargo) {

                $valor = floatval($cargo['valor'] ?? 0);

                if ($valor > 0) {
                    ReciboDetalle::create([
                        'empresa_id' => $empresaId,
                        'recibo_id' => $recibo->id,
                        'concepto' => $cargo['concepto'] ?? 'Cargo',
                        'valor' => $valor
                    ]);
                }
            }
        }

        DB::commit();

    } catch (\Exception $e){
        DB::rollBack();
        return back()->with('error','Error al actualizar');
    }

    return redirect()->route('recibos.index')
        ->with('success','Recibo actualizado correctamente');
}

    public function destroy(Recibo $recibo)
    {
        if($recibo->export_batch_id){
            return back()->with('error','No se puede eliminar, ya fue exportado');
        }

        $recibo->delete();

        return redirect()->route('recibos.index')
            ->with('success','Recibo eliminado correctamente');
    }
   public function calcularRecibo($afiliadoId, $fecha, $permitirMismoMes = false)
{
    $afiliado = Afiliado::find($afiliadoId);
    if (!$afiliado) return null;

    $afiliacion = Afiliacion::where('afiliado_id', $afiliadoId)
        ->where('estado', 1)
        ->with(['eps', 'pension', 'caja'])
        ->first();

    if (!$afiliacion) return null;

    $fechaRecibo = Carbon::parse($fecha);

    // 🔥 PERIODO
    if ($permitirMismoMes) {

    // 🔥 PARA EXPORTAR VIGENTES
    // LIQUIDAR MES ACTUAL
    $periodo = $fechaRecibo->copy();

} else {

    // 🔥 RECIBO NORMAL
    // LIQUIDA MES ANTERIOR
    $periodo = $fechaRecibo->copy()->subMonthNoOverflow();
}

$finPeriodo = $periodo->copy()->startOfMonth()->addDays(29);

    $fechaIngreso = Carbon::parse($afiliacion->fecha_afiliacion)->startOfDay();

    if (
    !$permitirMismoMes &&
    $fechaIngreso->year == $fechaRecibo->year &&
    $fechaIngreso->month == $fechaRecibo->month
) {
    return null;
}

    if (
    !$permitirMismoMes &&
    $fechaIngreso->gt($finPeriodo)
) {
    return null;
}

    // 🔥 DIAS
    $dias = 30;

    // 🔹 CASO INGRESO EN EL PERIODO
    if (
        $fechaIngreso->year == $periodo->year &&
        $fechaIngreso->month == $periodo->month
    ) {
        $diaIngreso = min($fechaIngreso->day, 30);
        $dias = 30 - ($diaIngreso - 1);
    }

    // 🔹 CASO RETIRO (AJUSTADO)
    if (request('novedad') == 'Retiro' && request('fecha_retiro')) {

        $fechaRetiro = \Carbon\Carbon::parse(request('fecha_retiro'));

        if (
            $fechaRetiro->month == $periodo->month &&
            $fechaRetiro->year == $periodo->year
        ) {
            $dias = min($fechaRetiro->day, 30);
        }
    }

    if ($dias <= 0) return null;

    // 🔥 PARAMETROS
    $anio = $fechaRecibo->month == 1
        ? $fechaRecibo->year - 1
        : $fechaRecibo->year;

    $parametro = ParametroAnual::where('empresa_id', session('empresa_id'))
        ->where('anio', $anio)
        ->first();

    if (!$parametro) return null;

    // 🔥 IBC
    $ibc = $afiliacion->tipo_ibc === 'FIJO'
        ? $afiliacion->ibc
        : $parametro->salario_minimo;

    // =========================
    // 🔥 VALIDACIONES PRO
    // =========================
    $tieneEntidad = function ($entidad) {
        if (!$entidad) return false;

        $nombre = strtoupper(trim($entidad->nombre));

        return !in_array($nombre, ['NINGUNA']);
    };

    $tieneCajaValida = function ($caja) {
        if (!$caja) return false;

        $nombre = strtoupper(trim($caja->nombre));

        return !in_array($nombre, ['NINGUNA', 'COMFIAR']);
    };

    // =========================
    // 🔥 CALCULOS
    // =========================
    $eps = 0;
    $pension = 0;
    $caja = 0;
    $arl = 0;

    if ($tieneEntidad($afiliacion->eps)) {
        $eps = round(($ibc * 0.04 / 30) * $dias);
    }

    if ($tieneEntidad($afiliacion->pension)) {
        $pension = round(($ibc * 0.16 / 30) * $dias);
    }

    if ($tieneCajaValida($afiliacion->caja)) {
        $caja = round(($ibc * 0.04 / 30) * $dias);
    }

    // 🔥 ARL
    $arlObj = null;

    if ($afiliacion->nivel_arl) {

        $arlObj = \App\Models\Arl::where('nivel', $afiliacion->nivel_arl)->first();

        if ($arlObj && $arlObj->porcentaje > 0) {
            $arl = round(($ibc * ($arlObj->porcentaje / 100) / 30) * $dias);
        }
    }

    // 🔥 ADMIN
    $admin = $parametro->administracion ?? 0;

    // 🔥 SERVICIOS
    $servicios = AfiliadoServicio::with('servicio')
        ->where('afiliado_id', $afiliadoId)
        ->where('estado', 1)
        ->get();

    $serviciosTotal = $servicios->sum('valor');

    // 🔥 DETALLES
    $detalles = [];

    if ($eps > 0) {
        $detalles[] = [
            'concepto' => 'EPS - ' . $afiliacion->eps->nombre,
            'valor' => $eps
        ];
    }

    if ($pension > 0) {
        $detalles[] = [
            'concepto' => 'Pensión - ' . $afiliacion->pension->nombre,
            'valor' => $pension
        ];
    }

    if ($caja > 0 && $tieneCajaValida($afiliacion->caja)) {
        $detalles[] = [
            'concepto' => 'Caja - ' . $afiliacion->caja->nombre,
            'valor' => $caja
        ];
    }

    if ($arl > 0 && $arlObj) {
        $detalles[] = [
            'concepto' => 'ARL - ' . $arlObj->nombre . ' Nivel ' . $arlObj->nivel,
            'valor' => $arl
        ];
    }

    if ($admin > 0) {
        $detalles[] = [
            'concepto' => 'Administración',
            'valor' => $admin
        ];
    }

    foreach ($servicios as $s) {
        $detalles[] = [
            'concepto' => $s->servicio->nombre ?? 'Servicio',
            'valor' => $s->valor
        ];
    }

    // 🔥 TOTAL
    $total = $eps + $pension + $caja + $arl + $admin + $serviciosTotal;

    return [
        'dias' => $dias,
        'ibc' => $ibc,
        'eps' => $eps,
        'pension' => $pension,
        'caja' => $caja,
        'arl' => $arl,
        'admin' => $admin,
        'servicios' => $serviciosTotal,
        'detalles' => $detalles,
        'total' => ceil($total / 100) * 100,
        'fecha_afiliacion' => $afiliacion->fecha_afiliacion
    ];
}
public function preview(Request $request)
{
    $afiliacion = Afiliacion::where('afiliado_id', $request->afiliado_id)
        ->latest()
        ->first();

    $data = $this->calcularRecibo(
        $request->afiliado_id,
        $request->fecha
    );

    // Agregar fecha_afiliacion al response
    $data['fecha_afiliacion'] = $afiliacion?->fecha_afiliacion;

    return response()->json($data);
}

public function usuariosSinRecibo()
{
    $empresaId = session('empresa_id');

    // 🔥 PERIODO ACTUAL (MES ANTERIOR)
    $periodo = now()->subMonth()->format('Y-m');

    // 🔥 AFILIADOS ACTIVOS
    $afiliados = Afiliado::where('empresa_id', $empresaId)
        ->where('estado', 1)
        ->get();

    // 🔥 RECIBOS DEL PERIODO
    $recibos = Recibo::where('empresa_id', $empresaId)
        ->whereRaw("DATE_FORMAT(DATE_SUB(fecha, INTERVAL 1 MONTH),'%Y-%m') = ?", [$periodo])
        ->pluck('afiliado_id');

    // 🔥 FILTRAR LOS QUE NO TIENEN RECIBO
    $sinRecibo = $afiliados->whereNotIn('id', $recibos);

    return view('modules.recibos.sin_recibo', [
        'afiliados' => $sinRecibo
    ]);
}

public function generarUno(Request $request, $afiliadoId)
{
    $empresaId = session('empresa_id');
    $fecha = now();

    $data = $this->calcularRecibo($afiliadoId, $fecha);

    if(!$data){
        return back()->with('error','No se pudo generar');
    }

    $periodo = now()->subMonth()->format('Y-m');

    $existe = Recibo::where('empresa_id', $empresaId)
        ->where('afiliado_id', $afiliadoId)
        ->whereRaw("DATE_FORMAT(DATE_SUB(fecha, INTERVAL 1 MONTH),'%Y-%m') = ?", [$periodo])
        ->exists();

    if($existe){
        return back()->with('error','Ya existe');
    }

    DB::beginTransaction();

    try {

        $numero = Recibo::lockForUpdate()
            ->where('empresa_id', $empresaId)
            ->max('numero') + 1;

        $recibo = Recibo::create([
            'empresa_id' => $empresaId,
            'numero' => $numero,
            'fecha' => $fecha,
            'afiliado_id' => $afiliadoId,
            'dias_liquidar' => $data['dias'],
            'ibc' => $data['ibc'],
            'valor_eps' => $data['eps'],
            'valor_arl' => $data['arl'],
            'valor_pension' => $data['pension'],
            'valor_caja' => $data['caja'],
            'valor_admon' => $data['admin'],
            'valor_servicios' => $data['servicios'],
            'total' => $data['total'],
        ]);

        foreach($data['detalles'] as $d){
            ReciboDetalle::create([
                'empresa_id' => $empresaId,
                'recibo_id' => $recibo->id,
                'concepto' => $d['concepto'],
                'valor' => $d['valor']
            ]);
        }

        DB::commit();

    } catch (\Exception $e){
        DB::rollBack();
        return back()->with('error','Error al generar');
    }

    return back()->with('success','Recibo generado');
}
public function generarTodos()
{
    $empresaId = session('empresa_id');
    $fecha = now();
    $periodo = now()->subMonth()->format('Y-m');

    $afiliados = Afiliado::where('empresa_id', $empresaId)
        ->where('estado', 1)
        ->get();

    DB::beginTransaction();

    try {

        foreach($afiliados as $a){

            $existe = Recibo::where('empresa_id', $empresaId)
                ->where('afiliado_id', $a->id)
                ->whereRaw("DATE_FORMAT(DATE_SUB(fecha, INTERVAL 1 MONTH),'%Y-%m') = ?", [$periodo])
                ->exists();

            if($existe) continue;

            $data = $this->calcularRecibo($a->id, $fecha);

            if(!$data) continue;

            $numero = Recibo::lockForUpdate()
                ->where('empresa_id', $empresaId)
                ->max('numero') + 1;

            $recibo = Recibo::create([
                'empresa_id' => $empresaId,
                'numero' => $numero,
                'fecha' => $fecha,
                'afiliado_id' => $a->id,
                'dias_liquidar' => $data['dias'],
                'ibc' => $data['ibc'],
                'valor_eps' => $data['eps'],
                'valor_arl' => $data['arl'],
                'valor_pension' => $data['pension'],
                'valor_caja' => $data['caja'],
                'valor_admon' => $data['admin'],
                'valor_servicios' => $data['servicios'],
                'total' => $data['total'],
            ]);

            foreach($data['detalles'] as $d){
                ReciboDetalle::create([
                    'empresa_id' => $empresaId,
                    'recibo_id' => $recibo->id,
                    'concepto' => $d['concepto'],
                    'valor' => $d['valor']
                ]);
            }
        }

        DB::commit();

    } catch (\Exception $e){
        DB::rollBack();
        return back()->with('error','Error masivo');
    }

    return back()->with('success','Recibos generados');
}
public function cerrarPeriodo()
{
    $empresaId = session('empresa_id');

    // 🔥 PERIODO (MES ANTERIOR)
    $periodo = now()->subMonth()->format('Y-m');

    // 🔥 AFILIADOS ACTIVOS
    $afiliados = Afiliado::where('empresa_id', $empresaId)
        ->where('estado', 1)
        ->pluck('id');

    // 🔥 RECIBOS DEL PERIODO
    $recibos = Recibo::where('empresa_id', $empresaId)
        ->whereRaw("DATE_FORMAT(DATE_SUB(fecha, INTERVAL 1 MONTH),'%Y-%m') = ?", [$periodo])
        ->pluck('afiliado_id');

    // 🔥 BUSCAR FALTANTES
    $faltantes = $afiliados->diff($recibos);

    // 🚨 SI HAY PENDIENTES → BLOQUEAR
    if ($faltantes->count() > 0) {

        $afiliadosPendientes = Afiliado::whereIn('id', $faltantes)->get();

        return view('modules.recibos.pendientes', [
            'afiliados' => $afiliadosPendientes
        ]);
    }

    // ✅ SI TODO OK → CERRAR PERIODO
    return redirect()->route('recibos.index')
        ->with('success', 'Periodo cerrado correctamente ✔');
}
public function exportarVigentes()
{
    return Excel::download(
        new AfiliadosVigentesExport,
        'afiliados_vigentes.xlsx'
    );
}

public function activosSiguientePeriodo()
{
    $empresaId = session('empresa_id');

    // 🔥 MES ACTUAL
    $periodo = now()->format('Y-m');

    // =========================
    // 🔥 CONDICIÓN REUTILIZABLE (SIN RETIRO)
    // =========================
    $sinRetiro = function ($q) {
        $q->whereNull('novedad')
          ->orWhere('novedad', '!=', 'Retiro');
    };

    // =========================
    // 🔥 1. RECIBOS DEL MES SIN RETIRO
    // =========================
    $recibosActivos = Recibo::where('empresa_id', $empresaId)
        ->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$periodo])
        ->where($sinRetiro)
        ->pluck('afiliado_id');

    // =========================
    // 🔥 2. AFILIADOS QUE INGRESARON ESTE MES
    // =========================
    $afiliadosNuevos = Afiliacion::where('empresa_id', $empresaId)
        ->whereRaw("DATE_FORMAT(fecha_afiliacion, '%Y-%m') = ?", [$periodo])
        ->pluck('afiliado_id');

    // =========================
    // 🔥 UNIR IDS
    // =========================
    $ids = $recibosActivos
        ->merge($afiliadosNuevos)
        ->unique();

    // =========================
    // 🔥 TRAER AFILIADOS
    // =========================
    $afiliados = Afiliado::whereIn('id', $ids)->get();

    return view('modules.recibos.activos_siguiente', compact('afiliados'));
}
public function crearLote(Request $request)
{

    $empresaId = session('empresa_id');

    $recibos = Recibo::with('afiliado.afiliacion.caja')
        ->where('empresa_id', $empresaId)
        ->whereNull('export_batch_id')
        ->get()
        ->filter(function($r){
            return $r->afiliado && $r->afiliado->afiliacion;
        });

    if($recibos->isEmpty()){
        return back()->with('error','No hay recibos válidos para exportar');
    }

    // =========================
    // 🔥 SEPARAR POR CAJA
    // =========================
    $comfiar = [];
    $otros = [];

    foreach ($recibos as $r) {

        $caja = strtoupper(trim($r->afiliado->afiliacion->caja->nombre ?? ''));

        if ($caja === 'COMFIAR') {
            $comfiar[] = $r;
        } else {
            $otros[] = $r;
        }
    }

    DB::beginTransaction();

    try {

        $batch = \App\Models\ExportBatch::create([
            'empresa_id' => $empresaId,
            'codigo' => 'PILA-'.now()->format('YmHis'),
            'periodo' => now()->format('Y-m'),
            'recibos_count' => $recibos->count(),
            'total' => $recibos->sum('total')
        ]);

        foreach ($recibos as $r) {
            $r->update(['export_batch_id' => $batch->id]);
        }

        // =========================
        // 🔥 GENERAR ARCHIVOS
        // =========================
        $files = [];

        if(count($comfiar)){
            $empresa = \App\Models\Empresa::find($empresaId);

$contenido = (new \App\Exports\PilaTxtExport(collect($comfiar), $empresa))->generar();
            $ruta = storage_path("app/pila_comfiar_{$batch->id}.txt");
            file_put_contents($ruta, $contenido);
            $files[] = $ruta;
        }

        if(count($otros)){
            $empresa = \App\Models\Empresa::find($empresaId);

$contenido = (new \App\Exports\PilaTxtExport(collect($otros), $empresa))->generar();
            $ruta = storage_path("app/pila_otros_{$batch->id}.txt");
            file_put_contents($ruta, $contenido);
            $files[] = $ruta;
        }

        DB::commit();

        // =========================
        // 🔥 DESCARGA ZIP
        // =========================
        $zip = new \ZipArchive();


$zipPath = storage_path("app/pila_{$batch->id}.zip");

$result = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

if ($result !== true) {
    throw new \Exception('No se pudo crear el ZIP');
}

foreach ($files as $file) {

    if (file_exists($file)) {

        $zip->addFile(
            $file,
            basename($file)
        );
    }
}

$zip->close();

// 🔥 VALIDAR ZIP
if (!file_exists($zipPath)) {
    throw new \Exception('El ZIP no fue generado');
}

return response()->download($zipPath)->deleteFileAfterSend(true);

    } catch (\Exception $e){

        DB::rollBack();

        return back()->with('error',$e->getMessage());
    }
}


public function exportarPilaExcel()
{
    $empresaId = session('empresa_id');

    $empresa = \App\Models\Empresa::find($empresaId);

    if (!$empresa) {
        return back()->with('error', 'Empresa no encontrada');
    }

    // =========================
    // 🔥 RECIBOS PENDIENTES
    // =========================
    $recibos = \App\Models\Recibo::with([
        'afiliado.documento',
        'afiliado.subtipoCotizante',
        'afiliado.afiliaciones.eps',
        'afiliado.afiliaciones.arl',
        'afiliado.afiliaciones.pension',
        'afiliado.afiliaciones.caja',
    ])
    ->where('empresa_id', $empresaId)
    ->whereNull('export_batch_id')
    ->get();

    if ($recibos->isEmpty()) {
        return back()->with('error', 'No hay datos para exportar');
    }

    // =========================
    // 🔥 SEPARAR POR CAJA
    // =========================
    $comfiar = collect();
    $otros = collect();

    foreach ($recibos as $r) {

        $afiliacion = $r->afiliado
            ->afiliaciones
            ->where('estado', 1)
            ->first();

        $caja = strtoupper(
            trim($afiliacion?->caja?->nombre ?? '')
        );

        if ($caja === 'COMFIAR') {
            $comfiar->push($r);
        } else {
            $otros->push($r);
        }
    }

    DB::beginTransaction();

    try {

        // =========================
        // 🔥 CREAR LOTE
        // =========================
        $batch = \App\Models\ExportBatch::create([
            'empresa_id' => $empresaId,
            'codigo' => 'PILA-' . now()->format('YmdHis'),
            'periodo' => now()->format('Y-m'),
            'recibos_count' => $recibos->count(),
            'total' => $recibos->sum('total')
        ]);

        // =========================
        // 🔥 MARCAR EXPORTADOS
        // =========================
        foreach ($recibos as $r) {

            $r->update([
                'export_batch_id' => $batch->id
            ]);
        }

        // =========================
        // 🔥 GENERAR ARCHIVOS
        // =========================
        $files = [];

        // 🔹 COMFIAR
        if ($comfiar->count() > 0) {

            $rutaComfiar = storage_path(
                "app/private/pila_comfiar_{$batch->id}.xlsx"
            );

            (new \App\Exports\PilaRealExport(
                $empresaId,
                now()->format('Y-m'),
                $comfiar
            ))->exportar($rutaComfiar);

            $files[] = $rutaComfiar;
        }

        // 🔹 OTRAS CAJAS
        if ($otros->count() > 0) {

            $rutaOtros = storage_path(
                "app/private/pila_otros_{$batch->id}.xlsx"
            );

            (new \App\Exports\PilaRealExport(
                $empresaId,
                now()->format('Y-m'),
                $otros
            ))->exportar($rutaOtros);

            $files[] = $rutaOtros;
        }

        DB::commit();

        // =========================
        // 🔥 ZIP FINAL
        // =========================
        $zipPath = storage_path(
            "app/private/pila_excel_{$batch->id}.zip"
        );

        $zip = new \ZipArchive();

        $result = $zip->open(
            $zipPath,
            \ZipArchive::CREATE | \ZipArchive::OVERWRITE
        );

        if ($result !== true) {
            throw new \Exception('No se pudo crear ZIP');
        }

        foreach ($files as $file) {

            if (!file_exists($file)) {

                throw new \Exception(
                    'Archivo inexistente: ' . $file
                );
            }

            $zip->addFile(
                $file,
                basename($file)
            );
        }

        $zip->close();

        if (!file_exists($zipPath)) {
            throw new \Exception('ZIP no generado');
        }

        return response()->download($zipPath)
            ->deleteFileAfterSend(true);

    } catch (\Exception $e) {

        DB::rollBack();

        return back()->with(
            'error',
            $e->getMessage()
        );
    }
}

public function guardar($ruta)
{
    // =====================================================
    // CARGAR PLANTILLA
    // =====================================================

    $spreadsheet = IOFactory::load(
        storage_path('app/templates/Libro1.xlsx')
    );

    $sheet = $spreadsheet->getSheetByName('Liquidaciones');

    // TODO el código original exactamente igual
    // desde EMPRESA hasta el foreach

    // 🔥 IMPORTANTE:
    // NO cambies nada de las columnas
    // ni fórmulas del operador PILA

    // =====================================================
    // GUARDAR
    // =====================================================

    $writer = new Xlsx($spreadsheet);

    $writer->save($ruta);

    return $ruta;
}
}