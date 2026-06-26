<?php

namespace App\Domains\Invoicing\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Resources\InvoiceResource;
use App\Domains\Invoicing\Requests\StoreInvoiceRequest;
use App\Domains\Invoicing\Requests\UpdateInvoiceRequest;
use App\Domains\Invoicing\Services\DianApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class InvoiceApiController extends Controller
{
    protected DianApiClient $dianClient;

    public function __construct(DianApiClient $dianClient)
    {
        $this->dianClient = $dianClient;
    }

    /**
     * @OA\Get(
     *     path="/v1/invoices",
     *     summary="Listar facturas",
     *     security={{"sanctum":{}}},
     *     tags={"Invoices"},
     *     parameters={
     *         @OA\Parameter(name="estado", in="query", description="Filtrar por estado (borrador, enviada, aceptada, rechazada)", schema={"type": "string"}),
     *         @OA\Parameter(name="page", in="query", description="Página", schema={"type": "integer"}),
     *         @OA\Parameter(name="per_page", in="query", description="Items por página", schema={"type": "integer"})
     *     },
     *     @OA\Response(response=200, description="Lista de facturas")
     * )
     */
    public function index()
    {
        $estado = request('estado');
        $query = Invoice::with(['sequence', 'lineItems', 'taxes']);

        if ($estado) {
            $query->where('estado', $estado);
        }

        $invoices = $query->paginate(15);
        return InvoiceResource::collection($invoices);
    }

    /**
     * @OA\Get(
     *     path="/v1/invoices/{id}",
     *     summary="Obtener factura",
     *     security={{"sanctum":{}}},
     *     tags={"Invoices"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID de la factura"),
     *     @OA\Response(response=200, description="Detalles de factura")
     * )
     */
    public function show(Invoice $invoice)
    {
        return new InvoiceResource($invoice->load(['sequence', 'lineItems', 'taxes']));
    }

    /**
     * @OA\Post(
     *     path="/v1/invoices",
     *     summary="Crear factura",
     *     security={{"sanctum":{}}},
     *     tags={"Invoices"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Invoice")
     *     ),
     *     @OA\Response(response=201, description="Factura creada")
     * )
     */
    public function store(StoreInvoiceRequest $request)
    {
        $validated = $request->validated();
        $validated['empresa_id'] = $request->user()->current_empresa_id;

        $invoice = Invoice::create($validated);
        $invoice->load(['sequence', 'lineItems', 'taxes']);

        return new InvoiceResource($invoice), 201;
    }

    /**
     * @OA\Put(
     *     path="/v1/invoices/{id}",
     *     summary="Actualizar factura",
     *     security={{"sanctum":{}}},
     *     tags={"Invoices"},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Invoice")),
     *     @OA\Response(response=200, description="Factura actualizada")
     * )
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        if (!$invoice->canEdit()) {
            return response()->json(['error' => 'Cannot edit invoice in this state'], 422);
        }

        $validated = $request->validated();
        $invoice->update($validated);
        $invoice->load(['sequence', 'lineItems', 'taxes']);

        return new InvoiceResource($invoice);
    }

    /**
     * @OA\Delete(
     *     path="/v1/invoices/{id}",
     *     summary="Eliminar factura",
     *     security={{"sanctum":{}}},
     *     tags={"Invoices"},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Factura eliminada")
     * )
     */
    public function destroy(Invoice $invoice)
    {
        if (!$invoice->canEdit()) {
            return response()->json(['error' => 'Cannot delete invoice in this state'], 422);
        }

        $invoice->delete();
        return response()->json(status: 200);
    }

    /**
     * @OA\Post(
     *     path="/v1/invoices/{id}/send-to-dian",
     *     summary="Enviar factura a DIAN",
     *     security={{"sanctum":{}}},
     *     tags={"Invoices"},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Factura enviada a DIAN")
     * )
     */
    public function sendToDian(Invoice $invoice)
    {
        if (!$invoice->xml_factura || !$invoice->firma_digital) {
            return response()->json(['error' => 'Invoice must be signed before sending'], 422);
        }

        try {
            $result = $this->dianClient->sendInvoice($invoice);

            $invoice->update([
                'uuid_dian' => $result['uuid_dian'] ?? null,
                'estado' => 'enviada',
            ]);

            return new InvoiceResource($invoice->refresh());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/v1/invoices/{id}/sign",
     *     summary="Firmar factura",
     *     security={{"sanctum":{}}},
     *     tags={"Invoices"},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"certificate_path", "password"},
     *             @OA\Property(property="certificate_path", type="string", description="Ruta al certificado PKCS12"),
     *             @OA\Property(property="password", type="string", description="Contraseña del certificado")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Factura firmada")
     * )
     */
    public function sign(Invoice $invoice)
    {
        $request = request();
        $certificatePath = $request->input('certificate_path');
        $password = $request->input('password', '');

        try {
            $signedXml = $invoice->toSignedXml($certificatePath, $password);
            return new InvoiceResource($invoice->refresh());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
