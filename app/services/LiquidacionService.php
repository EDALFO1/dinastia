<?php

namespace App\Services;

class LiquidacionService
{
    public function calcular($afiliado, $dias = 30)
    {
        $ibc = $this->calcularIBC($afiliado);

        $eps = $this->calcularEPS($ibc, $dias);
        $arl = $this->calcularARL($afiliado, $ibc, $dias);
        $pension = $this->calcularPension($ibc, $dias);
        $caja = $this->calcularCaja($ibc, $dias);

        $administracion = $this->calcularAdministracion($afiliado);
        $servicios = $this->calcularServicios($afiliado);

        $total = $eps + $arl + $pension + $caja + $administracion + $servicios;

        return [
            'ibc' => $ibc,
            'eps' => $eps,
            'arl' => $arl,
            'pension' => $pension,
            'caja' => $caja,
            'administracion' => $administracion,
            'servicios' => $servicios,
            'total' => $total
        ];
    }

    private function calcularIBC($afiliado)
    {
        return $afiliado->ibc;
    }

    private function calcularEPS($ibc, $dias)
    {
        $porcentaje = 0.04;

        return round(($ibc * $porcentaje / 30) * $dias);
    }

    private function calcularPension($ibc, $dias)
    {
        $porcentaje = 0.16;

        return round(($ibc * $porcentaje / 30) * $dias);
    }

    private function calcularCaja($ibc, $dias)
    {
        $porcentaje = 0.04;

        return round(($ibc * $porcentaje / 30) * $dias);
    }

    private function calcularARL($afiliado, $ibc, $dias)
    {
        if (!$afiliado->afiliacion || !$afiliado->afiliacion->arl) {
            return 0;
        }

        $porcentaje = $afiliado->afiliacion->arl->porcentaje / 100;

        return round(($ibc * $porcentaje / 30) * $dias);
    }

    private function calcularAdministracion($afiliado)
    {
        return $afiliado->administracion ?? 0;
    }

    private function calcularServicios($afiliado)
    {
        $total = 0;

        foreach ($afiliado->servicios as $servicio) {
            if ($servicio->estado) {
                $total += $servicio->valor;
            }
        }

        return $total;
    }
}