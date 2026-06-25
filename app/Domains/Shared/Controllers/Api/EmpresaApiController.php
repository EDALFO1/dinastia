<?php

namespace App\Domains\Shared\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmpresaResource;
use Illuminate\Http\Request;

class EmpresaApiController extends Controller
{
    public function current(Request $request): EmpresaResource
    {
        $empresaId = $request->user()->current_empresa_id;
        $empresa = $request->user()->empresas()->find($empresaId);
        return new EmpresaResource($empresa);
    }
}
