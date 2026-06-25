<?php

namespace App\Domains\Payroll\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Afiliado;
use App\Models\EmpresaLaboral;
use App\Models\Asesor;
use App\Models\Documento;
use App\Models\SubtipoCotizante;
use App\Imports\AfiliadosImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AfiliadosTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\StoreAfiliadoRequest;
use App\Http\Requests\UpdateAfiliadoRequest;
use App\Exports\AfiliadosExport;



class AfiliadoController extends Controller
{
   public function index()
{
    $titulo = "Afiliados";

    $afiliados = Afiliado::with([
        'empresaLaboral',
        'documento',
        'asesor'
    ])
    ->orderBy('primer_apellido')
    ->paginate(10); // ðŸ”¥ PAGINACIÃ“N

    return view('modules.afiliados.index',
        compact('titulo','afiliados'));
}

    public function create()
    {
        $titulo = "Crear Afiliado";

        // ðŸ”’ TODO ya filtrado automÃ¡ticamente por empresa
        $empresasLaborales = EmpresaLaboral::orderBy('nombre')->get();
        $asesores = Asesor::orderBy('nombre')->get();
        $documentos = Documento::orderBy('nombre')->get();
        $subtipos = SubtipoCotizante::orderBy('nombre')->get();

        return view('modules.afiliados.create',
            compact(
                'titulo',
                'empresasLaborales',
                'asesores',
                'documentos',
                'subtipos'
            ));
    }

    public function store(StoreAfiliadoRequest $request)
{
    $request->validate([
        'empresa_laboral_id' => 'required|exists:empresas_laborales,id',
        'asesor_id' => 'nullable|exists:asesores,id',
        'documento_id' => 'required|exists:documentos,id',
        'subtipo_cotizante_id' => 'required|exists:subtipo_cotizantes,id',

        'numero_documento' => [
            'required',
            'string',
            'max:50',
            Rule::unique('afiliados')
                ->where(fn ($q) => $q->where('empresa_id', session('empresa_id')))
        ],

        'primer_nombre' => 'required|string|max:255',
        'primer_apellido' => 'required|string|max:255',

        // ðŸ”¥ importante
        'fecha_nacimiento' => 'required|date_format:Y-m-d',
        

        'sexo' => 'required|in:M,F,Otro',
    ]);

    $data = $request->validated();

    Afiliado::create($data);

    return redirect()->route('afiliados.index')
        ->with('success','Afiliado creado correctamente');
}

    public function edit(Afiliado $afiliado)
    {
        $titulo = "Editar Afiliado";

        $empresasLaborales = EmpresaLaboral::orderBy('nombre')->get();
        $asesores = Asesor::orderBy('nombre')->get();
        $documentos = Documento::orderBy('nombre')->get();
        $subtipos = SubtipoCotizante::orderBy('nombre')->get();

        return view('modules.afiliados.edit',
            compact(
                'titulo',
                'afiliado',
                'empresasLaborales',
                'asesores',
                'documentos',
                'subtipos'
            ));
    }

   public function update(UpdateAfiliadoRequest $request, Afiliado $afiliado)
{
    // Obtener solo los datos validados
    $data = $request->validated();

    // ðŸ”¥ Forzar boolean correcto
    $data['estado'] = (bool) $request->estado;

    // (Opcional) asegurar empresa_id desde sesiÃ³n si no quieres que cambie
    $data['empresa_id'] = session('empresa_id');

    // Actualizar el afiliado
    $afiliado->update($data);

    // Redirigir con mensaje
    return redirect()->route('afiliados.index')
        ->with('success', 'Afiliado actualizado correctamente');
}

    public function destroy(Afiliado $afiliado)
    {
        $afiliado->delete();

        return redirect()->route('afiliados.index')
            ->with('success','Afiliado eliminado');
    }

    // ðŸ”¥ IMPORTACIÃ“N SEGURA
public function importar(Request $request)
{
    $request->validate([
        'archivo' => 'required|file|mimes:xlsx,xls,csv'
    ], [
        'archivo.required' => 'Debes seleccionar un archivo.',
        'archivo.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV.'
    ]);

    try {
        $import = new AfiliadosImport(session('empresa_id'));

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

    public function descargarPlantilla()
    {
        $export   = new AfiliadosTemplateExport((int) session('empresa_id'));
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($export->build());
        $tmpPath  = tempnam(sys_get_temp_dir(), 'afiliados_') . '.xlsx';
        $writer->save($tmpPath);

        return response()->download($tmpPath, 'plantilla_afiliados.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
    public function buscar(Request $request)
{
    $buscar = $request->buscar ?? $request->q;

    if (!$buscar) {
        return response()->json([]);
    }

    $afiliados = Afiliado::with(['empresaLaboral'])
        ->where('empresa_id', session('empresa_id'))
        ->where(function ($q) use ($buscar) {
            $q->where('numero_documento', 'like', "%{$buscar}%")
              ->orWhere('primer_nombre', 'like', "%{$buscar}%")
              ->orWhere('primer_apellido', 'like', "%{$buscar}%");
        })
        ->limit(20)
        ->get();

    return response()->json($afiliados);
}
public function exportar(Request $request)
{
    return Excel::download(
        new AfiliadosExport($request->all()),
        'afiliados.xlsx'
    );
}
}

