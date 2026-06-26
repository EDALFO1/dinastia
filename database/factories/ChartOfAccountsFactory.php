<?php

namespace Database\Factories;

use App\Domains\Accounting\Models\ChartOfAccounts;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChartOfAccountsFactory extends Factory
{
    protected $model = ChartOfAccounts::class;

    public function definition(): array
    {
        $tipos = ['activo', 'pasivo', 'patrimonio', 'ingresos', 'gastos', 'costo'];
        $tipo = $this->faker->randomElement($tipos);

        return [
            'codigo' => $this->faker->numerify('######'),
            'nombre' => ucwords($this->faker->words(3, true)),
            'descripcion' => $this->faker->sentence(),
            'tipo_cuenta' => $tipo,
            'nivel' => $this->faker->numberBetween(1, 4),
            'saldo_inicial' => $this->faker->numberBetween(0, 1000000),
            'estado' => 'activo',
            'permite_movimiento' => $this->faker->boolean(80),
            'orden' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function activo(): static
    {
        return $this->state([
            'tipo_cuenta' => 'activo',
            'permite_movimiento' => true,
        ]);
    }

    public function pasivo(): static
    {
        return $this->state([
            'tipo_cuenta' => 'pasivo',
            'permite_movimiento' => true,
        ]);
    }

    public function gasto(): static
    {
        return $this->state([
            'tipo_cuenta' => 'gastos',
            'permite_movimiento' => true,
        ]);
    }

    public function ingreso(): static
    {
        return $this->state([
            'tipo_cuenta' => 'ingresos',
            'permite_movimiento' => true,
        ]);
    }

    public function inactiva(): static
    {
        return $this->state([
            'estado' => 'inactivo',
        ]);
    }
}
