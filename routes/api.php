<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Domains\Shared\Controllers\Api\AuthApiController;
use App\Domains\Payroll\Controllers\Api\AfiliadoApiController;
use App\Domains\Payroll\Controllers\Api\ReciboApiController;
use App\Domains\Payroll\Controllers\Api\RemisionApiController;
use App\Domains\Shared\Controllers\Api\EmpresaApiController;
use App\Domains\Shared\Controllers\Api\UserApiController;

Route::prefix('v1')->group(function () {
    // Public auth endpoints
    Route::post('auth/login', [AuthApiController::class, 'login']);

    // Protected endpoints (require token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthApiController::class, 'logout']);
        Route::get('auth/me', [AuthApiController::class, 'me']);
        Route::get('auth/empresas', [AuthApiController::class, 'empresas']);

        // Protected endpoints requiring empresa context
        Route::middleware(['empresa.api', 'validate-tenant-binding'])->group(function () {
            // Afiliados
            Route::apiResource('afiliados', AfiliadoApiController::class);
            Route::get('afiliados/{afiliado}/recibos', [AfiliadoApiController::class, 'recibos']);

            // Recibos
            Route::apiResource('recibos', ReciboApiController::class);

            // Remisiones
            Route::apiResource('remisiones', RemisionApiController::class);

            // Empresa context
            Route::get('empresas/current', [EmpresaApiController::class, 'current']);
            Route::get('users/me', [UserApiController::class, 'me']);
        });
    });
});
