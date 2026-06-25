<?php

namespace App\Domains\Payroll\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Remision;
use App\Models\Afiliado;
use App\Models\Afiliacion;
use App\Models\RemisionDetalle;
use App\Models\AfiliadoServicio;
use App\Models\ParametroAnual;
use App\Models\PeriodoAfiliado;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RemisionController extends Controller
{
    public function index()
    {
        $titulo = "Remisiones";

        $remisiones = Remision::with(['afiliado'])
            ->orderBy('fecha','desc')
            ->paginate(15);

        return view('modules.remisiones.index', compact('titulo','remisiones'));
    }

    public function create()
    {
        $titulo = "Crear RemisiÃ³n";

        return view('modules.remisiones.create', compact('titulo'));
    }

    public function store(Request $request)
{
    $request->validate([
        'afiliado_id' => 'required|exists:afiliados,id',
        'fecha' => 'required|date'
    ]);

    $empresaId = session('empresa_id');

    $data = $this->calcularRemision($request->afiliado_id, $request->fecha);

    if(!$data){
        return back()->with('error','No se pudo calcular la remisiÃ³n');
    }

    $periodoFecha = Carbon::parse($request->fecha)->subMonth();
    $periodo = $periodoFecha->format('Y-m');

    DB::beginTransaction();

    try {

        $periodoAfiliado = PeriodoAfiliado::firstOrCreate([
            'empresa_id' => $empresaId,
            'afiliado_id' => $request->afiliado_id,
            'periodo' => $periodo
        ], [
            'estado' => 'Activo'
        ]);

        $existe = Remision::where('empresa_id', $empresaId)
            ->where('afiliado_id', $request->afiliado_id)
            ->whereRaw("DATE_FORMAT(DATE_SUB(fecha, INTERVAL 1 MONTH),'%Y-%m') = ?", [$periodo])
            ->exists();

        if($existe){
            return back()->with('error','Ya existe remisiÃ³n para este perÃ­odo');
        }

        $numero = Remision::lockForUpdate()
            ->where('empresa_id', $empresaId)
            ->max('numero') + 1;

        // =========================
        // ðŸ”¥ VALORES MANUALES
        // =========================
        $mensajeria = floatval($request->mensajeria ?? 0);
        $intereses = floatval($request->intereses ?? 0);

        // =========================
        // ðŸ”¥ TOTAL INICIAL
        // =========================
        $totalFinal = floatval($data['total']) + $mensajeria + $intereses;

        // =========================
        // ðŸ”¥ SUMAR CARGOS DINÃMICOS
        // =========================
        if ($request->cargos) {
            foreach ($request->cargos as $cargo) {

                $valor = floatval($cargo['valor'] ?? 0);

                if ($valor > 0) {
                    $totalFinal += $valor;
                }
            }
        }

        // =========================
        // ðŸ”¥ CREAR REMISIÃ“N
        // =========================
        $remision = Remision::create([
            'empresa_id' => $empresaId,
            'numero' => $numero,
            'fecha' => $request->fecha,
            'afiliado_id' => $request->afiliado_id,
            'periodo_afiliado_id' => $periodoAfiliado->id,
            'dias_liquidar' => $data['dias'],
            'mensajeria' => $mensajeria,
            'intereses' => $intereses,
            'total' => $totalFinal
        ]);

        // =========================
        // ðŸ”¥ DETALLES BASE
        // =========================
        foreach($data['detalles'] as $d){
            RemisionDetalle::create([
                'empresa_id' => $empresaId,
                'remision_id' => $remision->id,
                'concepto' => $d['concepto'],
                'valor' => $d['valor']
            ]);
        }

        // =========================
        // ðŸ”¥ MENSAJERÃA
        // =========================
        if ($mensajeria > 0) {
            RemisionDetalle::create([
                'empresa_id' => $empresaId,
                'remision_id' => $remision->id,
                'concepto' => 'MensajerÃ­a',
                'valor' => $mensajeria
            ]);
        }

        // =========================
        // ðŸ”¥ INTERESES
        // =========================
        if ($intereses > 0) {
            RemisionDetalle::create([
                'empresa_id' => $empresaId,
                'remision_id' => $remision->id,
                'concepto' => 'Intereses',
                'valor' => $intereses
            ]);
        }

        // =========================
        // ðŸ”¥ CARGOS DINÃMICOS
        // =========================
        if ($request->cargos) {
            foreach ($request->cargos as $cargo) {

                $valor = floatval($cargo['valor'] ?? 0);

                if ($valor > 0) {
                    RemisionDetalle::create([
                        'empresa_id' => $empresaId,
                        'remision_id' => $remision->id,
                        'concepto' => $cargo['concepto'] ?? 'Cargo',
                        'valor' => $valor
                    ]);
                }
            }
        }

        DB::commit();

    } catch (\Exception $e){

        DB::rollBack();

        return back()->with('error','Error al generar remisiÃ³n');
    }

    return redirect()->route('remisiones.index')
        ->with('success','RemisiÃ³n generada correctamente');
}

    public function preview(Request $request)
    {
        $data = $this->calcularRemision(
            $request->afiliado_id,
            $request->fecha
        );

        return response()->json($data);
    }

   public function edit($id)
{
    $titulo = "Editar RemisiÃ³n";

    $remision = Remision::with('detalles')->findOrFail($id);

    return view('modules.remisiones.edit', compact('titulo','remision'));
}

public function update(Request $request, $id)
{
    $remision = Remision::findOrFail($id);

    DB::beginTransaction();

    try {

        // ðŸ”¥ recalcular base SIEMPRE
        $data = $this->calcularRemision(
            $remision->afiliado_id,
            $remision->fecha
        );

        // ðŸ”¥ valores manuales
        $mensajeria = floatval($request->mensajeria ?? 0);
        $intereses  = floatval($request->intereses ?? 0);

        $totalFinal = $data['total'] + $mensajeria + $intereses;

        // ðŸ”¥ sumar dinÃ¡micos
        if ($request->cargos) {
            foreach ($request->cargos as $cargo) {
                $valor = floatval($cargo['valor'] ?? 0);
                if ($valor > 0) {
                    $totalFinal += $valor;
                }
            }
        }

        // ðŸ”¥ actualizar remisiÃ³n
        $remision->update([
            'mensajeria' => $mensajeria,
            'intereses'  => $intereses,
            'total'      => $totalFinal
        ]);

        // ðŸ”¥ LIMPIAR TODO (CLAVE)
        $remision->detalles()->delete();

        // =========================
        // ðŸ”¥ BASE LIMPIA
        // =========================
        foreach($data['detalles'] as $d){
            RemisionDetalle::create([
                'empresa_id' => session('empresa_id'),
                'remision_id' => $remision->id,
                'concepto' => $d['concepto'],
                'valor' => $d['valor']
            ]);
        }

        // =========================
        // ðŸ”¥ MANUALES
        // =========================
        if ($mensajeria > 0) {
            RemisionDetalle::create([
                'empresa_id' => session('empresa_id'),
                'remision_id' => $remision->id,
                'concepto' => 'MensajerÃ­a',
                'valor' => $mensajeria
            ]);
        }

        if ($intereses > 0) {
            RemisionDetalle::create([
                'empresa_id' => session('empresa_id'),
                'remision_id' => $remision->id,
                'concepto' => 'Intereses',
                'valor' => $intereses
            ]);
        }

        // =========================
        // ðŸ”¥ DINÃMICOS
        // =========================
        if ($request->cargos) {
            foreach ($request->cargos as $cargo) {

                $valor = floatval($cargo['valor'] ?? 0);

                if ($valor > 0) {
                    RemisionDetalle::create([
                        'empresa_id' => session('empresa_id'),
                        'remision_id' => $remision->id,
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

    return redirect()->route('remisiones.index')
        ->with('success','RemisiÃ³n actualizada correctamente');
}

public function destroy($id)
{
    $remision = Remision::findOrFail($id);

    DB::beginTransaction();

    try {

        // ðŸ”¥ eliminar detalles primero
        $remision->detalles()->delete();

        // ðŸ”¥ eliminar remisiÃ³n
        $remision->delete();

        DB::commit();

    } catch (\Exception $e){

        DB::rollBack();

        return back()->with('error','Error al eliminar la remisiÃ³n');
    }

    return redirect()->route('remisiones.index')
        ->with('success','RemisiÃ³n eliminada correctamente');
}

    public function imprimir($id)
    {
        $remision = Remision::with(['afiliado', 'detalles', 'empresa'])->findOrFail($id);

        return view('modules.remisiones.imprimir', compact('remision'));
    }

    public function buscarAfiliados(Request $request)
{
    $buscar = $request->q;

    if (!$buscar) {
        return response()->json([]);
    }

    $afiliados = Afiliado::where('empresa_id', session('empresa_id'))
        ->where('estado', 1)
        ->where(function ($q) use ($buscar) {

            // ðŸ”¥ PRIORIDAD: documento exacto
            $q->where('numero_documento', $buscar)

              // ðŸ”¥ BÃºsqueda flexible
            ->orWhere('numero_documento', 'like', "%{$buscar}%")
            ->orWhere('primer_nombre', 'like', "%{$buscar}%")
            ->orWhere('primer_apellido', 'like', "%{$buscar}%");
        })
        ->limit(10)
        ->get();

    return response()->json($afiliados);
}

    private function calcularRemision($afiliadoId, $fecha)
{
    $afiliado = Afiliado::find($afiliadoId);
    if (!$afiliado) return null;

    $afiliacion = Afiliacion::where('afiliado_id', $afiliadoId)
        ->where('estado', 1)
        ->with(['eps', 'pension', 'caja'])
        ->first();

    if (!$afiliacion) return null;

    // =========================
    // ðŸ”¥ FECHAS BASE (CORREGIDO)
    // =========================
    $fechaRemision = Carbon::parse($fecha);

    // ðŸ”¥ CLAVE: evita errores con 31 â†’ febrero
    $periodo = $fechaRemision->copy()->subMonthNoOverflow();

    $inicioPeriodo = $periodo->copy()->startOfMonth();

    // ðŸ”¥ SIEMPRE 30 DÃAS (REGLA DE NEGOCIO)
    $finPeriodo = $periodo->copy()->startOfMonth()->addDays(29);

    $fechaIngreso = Carbon::parse($afiliacion->fecha_afiliacion)->startOfDay();

    // =========================
    // âŒ NO LIQUIDAR
    // =========================

    // Si ingresÃ³ en el mismo mes de la remisiÃ³n
    if (
        $fechaIngreso->year == $fechaRemision->year &&
        $fechaIngreso->month == $fechaRemision->month
    ) {
        return null;
    }

    // Si ingresÃ³ despuÃ©s del periodo
    if ($fechaIngreso->gt($finPeriodo)) {
        return null;
    }

    // =========================
    // ðŸ”¥ DÃAS A LIQUIDAR (CORREGIDO)
    // =========================
    if (
        $fechaIngreso->year == $periodo->year &&
        $fechaIngreso->month == $periodo->month
    ) {
        // ðŸ”¥ PROTECCIÃ“N: evita dÃ­a 31
        $diaIngreso = min($fechaIngreso->day, 30);

        $dias = 30 - ($diaIngreso - 1);
    } else {
        $dias = 30;
    }

    if ($dias <= 0) return null;

    // =========================
    // ðŸ”¥ AÃ‘O PARAMETROS
    // =========================
    $anio = $fechaRemision->month == 1
        ? $fechaRemision->year - 1
        : $fechaRemision->year;

    $parametro = ParametroAnual::where('empresa_id', session('empresa_id'))
        ->where('anio', $anio)
        ->first();

    if (!$parametro) return null;

    // =========================
    // ðŸ”¥ IBC
    // =========================
    $ibc = $afiliacion->tipo_ibc === 'FIJO'
        ? $afiliacion->ibc
        : $parametro->salario_minimo;

    // =========================
    // ðŸ”¥ INICIALIZAR
    // =========================
    $eps = 0;
    $pension = 0;
    $caja = 0;
    $arl = 0;

    $tieneEntidad = function ($entidad) {
        return $entidad && strtoupper(trim($entidad->nombre)) !== 'NINGUNA';
    };

    // =========================
    // âœ… EPS
    // =========================
    if ($tieneEntidad($afiliacion->eps)) {
        $eps = round(($ibc * 0.04 / 30) * $dias);
    }

    // =========================
    // âœ… PENSIÃ“N
    // =========================
    if ($tieneEntidad($afiliacion->pension)) {
        $pension = round(($ibc * 0.16 / 30) * $dias);
    }

    // =========================
    // âš ï¸ CAJA
    // =========================
    if (
        $tieneEntidad($afiliacion->caja) &&
        strtoupper(trim($afiliacion->caja->nombre)) !== 'COMFIAR'
    ) {
        $caja = round(($ibc * 0.04 / 30) * $dias);
    }

    // =========================
    // ðŸ”¥ ARL
    // =========================
    $arlObj = null;

    if ($afiliacion->nivel_arl) {
        $arlObj = \App\Models\Arl::where('nivel', $afiliacion->nivel_arl)->first();

        if ($arlObj && $arlObj->porcentaje > 0) {
            $arl = round(($ibc * ($arlObj->porcentaje / 100) / 30) * $dias);
        }
    }

    // =========================
    // ðŸ”¥ ADMIN
    // =========================
    $administracion = $parametro->administracion ?? 0;

    // =========================
    // ðŸ”¥ SERVICIOS
    // =========================
    $servicios = AfiliadoServicio::with('servicio')
        ->where('afiliado_id', $afiliadoId)
        ->where('estado', 1)
        ->get();

    $serviciosTotal = $servicios->sum('valor');

    // =========================
    // ðŸ”¥ DETALLES
    // =========================
    $detalles = [];

    if ($eps > 0) {
        $detalles[] = [
            'concepto' => 'EPS - ' . $afiliacion->eps->nombre,
            'valor' => $eps
        ];
    }

    if ($pension > 0) {
        $detalles[] = [
            'concepto' => 'PensiÃ³n - ' . $afiliacion->pension->nombre,
            'valor' => $pension
        ];
    }

    if ($caja > 0) {
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

    if ($administracion > 0) {
        $detalles[] = [
            'concepto' => 'AdministraciÃ³n',
            'valor' => $administracion
        ];
    }

    foreach ($servicios as $s) {
        $detalles[] = [
            'concepto' => $s->servicio->nombre ?? 'Servicio',
            'valor' => $s->valor
        ];
    }

    // =========================
    // ðŸ”¥ TOTAL
    // =========================
    $total = $eps + $pension + $caja + $arl + $administracion + $serviciosTotal;

    return [
        'dias' => $dias,
        'detalles' => $detalles,
        'total' => $this->redondear100($total),
        'fecha_afiliacion' => $afiliacion->fecha_afiliacion
    ];
}

    private function redondear100($valor)
    {
        return ceil($valor / 100) * 100;
    }
}

