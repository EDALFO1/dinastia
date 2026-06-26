<?php

namespace App\Domains\Payroll\Services;

use App\Domains\Payroll\Enums\TipoRetencion;
use App\Domains\Payroll\Models\RetencioneNomina;

class RetencionesCalculator
{
    protected float $salarioBruto;
    protected int $reciboId;
    protected int $empresaId;

    public function __construct(float $salarioBruto, int $reciboId, int $empresaId)
    {
        $this->salarioBruto = $salarioBruto;
        $this->reciboId = $reciboId;
        $this->empresaId = $empresaId;
    }

    /**
     * Calcular retenciones (renta, solidaridad, etc)
     */
    public function calcular(): array
    {
        $retenciones = [];
        $totalRetenciones = 0;

        // Renta (si aplica)
        $renta = $this->calcularRenta();
        if ($renta > 0) {
            $retencion = RetencioneNomina::create([
                'recibo_id' => $this->reciboId,
                'tipo_retencion' => TipoRetencion::RENTA,
                'base_calculo' => $this->salarioBruto,
                'porcentaje' => $this->obtenerPorcentajeRenta(),
                'valor_retencion' => $renta,
                'concepto' => 'Retención en la fuente (Renta)',
                'empresa_id' => $this->empresaId,
            ]);
            $retenciones[] = $retencion;
            $totalRetenciones += $renta;
        }

        // Solidaridad (si aplica - dependiendo de salario)
        $solidaridad = $this->calcularSolidaridad();
        if ($solidaridad > 0) {
            $retencion = RetencioneNomina::create([
                'recibo_id' => $this->reciboId,
                'tipo_retencion' => TipoRetencion::SOLIDARIDAD,
                'base_calculo' => $this->salarioBruto,
                'porcentaje' => 1.0,
                'valor_retencion' => $solidaridad,
                'concepto' => 'Aporte de solidaridad',
                'empresa_id' => $this->empresaId,
            ]);
            $retenciones[] = $retencion;
            $totalRetenciones += $solidaridad;
        }

        return [
            'retenciones' => $retenciones,
            'total_retenciones' => $totalRetenciones,
        ];
    }

    /**
     * Calcular renta según tabla colombiana
     * Tarifa 2024 (aproximada)
     */
    private function calcularRenta(): float
    {
        if ($this->salarioBruto <= 1300000) {
            return 0;
        } elseif ($this->salarioBruto <= 2300000) {
            return ($this->salarioBruto - 1300000) * 0.19;
        } elseif ($this->salarioBruto <= 4100000) {
            return 190000 + ($this->salarioBruto - 2300000) * 0.28;
        } else {
            return 694000 + ($this->salarioBruto - 4100000) * 0.33;
        }
    }

    /**
     * Obtener porcentaje efectivo de renta
     */
    private function obtenerPorcentajeRenta(): float
    {
        if ($this->salarioBruto <= 1300000) {
            return 0;
        } elseif ($this->salarioBruto <= 2300000) {
            return 19;
        } elseif ($this->salarioBruto <= 4100000) {
            return 28;
        } else {
            return 33;
        }
    }

    /**
     * Calcular aporte de solidaridad (1% para salarios > SMLMV x 2)
     */
    private function calcularSolidaridad(): float
    {
        $salarioMinimo = 1300000; // 2024
        if ($this->salarioBruto > ($salarioMinimo * 2)) {
            return $this->salarioBruto * 0.01;
        }
        return 0;
    }
}
