<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetEmpresaContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $empresaId = (int) $request->header('X-Empresa-ID');

        if (!$empresaId) {
            return response()->json(['message' => 'X-Empresa-ID header required'], 422);
        }

        $user = auth()->user();
        $empresa = $user->empresas()->find($empresaId);

        if (!$empresa) {
            return response()->json(['message' => 'Unauthorized: empresa access denied'], 403);
        }

        // Set empresa_id on user for EmpresaScope to read
        $user->current_empresa_id = $empresaId;

        return $next($request);
    }
}
