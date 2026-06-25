<?php

namespace App\Domains\Invoicing\Enums;

enum DocumentType: string
{
    case CEDULA = 'cedula';
    case NIT = 'nit';
    case PASAPORTE = 'pasaporte';
    case DOCUMENTO_EXTRANJERO = 'documento_extranjero';
}
