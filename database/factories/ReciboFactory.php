<?php

namespace Database\Factories;

use App\Models\Afiliado;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recibo>
 */
class ReciboFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'afiliado_id' => Afiliado::factory(),
            'numero' => fake()->numerify('REC-######'),
            'periodo' => fake()->date('Y-m'),
            'salario' => fake()->numberBetween(1000000, 5000000),
            'salario_neto' => fake()->numberBetween(800000, 4000000),
            'aporte_eps' => fake()->numberBetween(50000, 200000),
            'aporte_arl' => fake()->numberBetween(30000, 100000),
            'aporte_pension' => fake()->numberBetween(100000, 400000),
            'estado' => 'generado',
        ];
    }
}
