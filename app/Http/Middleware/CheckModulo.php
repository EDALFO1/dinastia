<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ModuloService;
use Symfony\Component\HttpFoundation\Response;

class CheckModulo
{
    public function __construct(private ModuloService $service) {}

    public function handle(Request $request, Closure $next, string ...$slugs): Response
    {
        foreach ($slugs as $slug) {
            if ($this->service->puedeAcceder($slug)) {
                return $next($request);
            }
        }

        return redirect()->route('dashboard')
            ->with('error', 'No tienes permiso para acceder a esa sección.');
    }
}
