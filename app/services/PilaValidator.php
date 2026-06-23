<?php

namespace App\Services;

class PilaValidator
{
    public function validar($recibos)
    {
        $errores = [];

        foreach ($recibos as $r) {

            $a = $r->afiliado;

            if(!$a){
                $errores[] = "Recibo {$r->id} sin afiliado";
                continue;
            }

            if(!$a->numero_documento){
                $errores[] = "Afiliado {$a->id} sin documento";
            }

            if(!$a->primer_nombre || !$a->primer_apellido){
                $errores[] = "Afiliado {$a->numero_documento} sin nombre";
            }

            if(!$r->ibc || $r->ibc <= 0){
                $errores[] = "IBC inválido {$a->numero_documento}";
            }

            if($r->dias_liquidar < 1 || $r->dias_liquidar > 30){
                $errores[] = "Días inválidos {$a->numero_documento}";
            }

            if($r->novedad == 'Retiro' && !$r->fecha_retiro){
                $errores[] = "Retiro sin fecha {$a->numero_documento}";
            }

        }

        return $errores;
    }
}