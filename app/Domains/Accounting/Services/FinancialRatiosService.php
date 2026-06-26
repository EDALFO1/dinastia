<?php

namespace App\Domains\Accounting\Services;

use Carbon\Carbon;

class FinancialRatiosService
{
    protected BalanceSheetService $balanceSheetService;
    protected IncomeStatementService $incomeStatementService;

    public function __construct(
        BalanceSheetService $balanceSheetService,
        IncomeStatementService $incomeStatementService
    ) {
        $this->balanceSheetService = $balanceSheetService;
        $this->incomeStatementService = $incomeStatementService;
    }

    /**
     * Calcular ratios financieros completos
     */
    public function calculateRatios(
        int $empresaId,
        Carbon $fecha,
        Carbon $desde,
        Carbon $hasta
    ): array {
        $balanceSheet = $this->balanceSheetService->generateBalanceSheet($empresaId, $fecha);
        $incomeStatement = $this->incomeStatementService->generateIncomeStatement(
            $empresaId,
            $desde,
            $hasta
        );

        return [
            'fecha_calculo' => $fecha->format('Y-m-d'),
            'ratios_liquidez' => $this->calculateLiquidityRatios($balanceSheet),
            'ratios_rentabilidad' => $this->calculateProfitabilityRatios(
                $balanceSheet,
                $incomeStatement
            ),
            'ratios_solvencia' => $this->calculateSolvencyRatios($balanceSheet),
            'ratios_eficiencia' => $this->calculateEfficiencyRatios(
                $balanceSheet,
                $incomeStatement
            ),
        ];
    }

    /**
     * Ratios de Liquidez (capacidad de pagar obligaciones corto plazo)
     */
    private function calculateLiquidityRatios(array $balanceSheet): array
    {
        $activos = $balanceSheet['activos']['total'];
        $pasivos = $balanceSheet['pasivos']['total'];

        // Razón corriente = Activos circulantes / Pasivos circulantes
        $razonCorriente = $pasivos != 0 ? $activos / $pasivos : 0;

        return [
            'razon_corriente' => round($razonCorriente, 2),
            'interpretacion_razon_corriente' => $this->interpretarRazonCorriente($razonCorriente),
            'capital_trabajo' => round($activos - $pasivos, 2),
            'indice_liquidez' => $pasivos != 0 ? round(($activos / $pasivos) * 100, 2) : 0,
        ];
    }

    /**
     * Ratios de Rentabilidad (capacidad de generar ganancias)
     */
    private function calculateProfitabilityRatios(
        array $balanceSheet,
        array $incomeStatement
    ): array {
        $activos = $balanceSheet['activos']['total'];
        $patrimonio = $balanceSheet['patrimonio']['total'];
        $utilidad = $incomeStatement['utilidad_operacional'];
        $ingresos = $incomeStatement['ingresos']['total'];

        // ROA: Rentabilidad sobre Activos
        $roa = $activos != 0 ? ($utilidad / $activos) * 100 : 0;

        // ROE: Rentabilidad sobre Patrimonio
        $roe = $patrimonio != 0 ? ($utilidad / $patrimonio) * 100 : 0;

        // Margen de Utilidad
        $margenUtilidad = $ingresos != 0 ? ($utilidad / $ingresos) * 100 : 0;

        return [
            'roa' => round($roa, 2),
            'roe' => round($roe, 2),
            'margen_utilidad' => round($margenUtilidad, 2),
            'interpretacion' => [
                'roa' => $this->interpretarRentabilidad($roa),
                'roe' => $this->interpretarRentabilidad($roe),
            ],
        ];
    }

    /**
     * Ratios de Solvencia (capacidad de pagar deudas largo plazo)
     */
    private function calculateSolvencyRatios(array $balanceSheet): array
    {
        $activos = $balanceSheet['activos']['total'];
        $pasivos = $balanceSheet['pasivos']['total'];
        $patrimonio = $balanceSheet['patrimonio']['total'];

        // Índice de Endeudamiento
        $endeudamiento = $activos != 0 ? ($pasivos / $activos) * 100 : 0;

        // Índice de Autonomía
        $autonomia = $activos != 0 ? ($patrimonio / $activos) * 100 : 0;

        // Cobertura de Deuda
        $cobertura = $patrimonio != 0 ? $pasivos / $patrimonio : 0;

        return [
            'indice_endeudamiento' => round($endeudamiento, 2),
            'indice_autonomia' => round($autonomia, 2),
            'razon_deuda_patrimonio' => round($cobertura, 2),
            'interpretacion' => [
                'endeudamiento' => $this->interpretarEndeudamiento($endeudamiento),
                'autonomia' => $autonomia >= 50 ? 'Buena' : 'Debe mejorar',
            ],
        ];
    }

    /**
     * Ratios de Eficiencia (uso de activos)
     */
    private function calculateEfficiencyRatios(
        array $balanceSheet,
        array $incomeStatement
    ): array {
        $activos = $balanceSheet['activos']['total'];
        $ingresos = $incomeStatement['ingresos']['total'];
        $gastos = $incomeStatement['gastos']['total'];

        // Rotación de Activos
        $rotacion = $activos != 0 ? $ingresos / $activos : 0;

        // Índice de Gastos
        $indiceGastos = $ingresos != 0 ? ($gastos / $ingresos) * 100 : 0;

        return [
            'rotacion_activos' => round($rotacion, 2),
            'indice_gastos' => round($indiceGastos, 2),
            'ingreso_por_activo' => round($rotacion, 2),
        ];
    }

    /**
     * Interpretaciones
     */
    private function interpretarRazonCorriente(float $razon): string
    {
        if ($razon >= 1.5) {
            return 'Excelente - Buena capacidad para pagar deudas';
        } elseif ($razon >= 1.0) {
            return 'Bueno - Puede cubrir sus obligaciones';
        } elseif ($razon >= 0.5) {
            return 'Aceptable - Requiere vigilancia';
        } else {
            return 'Crítico - Riesgo de insolvencia';
        }
    }

    private function interpretarRentabilidad(float $rentabilidad): string
    {
        if ($rentabilidad >= 15) {
            return 'Excelente';
        } elseif ($rentabilidad >= 10) {
            return 'Buena';
        } elseif ($rentabilidad >= 5) {
            return 'Aceptable';
        } else {
            return 'Deficiente';
        }
    }

    private function interpretarEndeudamiento(float $endeudamiento): string
    {
        if ($endeudamiento <= 30) {
            return 'Bajo - Bajo riesgo';
        } elseif ($endeudamiento <= 60) {
            return 'Moderado - Equilibrio aceptable';
        } else {
            return 'Alto - Alto riesgo financiero';
        }
    }
}
