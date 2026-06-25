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
            'numero' => fake()->numberBetween(1000, 9999),
            'fecha' => fake()->date(),
            'mensajeria' => 0,
            'intereses' => 0,
            'dias_liquidar' => 30,
            'observaciones' => fake()->sentence(),
        ];
    }
}
