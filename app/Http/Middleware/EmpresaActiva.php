<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EmpresaActiva
{
    public function handle(Request $request, Closure $next)
    {
        // 🔥 rutas permitidas sin empresa
        $rutasPermitidas = [
            'seleccionar.empresa',
            'cambiar.empresa',
            'logout'
        ];

        // 🔥 si no hay empresa en sesión
        if (!session('empresa_id')) {

            if (!in_array($request->route()->getName(), $rutasPermitidas)) {

                return redirect()
                    ->route('seleccionar.empresa')
                    ->with('warning', 'Debe seleccionar una empresa');
            }
        }

        return $next($request);
    }
}