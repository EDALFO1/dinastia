<?php

namespace App\Http\Controllers;

use App\Models\ArlAfiliado;
use App\Models\Arl;
use App\Models\Documento;
use App\Models\EmpresaLaboral;
use App\Exports\ArlAfiliadosExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArlAfiliadoController extends Controller
{
    public function index()
    {
        $titulo = 'Afiliados ARL';

        $afiliados = ArlAfiliado::with(['arl', 'documento', 'empresaLaboral'])
            ->orderBy('nombre')
            ->paginate(10);

        return view('modules.arl-afiliados.index', compact('titulo', 'afiliados'));
    }

    public function buscar(Request $request)
    {
        $q = $request->q ?? $request->buscar;

        if (!$q) {
            return response()->json([]);
        }

        $results = ArlAfiliado::with(['arl', 'documento'])
            ->where(function ($query) use ($q) {
                $query->where('numero', 'like', "%{$q}%")
                      ->orWhere('nombre', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get();

        return response()->json($results);
    }

    public function create()
    {
        $titulo     = 'Nuevo Afiliado ARL';
        $arls       = Arl::orderBy('nombre')->get();
        $documentos = Documento::orderBy('nombre')->get();
        $empresas   = EmpresaLaboral::where('estado', true)->orderBy('nombre')->get();

        return view('modules.arl-afiliados.create', compact('titulo', 'arls', 'documentos', 'empresas'));
    }

    public function store(Request $request)
    {
        $request->validate(array_merge(ArlAfiliado::rules(), [
            'numero' => [
                'required',
                'string',
                'max:50',
                Rule::unique('arl_afiliados')
                    ->where(fn($q) => $q->where('empresa_id', session('empresa_id'))),
            ],
        ]));

        ArlAfiliado::create($request->all());

        return redirect()->route('arl-afiliados.index')
            ->with('success', 'Afiliado ARL creado correctamente.');
    }

    public function edit(ArlAfiliado $arl_afiliado)
    {
        $titulo     = 'Editar Afiliado ARL';
        $arls       = Arl::orderBy('nombre')->get();
        $documentos = Documento::orderBy('nombre')->get();
        $empresas   = EmpresaLaboral::orderBy('nombre')->get();

        return view('modules.arl-afiliados.edit', compact(
            'titulo', 'arl_afiliado', 'arls', 'documentos', 'empresas'
        ));
    }

    public function update(Request $request, ArlAfiliado $arl_afiliado)
    {
        $request->validate(array_merge(ArlAfiliado::rules($arl_afiliado->id), [
            'numero' => [
                'required',
                'string',
                'max:50',
                Rule::unique('arl_afiliados')
                    ->where(fn($q) => $q->where('empresa_id', session('empresa_id')))
                    ->ignore($arl_afiliado->id),
            ],
        ]));

        $data = $request->all();
        $data['estado'] = (bool) $request->estado;

        $arl_afiliado->update($data);

        return redirect()->route('arl-afiliados.index')
            ->with('success', 'Afiliado ARL actualizado correctamente.');
    }

    public function destroy(ArlAfiliado $arl_afiliado)
    {
        $arl_afiliado->delete();

        return redirect()->route('arl-afiliados.index')
            ->with('success', 'Afiliado ARL eliminado.');
    }

    public function exportar(Request $request)
    {
        return Excel::download(
            new ArlAfiliadosExport($request->all()),
            'afiliados-arl.xlsx'
        );
    }
}
