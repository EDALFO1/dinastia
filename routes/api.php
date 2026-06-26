<?php

/**
 * @OA\Info(
 *     title="Dinastia ERP API",
 *     version="1.0.0",
 *     description="API REST para el sistema de gestión de nómina y recursos humanos Dinastia ERP",
 *     @OA\Contact(email="soporte@dinastia.co")
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="token"
 * )
 * @OA\Server(url="/api/v1", description="API v1")
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Domains\Shared\Controllers\Api\AuthApiController;
use App\Domains\Payroll\Controllers\Api\AfiliadoApiController;
use App\Domains\Payroll\Controllers\Api\ReciboApiController;
use App\Domains\Payroll\Controllers\Api\RemisionApiController;
use App\Domains\Shared\Controllers\Api\EmpresaApiController;
use App\Domains\Shared\Controllers\Api\UserApiController;
use App\Domains\Invoicing\Controllers\Api\InvoiceApiController;

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

            // Invoices
            Route::apiResource('invoices', InvoiceApiController::class);
            Route::post('invoices/{invoice}/sign', [InvoiceApiController::class, 'sign']);
            Route::post('invoices/{invoice}/send-to-dian', [InvoiceApiController::class, 'sendToDian']);

            // Empresa context
            Route::get('empresas/current', [EmpresaApiController::class, 'current']);
            Route::get('users/me', [UserApiController::class, 'me']);
        });
    });
});
