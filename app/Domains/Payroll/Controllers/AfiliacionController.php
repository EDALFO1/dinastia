<?php

namespace App\Domains\Payroll\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Afiliacion;
use App\Models\Afiliado;
use App\Models\Eps;
use App\Models\Arl;
use App\Models\Pension;
use App\Models\Caja;
use App\Models\ParametroAnual;

use App\Exports\AfiliacionesTemplateExport;
use App\Imports\AfiliacionesImport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AfiliacionController extends Controller
{
    public function index(Request $request)
    {
        $titulo = "Afiliaciones";

        $query = Afiliacion::with([
            'afiliado',
            'eps',
            'arl',
            'pension',
            'caja'
        ]);
        

        // ðŸ”Ž BUSCAR
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->whereHas('afiliado', function ($q) use ($buscar) {
                $q->where('primer_nombre', 'like', "%$buscar%")
                  ->orWhere('primer_apellido', 'like', "%$buscar%")
                  ->orWhere('numero_documento', 'like', "%$buscar%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('eps')) {
            $query->where('eps_id', $request->eps);
        }

        $afiliaciones = $query
            ->orderBy('fecha_afiliacion', 'desc')
            ->paginate(15);

        $eps = Eps::orderBy('nombre')->get();
        $afiliados = Afiliado::orderBy('primer_apellido')->get();

        return view('modules.afiliaciones.index', compact(
            'titulo',
            'afiliaciones',
            'eps',
            'afiliados'
        ));
    }

    public function create()
{
    $titulo = "Crear AfiliaciÃ³n";

    $afiliados = Afiliado::orderBy('primer_apellido')->get();

    if ($afiliados->isEmpty()) {
        return redirect()->route('afiliaciones.index')
            ->with('error', 'No hay afiliados registrados.');
    }

    $eps = Eps::orderBy('nombre')->get();

    // âœ… CORREGIDO
    $arls = Arl::orderBy('nombre')
        ->orderBy('nivel')
        ->get();

    $pensions = Pension::orderBy('nombre')->get();
    $cajas = Caja::orderBy('nombre')->get();

    $parametros = ParametroAnual::where('empresa_id', session('empresa_id'))
        ->pluck('salario_minimo', 'anio');

    return view('modules.afiliaciones.create', compact(
        'titulo',
        'afiliados',
        'eps',
        'arls',
        'pensions',
        'cajas',
        'parametros'
    ));
}

    public function store(Request $request)
{
    $request->validate([

        'afiliado_id' => 'required|exists:afiliados,id',

        'eps_id' => 'required|exists:eps,id',

        // âœ… CORREGIDO
        'arl_id' => 'required|exists:arls,id',

        'pension_id' => 'required|exists:pensions,id',
        'caja_id' => 'required|exists:cajas,id',

        'fecha_afiliacion' => 'required|date',
        'fecha_retiro' => 'nullable|date|after_or_equal:fecha_afiliacion',

        'tipo_ibc' => 'required',

        'ibc' => 'required_if:tipo_ibc,FIJO|nullable|numeric|min:0'
    ]);

    $afiliado = Afiliado::where('id', $request->afiliado_id)
        ->where('empresa_id', session('empresa_id'))
        ->firstOrFail();

    if ((int)$afiliado->estado !== 1) {
        return back()->with('error', 'No puedes crear una afiliaciÃ³n porque el afiliado estÃ¡ inactivo.');
    }

    $existe = Afiliacion::where('afiliado_id', $request->afiliado_id)
        ->where('estado', 1)
        ->exists();

    if ($existe) {
        return back()->with('error', 'El afiliado ya tiene una afiliaciÃ³n activa.');
    }

    $anio = Carbon::parse($request->fecha_afiliacion)->year;

    $parametro = ParametroAnual::where('anio', $anio)->first();

    if (!$parametro) {
        return back()->with('error', 'No existen parÃ¡metros para el aÃ±o ' . $anio);
    }

    $ibc = $request->tipo_ibc == 'SMMLV'
        ? $parametro->salario_minimo
        : $request->ibc;

    // âœ… OBTENER NIVEL DESDE EL ARL
    $arl = Arl::findOrFail($request->arl_id);

    $data = $request->except('empresa_id');

    $data['ibc'] = $ibc;

    // âœ… SINCRONIZAR NIVEL
    $data['nivel_arl'] = $arl->nivel;

    $data['estado'] = 1;

    Afiliacion::create($data);

    return redirect()->route('afiliaciones.index')
        ->with('success', 'AfiliaciÃ³n creada correctamente');
}

   public function edit(Afiliacion $afiliacion)
{
    $titulo = "Editar AfiliaciÃ³n";

    $afiliados = Afiliado::orderBy('primer_apellido')->get();

    $eps = Eps::orderBy('nombre')->get();

    // âœ… CORREGIDO
    $arls = Arl::orderBy('nombre')
        ->orderBy('nivel')
        ->get();

    $pensions = Pension::orderBy('nombre')->get();

    $cajas = Caja::orderBy('nombre')->get();

    return view('modules.afiliaciones.edit', compact(
        'titulo',
        'afiliacion',
        'afiliados',
        'eps',
        'arls',
        'pensions',
        'cajas'
    ));
}

    public function update(Request $request, Afiliacion $afiliacion)
{
    $request->validate([

        'afiliado_id' => 'required|exists:afiliados,id',

        'eps_id' => 'required|exists:eps,id',

        // âœ… CORREGIDO
        'arl_id' => 'required|exists:arls,id',

        'pension_id' => 'required|exists:pensions,id',
        'caja_id' => 'required|exists:cajas,id',

        'fecha_afiliacion' => 'required|date',

        'fecha_retiro' => 'nullable|date|after_or_equal:fecha_afiliacion',

        'tipo_ibc' => 'required',

        'ibc' => 'required_if:tipo_ibc,FIJO|nullable|numeric|min:0'
    ]);

    $afiliado = Afiliado::where('id', $request->afiliado_id)
        ->where('empresa_id', session('empresa_id'))
        ->firstOrFail();

    $anio = Carbon::parse($request->fecha_afiliacion)->year;

    $parametro = ParametroAnual::where('anio', $anio)->first();

    if (!$parametro) {
        return back()->with('error', 'No existen parÃ¡metros para el aÃ±o ' . $anio);
    }

    $ibc = $request->tipo_ibc == 'SMMLV'
        ? $parametro->salario_minimo
        : $request->ibc;

    // âœ… OBTENER ARL
    $arl = Arl::findOrFail($request->arl_id);

    $data = $request->except('empresa_id');

    $data['ibc'] = $ibc;

    // âœ… SINCRONIZAR NIVEL
    $data['nivel_arl'] = $arl->nivel;

    $afiliacion->update($data);

    return redirect()->route('afiliaciones.index')
        ->with('success', 'AfiliaciÃ³n actualizada');
}

    public function destroy(Afiliacion $afiliacion)
    {
        $afiliacion->delete();

        return redirect()->route('afiliaciones.index')
            ->with('success', 'AfiliaciÃ³n eliminada');
    }
    public function descargarPlantilla()
    {
        return Excel::download(new AfiliacionesTemplateExport, 'plantilla_afiliaciones.xlsx');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv'
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.mimes'    => 'El archivo debe ser Excel (.xlsx, .xls) o CSV.',
        ]);

        try {
            $import = new AfiliacionesImport(session('empresa_id'));

            Excel::import($import, $request->file('archivo'));

            return redirect()->back()
                ->with('success', 'ImportaciÃ³n completada')
                ->with('duplicados', $import->duplicados ?? [])
                ->with('error_excel', $import->errores ?? []);

        } catch (\Exception $e) {
            $mensaje = $e->getMessage();
            if (str_contains($mensaje, 'No ReaderType')) {
                $mensaje = 'El archivo no es vÃ¡lido. Debe ser un Excel (.xlsx)';
            }
            return redirect()->back()->with('error', $mensaje);
        }
    }

   public function buscar(Request $request)
{
    $buscar = $request->buscar ?? $request->q;

    $afiliados = Afiliado::where('empresa_id', session('empresa_id'))
        ->where(function ($q) use ($buscar) {
            $q->where('numero_documento', 'like', "%{$buscar}%")
              ->orWhere('primer_nombre', 'like', "%{$buscar}%")
              ->orWhere('primer_apellido', 'like', "%{$buscar}%");
        })
        ->limit(20)
        ->get()
        ->map(function ($afiliado) {
            // AquÃ­ verificamos si tiene afiliaciÃ³n activa
            $afiliado->tiene_afiliacion_activa = $afiliado->afiliaciones()->where('estado', 1)->exists();
            return $afiliado;
        });

    return response()->json($afiliados);
}
}

