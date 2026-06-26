<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Models\NominaElectronica;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class NominaApiClient
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;
    protected string $accessToken;
    protected int $maxRetries = 3;
    protected int $retryDelayMs = 1000;

    public function __construct()
    {
        $this->clientId = config('payroll.dian.client_id');
        $this->clientSecret = config('payroll.dian.client_secret');
        $this->baseUrl = config('payroll.dian.api_url', 'https://api.dian.gov.co/api/ws/nomina');
    }

    /**
     * Autenticar con DIAN (OAuth2)
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
                    'scope' => 'nomina:submit nomina:status',
                ])
                ->throw();

            $this->accessToken = $response->json('access_token');

            Log::info('DIAN Nómina authentication successful', [
                'expires_in' => $response->json('expires_in'),
            ]);

            return $this->accessToken;
        } catch (Exception $e) {
            Log::error('DIAN Nómina authentication failed', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception('DIAN authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Enviar nómina a DIAN
     */
    public function enviarNomina(NominaElectronica $nomina, int $attempt = 1): array
    {
        if (!$nomina->xml_nomina || !$nomina->firma_digital) {
            throw new Exception('Nómina debe estar firmada antes de enviar');
        }

        try {
            $token = $this->authenticate();

            $response = Http::withToken($token)
                ->timeout(30)
                ->post("{$this->baseUrl}/nomina/submit", [
                    'payroll_xml' => $nomina->xml_nomina,
                    'employer_nit' => $nomina->empresa->nit,
                    'payroll_number' => $nomina->numero_nomina,
                    'payment_date' => $nomina->periodo_pago_final->format('Y-m-d'),
                ])
                ->throw();

            $result = $response->json();

            Log::info('Nómina sent to DIAN successfully', [
                'nomina_id' => $nomina->id,
                'uuid_dian' => $result['uuid_dian'] ?? null,
            ]);

            return [
                'success' => true,
                'uuid_dian' => $result['uuid_dian'] ?? null,
                'tracking_number' => $result['tracking_number'] ?? null,
                'status_url' => $result['status_url'] ?? null,
            ];
        } catch (Exception $e) {
            if ($attempt < $this->maxRetries) {
                Log::warning("Payroll send attempt {$attempt} failed, retrying...", [
                    'nomina_id' => $nomina->id,
                    'error' => $e->getMessage(),
                ]);

                usleep($this->retryDelayMs * 1000 * $attempt);
                return $this->enviarNomina($nomina, $attempt + 1);
            }

            Log::error('Payroll send failed after max retries', [
                'nomina_id' => $nomina->id,
                'attempts' => $attempt,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Obtener estado de nómina en DIAN
     */
    public function obtenerEstado(string $uuidDian): array
    {
        try {
            $token = $this->authenticate();

            $response = Http::withToken($token)
                ->timeout(30)
                ->get("{$this->baseUrl}/nomina/status/{$uuidDian}")
                ->throw();

            $data = $response->json();

            Log::info('Payroll status retrieved from DIAN', [
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
            Log::error('Failed to get payroll status from DIAN', [
                'uuid_dian' => $uuidDian,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Recibir acuse de recibo (webhook)
     */
    public function recibirAck(array $payload): void
    {
        try {
            $uuidDian = $payload['uuid_dian'] ?? null;
            $status = $payload['status'] ?? null;

            if (!$uuidDian || !$status) {
                throw new Exception('Missing uuid_dian or status in ACK payload');
            }

            $nomina = NominaElectronica::where('uuid_dian', $uuidDian)->first();

            if (!$nomina) {
                Log::warning('Received ACK for unknown payroll', [
                    'uuid_dian' => $uuidDian,
                ]);
                return;
            }

            $nominaStatus = $status === 'ACEPTACION' ? 'aceptada' : 'rechazada';

            $nomina->update([
                'estado' => $nominaStatus,
                'respuesta_dian' => $payload,
                'fecha_envio_dian' => now(),
            ]);

            Log::info('Payroll ACK processed', [
                'nomina_id' => $nomina->id,
                'uuid_dian' => $uuidDian,
                'status' => $nominaStatus,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to process ACK', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            throw $e;
        }
    }
}
