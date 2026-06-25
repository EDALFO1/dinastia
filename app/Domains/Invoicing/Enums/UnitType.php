<?php

namespace App\Domains\Invoicing\Enums;

enum UnitType: string
{
    case UNIDAD = 'unidad';
    case KILOGRAMO = 'kilogramo';
    case GRAMO = 'gramo';
    case METRO = 'metro';
    case CENTIMETRO = 'centimetro';
    case HORA = 'hora';
    case MINUTO = 'minuto';
    case LITRO = 'litro';
    case MILILITRO = 'mililitro';
}
