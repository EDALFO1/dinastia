<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DianApiClient
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;
    protected string $accessToken;
    protected int $maxRetries = 3;
    protected int $retryDelayMs = 1000;

    public function __construct()
    {
        $this->clientId = config('invoicing.dian.client_id');
        $this->clientSecret = config('invoicing.dian.client_secret');
        $this->baseUrl = config('invoicing.dian.api_url', 'https://api.dian.gov.co/api/ws/fedora');
    }

    /**
     * Autenticar con DIAN y obtener token de acceso
     */
    public function authenticate(): string
    {
        if (!empty($this->accessToken)) {
            return $this->accessToken;
        }

        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->timeout(30)
                ->post("{$this->baseUrl}/oauth2/token", [
                    'grant_type' => 'client_credentials',
                    'scope' => 'invoice:submit invoice:status',
                ])
                ->throw();

            $this->accessToken = $response->json('access_token');

            Log::info('DIAN authentication successful', [
                'expires_in' => $response->json('expires_in'),
            ]);

            return $this->accessToken;
        } catch (Exception $e) {
            Log::error('DIAN authentication failed', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception('DIAN authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Enviar factura firmada a DIAN
     */
    public function sendInvoice(Invoice $invoice, int $attempt = 1): array
    {
        if (!$invoice->xml_factura || !$invoice->firma_digital) {
            throw new Exception('Invoice must be signed before sending');
        }

        try {
            $token = $this->authenticate();

            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/invoice/submit", [
                    'invoice_xml' => $invoice->xml_factura,
                    'sender_nit' => $invoice->sequence->empresa->nit ?? null,
                    'invoice_number' => $invoice->numero,
                    'invoice_date' => $invoice->fecha_emision->format('Y-m-d'),
                ])
                ->throw();

            $result = $response->json();

            Log::info('Invoice sent to DIAN successfully', [
                'invoice_id' => $invoice->id,
                'uuid_dian' => $result['uuid_dian'] ?? null,
                'tracking_number' => $result['tracking_number'] ?? null,
            ]);

            return [
                'success' => true,
                'uuid_dian' => $result['uuid_dian'] ?? null,
                'tracking_number' => $result['tracking_number'] ?? null,
                'status_url' => $result['status_url'] ?? null,
            ];
        } catch (Exception $e) {
            if ($attempt < $this->maxRetries) {
                Log::warning("Invoice send attempt {$attempt} failed, retrying...", [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);

                usleep($this->retryDelayMs * 1000 * $attempt);
                return $this->sendInvoice($invoice, $attempt + 1);
            }

            Log::error('Invoice send failed after max retries', [
                'invoice_id' => $invoice->id,
                'attempts' => $attempt,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Obtener estado de factura en DIAN
     */
    public function getInvoiceStatus(string $uuidDian): array
    {
        try {
            $token = $this->authenticate();

            $response = Http::withToken($token)
                ->timeout(30)
                ->get("{$this->baseUrl}/invoice/status/{$uuidDian}")
                ->throw();

            $data = $response->json();

            Log::info('Invoice status retrieved from DIAN', [
                'uuid_dian' => $uuidDian,
                'status' => $data['status'] ?? null,
            ]);

            return [
                'uuid_dian' => $uuidDian,
                'status' => $data['status'] ?? null, // accepted, rejected, pending
                'message' => $data['message'] ?? null,
                'rejection_reason' => $data['rejection_reason'] ?? null,
                'last_updated' => $data['last_updated'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get invoice status from DIAN', [
                'uuid_dian' => $uuidDian,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Recibir acuse de recibo (webhook simulado)
     */
    public function receiveAck(array $payload): void
    {
        try {
            $uuidDian = $payload['uuid_dian'] ?? null;
            $status = $payload['status'] ?? null;

            if (!$uuidDian || !$status) {
                throw new Exception('Missing uuid_dian or status in ACK payload');
            }

            $invoice = Invoice::where('uuid_dian', $uuidDian)->first();

            if (!$invoice) {
                Log::warning('Received ACK for unknown invoice', [
                    'uuid_dian' => $uuidDian,
                ]);
                return;
            }

            $invoiceStatus = $status === 'ACEPTACION' ? 'aceptada' : 'rechazada';

            $invoice->update([
                'estado' => $invoiceStatus,
            ]);

            Log::info('Invoice ACK processed', [
                'invoice_id' => $invoice->id,
                'uuid_dian' => $uuidDian,
                'status' => $invoiceStatus,
                'rejection_reason' => $payload['rejection_reason'] ?? null,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to process ACK', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            throw $e;
        }
    }

    /**
     * Validar factura en DIAN antes de enviar (pre-validación)
     */
    public function validateInvoice(Invoice $invoice): array
    {
        try {
            $token = $this->authenticate();

            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/invoice/validate", [
                    'invoice_xml' => $invoice->xml_factura,
                    'document_type' => $invoice->tipo_documento->value,
                    'total_amount' => (string) $invoice->total,
                ])
                ->throw();

            $data = $response->json();

            Log::info('Invoice validated with DIAN', [
                'invoice_id' => $invoice->id,
                'is_valid' => $data['is_valid'] ?? false,
            ]);

            return [
                'is_valid' => $data['is_valid'] ?? false,
                'errors' => $data['errors'] ?? [],
                'warnings' => $data['warnings'] ?? [],
            ];
        } catch (Exception $e) {
            Log::error('Invoice validation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'is_valid' => false,
                'errors' => [$e->getMessage()],
                'warnings' => [],
            ];
        }
    }

    /**
     * Revocar factura (para facturas aceptadas)
     */
    public function revokeInvoice(Invoice $invoice, string $reason): array
    {
        if ($invoice->estado !== 'aceptada') {
            throw new Exception('Only accepted invoices can be revoked');
        }

        try {
            $token = $this->authenticate();

            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/invoice/revoke/{$invoice->uuid_dian}", [
                    'reason' => $reason,
                    'revocation_date' => now()->format('Y-m-d'),
                ])
                ->throw();

            $data = $response->json();

            Log::info('Invoice revoked with DIAN', [
                'invoice_id' => $invoice->id,
                'uuid_dian' => $invoice->uuid_dian,
            ]);

            return [
                'success' => true,
                'revocation_id' => $data['revocation_id'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Invoice revocation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
