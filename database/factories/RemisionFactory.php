<?php

namespace Database\Factories;

use App\Models\Afiliado;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Remision>
 */
class RemisionFactory extends Factory
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
            'numero' => fake()->unique()->numberBetween(1, 99999),
            'fecha' => fake()->date(),
            'dias_liquidar' => 30,
            'mensajeria' => fake()->numberBetween(0, 50000),
            'intereses' => fake()->numberBetween(0, 100000),
            'total' => fake()->numberBetween(100000, 500000),
        ];
    }
}
