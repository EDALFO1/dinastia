<?php

namespace App\Domains\Payroll\Enums;

enum TipoNovedad: string
{
    case SALARIO_ORDINARIO = 'salario_ordinario';
    case SALARIO_INTEGRAL = 'salario_integral';
    case AUXILIO_TRANSPORTE = 'auxilio_transporte';
    case BONIFICACION = 'bonificacion';
    case COMISION = 'comision';
    case PRIMA = 'prima';
    case CESANTIA = 'cesantia';
    case VACACIONES = 'vacaciones';

    // Descuentos
    case DEDUCCION_VOLUNTARIA = 'deduccion_voluntaria';
    case EMBARGO = 'embargo';
    case CREDITO = 'credito';

    // Ausencias
    case INCAPACIDAD = 'incapacidad';
    case LICENCIA_NO_REMUNERADA = 'licencia_no_remunerada';
    case PERMISO = 'permiso';
    case AUSENCIA = 'ausencia';

    // Cambios de estado
    case RETIRO = 'retiro';
    case ENTRADA = 'entrada';
    case SUSPENSION = 'suspension';
    case REINCORPORACION = 'reincorporacion';

    public function label(): string
    {
        return match ($this) {
            self::SALARIO_ORDINARIO => 'Salario Ordinario',
            self::SALARIO_INTEGRAL => 'Salario Integral',
            self::AUXILIO_TRANSPORTE => 'Auxilio de Transporte',
            self::BONIFICACION => 'Bonificación',
            self::COMISION => 'Comisión',
            self::PRIMA => 'Prima',
            self::CESANTIA => 'Cesantía',
            self::VACACIONES => 'Vacaciones',
            self::DEDUCCION_VOLUNTARIA => 'Deducción Voluntaria',
            self::EMBARGO => 'Embargo',
            self::CREDITO => 'Crédito',
            self::INCAPACIDAD => 'Incapacidad',
            self::LICENCIA_NO_REMUNERADA => 'Licencia No Remunerada',
            self::PERMISO => 'Permiso',
            self::AUSENCIA => 'Ausencia',
            self::RETIRO => 'Retiro',
            self::ENTRADA => 'Entrada',
            self::SUSPENSION => 'Suspensión',
            self::REINCORPORACION => 'Reincorporación',
        };
    }
}
