<?php

namespace Database\Factories;

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
            'numero' => fake()->numerify('REM-######'),
            'fecha' => fake()->date(),
            'tipo' => fake()->randomElement(['PILA', 'ARL', 'EPS']),
            'estado' => 'generada',
        ];
    }
}
