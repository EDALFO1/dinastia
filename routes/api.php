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
use App\Domains\Invoicing\Controllers\Api\ReportApiController;
use App\Domains\Accounting\Controllers\JournalEntryApiController;
use App\Domains\Accounting\Controllers\FinancialReportController;

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
            Route::get('invoices/{invoice}/pdf', [InvoiceApiController::class, 'downloadPdf']);
            Route::post('invoices/{invoice}/sign', [InvoiceApiController::class, 'sign']);
            Route::post('invoices/{invoice}/send-to-dian', [InvoiceApiController::class, 'sendToDian']);

            // Reports
            Route::prefix('reports')->group(function () {
                Route::get('sales-book', [ReportApiController::class, 'salesBook']);
                Route::get('sales-book-summary', [ReportApiController::class, 'salesBookSummary']);
                Route::get('invoice-audit-log', [ReportApiController::class, 'invoiceAuditLog']);
                Route::get('monthly-summary', [ReportApiController::class, 'monthlySummary']);
            });

            // Accounting - Journal Entries & Reports
            Route::prefix('accounting')->group(function () {
                // Journal Entries
                Route::apiResource('journal-entries', JournalEntryApiController::class);
                Route::post('journal-entries/{id}/approve', [JournalEntryApiController::class, 'approve']);
                Route::post('journal-entries/{id}/reject', [JournalEntryApiController::class, 'reject']);
                Route::get('summary/balances', [JournalEntryApiController::class, 'balanceSummary']);

                // Financial Reports
                Route::prefix('reports')->group(function () {
                    Route::get('ledger', [FinancialReportController::class, 'ledger']);
                    Route::get('ledger-consolidated', [FinancialReportController::class, 'ledgerConsolidated']);
                    Route::get('balance-sheet', [FinancialReportController::class, 'balanceSheet']);
                    Route::get('balance-sheet-vertical', [FinancialReportController::class, 'balanceSheetVertical']);
                    Route::get('income-statement', [FinancialReportController::class, 'incomeStatement']);
                    Route::get('income-comparison', [FinancialReportController::class, 'incomeComparison']);
                    Route::get('financial-ratios', [FinancialReportController::class, 'financialRatios']);
                });
            });

            // Empresa context
            Route::get('empresas/current', [EmpresaApiController::class, 'current']);
            Route::get('users/me', [UserApiController::class, 'me']);
        });
    });
});
