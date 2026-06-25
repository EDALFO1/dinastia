<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\BaseModel;
use Symfony\Component\HttpFoundation\Response;

class ValidateMultiTenantBinding
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get the empresa_id from the header
        $empresaId = (int) $request->header('X-Empresa-ID');

        if (!$empresaId || !auth()->check()) {
            return $next($request);
        }

        // Check all route parameters for BaseModel instances
        foreach ($request->route()->parameters() as $param) {
            if ($param instanceof BaseModel && isset($param->empresa_id)) {
                if ($param->empresa_id !== $empresaId) {
                    // Model belongs to a different empresa - return 404
                    abort(404);
                }
            }
        }

        return $next($request);
    }
}
