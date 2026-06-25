<?php

namespace App\Domains\Invoicing\Enums;

enum TaxType: string
{
    case IVA = 'iva';
    case IMPUESTO_CONSUMO = 'impuesto_consumo';
    case IMPUESTO_NACIONAL = 'impuesto_nacional';
}
