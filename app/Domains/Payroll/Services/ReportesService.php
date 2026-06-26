<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Models\NominaElectronica;
use App\Models\Afiliado;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportesService
{
    /**
     * Generar recibo PDF de nómina
     */
    public function generarReciboPdf(NominaElectronica $nomina): string
    {
        try {
            $generator = new ReciboPdfGenerator();
            return $generator->generate($nomina);
        } catch (\Exception $e) {
            Log::error('Error generando recibo PDF', [
                'nomina_id' => $nomina->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generar nota de crédito
     */
    public function generarNotaCredito(NominaElectronica $nomina, string $razon, float $monto): string
    {
        try {
            $generator = new NotaCreditoDebitoGenerator();
            return $generator->generarNotaCredito($nomina, $razon, $monto);
        } catch (\Exception $e) {
            Log::error('Error generando nota de crédito', [
                'nomina_id' => $nomina->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generar nota de débito
     */
    public function generarNotaDebito(NominaElectronica $nomina, string $razon, float $monto): string
    {
        try {
            $generator = new NotaCreditoDebitoGenerator();
            return $generator->generarNotaDebito($nomina, $razon, $monto);
        } catch (\Exception $e) {
            Log::error('Error generando nota de débito', [
                'nomina_id' => $nomina->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generar reporte PILA consolidado
     */
    public function generarReportePila(Empresa $empresa, Carbon $desde, Carbon $hasta): string
    {
        try {
            $generator = new ReportePilaGenerator();
            return $generator->generate($empresa, $desde, $hasta);
        } catch (\Exception $e) {
            Log::error('Error generando reporte PILA', [
                'empresa_id' => $empresa->id,
                'desde' => $desde,
                'hasta' => $hasta,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generar certificado PILA de afiliado
     */
    public function generarCertificadoPila(Afiliado $afiliado, int $anio): string
    {
        try {
            $generator = new CertificadoPilaGenerator();
            return $generator->generate($afiliado, $anio);
        } catch (\Exception $e) {
            Log::error('Error generando certificado PILA', [
                'afiliado_id' => $afiliado->id,
                'anio' => $anio,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generar batch de certificados PILA (por empresa)
     */
    public function generarBatchCertificadosPila(Empresa $empresa, int $anio, string $outputPath): array
    {
        try {
            $afiliados = Afiliado::where('empresa_id', $empresa->id)
                ->whereHas('recibos', fn ($q) => $q->whereYear('fecha_pago', $anio))
                ->get();

            $archivos = [];

            foreach ($afiliados as $afiliado) {
                $pdf = $this->generarCertificadoPila($afiliado, $anio);
                $archivos[] = [
                    'afiliado_id' => $afiliado->id,
                    'nombre_archivo' => "Certificado_PILA_{$afiliado->documento}_{$anio}.pdf",
                    'contenido' => $pdf,
                ];
            }

            Log::info('Batch de certificados PILA generado', [
                'empresa_id' => $empresa->id,
                'anio' => $anio,
                'cantidad' => count($archivos),
            ]);

            return $archivos;
        } catch (\Exception $e) {
            Log::error('Error generando batch certificados PILA', [
                'empresa_id' => $empresa->id,
                'anio' => $anio,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generar reporte 347 (resumen anual de aportes)
     */
    public function generarReporte347(Empresa $empresa, int $anio): array
    {
        try {
            $afiliados = Afiliado::where('empresa_id', $empresa->id)
                ->with(['recibos' => function ($q) use ($anio) {
                    $q->whereYear('fecha_pago', $anio)
                        ->with('aportes');
                }])
                ->get()
                ->filter(fn ($a) => $a->recibos->isNotEmpty());

            $datos = [
                'empresa' => $empresa,
                'anio' => $anio,
                'fecha_generacion' => now(),
                'resumen_general' => [
                    'total_afiliados' => $afiliados->count(),
                    'total_salarios' => $afiliados->sum(fn ($a) => $a->recibos->sum('salario_base')),
                    'total_aportes' => $afiliados->sum(fn ($a) => $a->recibos->sum(fn ($r) => $r->aportes->sum('aporte_empleador'))),
                ],
                'afiliados' => $afiliados->map(function ($afiliado) {
                    $totalAportes = $afiliado->recibos->sum(fn ($r) => $r->aportes->sum('aporte_empleador'));
                    return [
                        'documento' => $afiliado->documento,
                        'nombre' => $afiliado->nombre,
                        'meses_aportados' => $afiliado->recibos->groupBy(fn ($r) => $r->fecha_pago->month)->count(),
                        'total_salarios' => $afiliado->recibos->sum('salario_base'),
                        'total_aportes' => $totalAportes,
                        'aportes_por_tipo' => $afiliado->recibos->lazy()
                            ->flatMap(fn ($r) => $r->aportes)
                            ->groupBy('tipo_aporte')
                            ->map(fn ($group) => $group->sum('aporte_empleador'))
                            ->toArray(),
                    ];
                })->values()->toArray(),
            ];

            Log::info('Reporte 347 generado', [
                'empresa_id' => $empresa->id,
                'anio' => $anio,
            ]);

            return $datos;
        } catch (\Exception $e) {
            Log::error('Error generando reporte 347', [
                'empresa_id' => $empresa->id,
                'anio' => $anio,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validar que nóminas están listas para reporte
     */
    public function validarNominasParaReporte(Empresa $empresa, Carbon $desde, Carbon $hasta): array
    {
        $nominas = NominaElectronica::where('empresa_id', $empresa->id)
            ->whereBetween('fecha_emision', [$desde, $hasta])
            ->get();

        $pendientes = $nominas->filter(fn ($n) => $n->estado === 'borrador')->count();
        $enviadas = $nominas->filter(fn ($n) => $n->estado === 'enviada')->count();
        $aceptadas = $nominas->filter(fn ($n) => $n->estado === 'aceptada')->count();
        $rechazadas = $nominas->filter(fn ($n) => $n->estado === 'rechazada')->count();

        return [
            'total_nominas' => $nominas->count(),
            'pendientes' => $pendientes,
            'enviadas' => $enviadas,
            'aceptadas' => $aceptadas,
            'rechazadas' => $rechazadas,
            'porcentaje_aceptadas' => $nominas->count() > 0
                ? round(($aceptadas / $nominas->count()) * 100, 2)
                : 0,
            'lista_para_reporte' => $pendientes === 0 && $rechazadas === 0,
        ];
    }
}
