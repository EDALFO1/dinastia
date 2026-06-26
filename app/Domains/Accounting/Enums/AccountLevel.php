<?php

namespace App\Domains\Accounting\Enums;

enum AccountLevel: int
{
    case NIVEL_1 = 1; // Clase (2 dígitos: 10-99)
    case NIVEL_2 = 2; // Grupo (4 dígitos: 1001-9999)
    case NIVEL_3 = 3; // Cuenta (6 dígitos: 100101-999999)
    case NIVEL_4 = 4; // Subcuenta (8 dígitos: 10010101-99999999)
    case NIVEL_5 = 5; // Detalle (10 dígitos: 1001010101-9999999999)

    public function label(): string
    {
        return match($this) {
            self::NIVEL_1 => 'Clase',
            self::NIVEL_2 => 'Grupo',
            self::NIVEL_3 => 'Cuenta',
            self::NIVEL_4 => 'Subcuenta',
            self::NIVEL_5 => 'Detalle',
        };
    }

    public function digits(): int
    {
        return match($this) {
            self::NIVEL_1 => 2,
            self::NIVEL_2 => 4,
            self::NIVEL_3 => 6,
            self::NIVEL_4 => 8,
            self::NIVEL_5 => 10,
        };
    }

    public function canHaveChildren(): bool
    {
        return $this !== self::NIVEL_5;
    }

    public static function operative(): array
    {
        return [self::NIVEL_3, self::NIVEL_4];
    }
}
