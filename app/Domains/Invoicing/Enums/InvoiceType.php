<?php

namespace App\Domains\Invoicing\Enums;

enum InvoiceType: string
{
    case FACTURA = 'factura';
    case NOTA_CREDITO = 'nota_credito';
    case NOTA_DEBITO = 'nota_debito';
}
