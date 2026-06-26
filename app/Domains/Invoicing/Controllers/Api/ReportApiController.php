<?php

namespace App\Domains\Invoicing\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Invoicing\Services\SalesBookGenerator;
use App\Domains\Invoicing\Models\InvoiceAuditLog;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/v1/reports/sales-book",
     *     summary="Descargar libro de ventas",
     *     security={{"sanctum":{}}},
     *     tags={"Reports"},
     *     parameters={
     *         @OA\Parameter(name="fecha_inicio", in="query", required=true, description="YYYY-MM-DD", schema={"type": "string"}),
     *         @OA\Parameter(name="fecha_fin", in="query", required=true, description="YYYY-MM-DD", schema={"type": "string"}),
     *         @OA\Parameter(name="estado", in="query", description="borrador|enviada|aceptada|rechazada", schema={"type": "string"})
     *     },
     *     @OA\Response(response=200, description="Excel file")
     * )
     */
    public function salesBook(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
            'estado' => 'nullable|in:borrador,enviada,aceptada,rechazada',
        ]);

        $empresa = Empresa::find($request->user()->current_empresa_id);

        $generator = new SalesBookGenerator(
            $empresa,
            Carbon::parse($request->input('fecha_inicio')),
            Carbon::parse($request->input('fecha_fin')),
            $request->input('estado')
        );

        return $generator->download();
    }

    /**
     * @OA\Get(
     *     path="/v1/reports/sales-book-summary",
     *     summary="Resumen del libro de ventas",
     *     security={{"sanctum":{}}},
     *     tags={"Reports"},
     *     parameters={
     *         @OA\Parameter(name="fecha_inicio", in="query", required=true, description="YYYY-MM-DD", schema={"type": "string"}),
     *         @OA\Parameter(name="fecha_fin", in="query", required=true, description="YYYY-MM-DD", schema={"type": "string"})
     *     },
     *     @OA\Response(response=200, description="Summary statistics")
     * )
     */
    public function salesBookSummary(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
        ]);

        $empresa = Empresa::find($request->user()->current_empresa_id);

        $generator = new SalesBookGenerator(
            $empresa,
            Carbon::parse($request->input('fecha_inicio')),
            Carbon::parse($request->input('fecha_fin'))
        );

        return response()->json([
            'summary' => $generator->getSummary(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1/reports/invoice-audit-log",
     *     summary="Bitácora de cambios de factura",
     *     security={{"sanctum":{}}},
     *     tags={"Reports"},
     *     parameters={
     *         @OA\Parameter(name="invoice_id", in="query", required=true, description="ID de factura", schema={"type": "integer"})
     *     },
     *     @OA\Response(response=200, description="Audit log entries")
     * )
     */
    public function invoiceAuditLog(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        $logs = InvoiceAuditLog::where('invoice_id', $request->input('invoice_id'))
            ->where('empresa_id', $request->user()->current_empresa_id)
            ->orderBy('timestamp', 'desc')
            ->paginate(50);

        return response()->json([
            'data' => $logs->items(),
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1/reports/monthly-summary",
     *     summary="Resumen de ventas por mes",
     *     security={{"sanctum":{}}},
     *     tags={"Reports"},
     *     parameters={
     *         @OA\Parameter(name="anio", in="query", required=true, description="Año YYYY", schema={"type": "string"})
     *     },
     *     @OA\Response(response=200, description="Monthly summary")
     * )
     */
    public function monthlySummary(Request $request)
    {
        $request->validate([
            'anio' => 'required|digits:4|numeric',
        ]);

        $year = (int) $request->input('anio');
        $empresaId = $request->user()->current_empresa_id;

        $summary = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1);
            $endDate = $startDate->clone()->endOfMonth();

            $invoices = \App\Domains\Invoicing\Models\Invoice::where('empresa_id', $empresaId)
                ->whereBetween('fecha_emision', [$startDate, $endDate])
                ->with(['taxes'])
                ->get();

            $summary[sprintf('%04d-%02d', $year, $month)] = [
                'mes' => $startDate->format('F'),
                'cantidad' => $invoices->count(),
                'subtotal' => $invoices->sum('subtotal'),
                'descuentos' => $invoices->sum('descuento'),
                'impuestos' => $invoices->sum('total_impuestos'),
                'total' => $invoices->sum('total'),
                'aceptadas' => $invoices->where('estado', 'aceptada')->count(),
                'pendientes' => $invoices->where('estado', 'enviada')->count(),
                'rechazadas' => $invoices->where('estado', 'rechazada')->count(),
            ];
        }

        return response()->json([
            'ano' => $year,
            'meses' => $summary,
        ]);
    }
}
