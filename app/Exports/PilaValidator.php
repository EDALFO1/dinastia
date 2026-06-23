<?php

namespace App\Services;

class PilaValidator
{
    public function validar($recibos)
    {
        $errores = [];

        foreach ($recibos as $r) {

            $a = $r->afiliado;

            // 🔴 DOCUMENTO
            if(!$a->numero_documento){
                $errores[] = "Afiliado {$a->id} sin documento";
            }

            // 🔴 NOMBRES
            if(!$a->primer_nombre || !$a->primer_apellido){
                $errores[] = "Afiliado {$a->numero_documento} sin nombre completo";
            }

            // 🔴 IBC
            if(!$r->ibc || $r->ibc <= 0){
                $errores[] = "IBC inválido en {$a->numero_documento}";
            }

            // 🔴 DÍAS
            if($r->dias_liquidar < 1 || $r->dias_liquidar > 30){
                $errores[] = "Días inválidos en {$a->numero_documento}";
            }

            // 🔴 EPS / PENSIÓN
            if($r->valor_eps <= 0){
                $errores[] = "EPS en 0 en {$a->numero_documento}";
            }

            if($r->valor_pension <= 0){
                $errores[] = "Pensión en 0 en {$a->numero_documento}";
            }

            // 🔴 NOVEDAD RETIRO
            if($r->novedad == 'Retiro' && !$r->fecha_retiro){
                $errores[] = "Retiro sin fecha {$a->numero_documento}";
            }

            // 🔴 VALIDAR MES RETIRO
            if($r->novedad == 'Retiro'){
                $mesAnterior = now()->subMonth()->format('Y-m');

                if(!str_contains($r->fecha_retiro, $mesAnterior)){
                    $errores[] = "Retiro fuera de periodo {$a->numero_documento}";
                }
            }

            // 🔴 TOPES IBC (25 SMMLV)
            if($r->ibc > 25 * 1300000){ // puedes parametrizar esto
                $errores[] = "IBC supera tope legal {$a->numero_documento}";
            }

        }

        return $errores;
    }
}