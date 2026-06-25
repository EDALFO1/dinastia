<?php

namespace App\Domains\Invoicing\Services;

class DianValidator
{
    public function validarNit(string $nit): bool
    {
        $nit = preg_replace('/[^0-9]/', '', $nit);

        return strlen($nit) >= 6 && strlen($nit) <= 11;
    }

    public function validarCedula(string $cedula): bool
    {
        $cedula = preg_replace('/[^0-9]/', '', $cedula);

        if (strlen($cedula) !== 10) {
            return false;
        }

        $tipos = [1, 2, 3];
        $primerDigito = (int) substr($cedula, 0, 1);

        if (!in_array($primerDigito, $tipos)) {
            return false;
        }

        $suma = 0;
        $pesos = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43];

        for ($i = 0; $i < 10; $i++) {
            $valor = ((int) $cedula[$i]) * $pesos[$i];
            $dac = (int) ($valor / 11);
            $residuo = $valor % 11;
            $digito = $dac + $residuo;
            $suma += (int) ($digito % 10);
        }

        return ($suma % 10) === 0;
    }

    public function validarReferenciaTributaria(string $ref): bool
    {
        return preg_match('/^[A-Z0-9\-]{3,20}$/', $ref) === 1;
    }

    public function validarCodigoProducto(string $codigo): bool
    {
        return !empty($codigo) && strlen($codigo) <= 50 && preg_match('/^[A-Z0-9\-\.]+$/i', $codigo) === 1;
    }

    public function validarUnidadMedida(string $unidad): bool
    {
        $unidadesValidas = [
            'unidad', 'kilogramo', 'gramo', 'metro', 'centimetro',
            'hora', 'minuto', 'litro', 'mililitro'
        ];

        return in_array(strtolower($unidad), $unidadesValidas);
    }

    public function validarTipoImpuesto(string $tipo): bool
    {
        $tiposValidos = ['iva', 'impuesto_consumo', 'impuesto_nacional'];

        return in_array(strtolower($tipo), $tiposValidos);
    }

    public function validarPorcentajeIVA(float $porcentaje): bool
    {
        $porcentajesValidos = [0, 5, 19];

        return in_array($porcentaje, $porcentajesValidos);
    }
}
