<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubtipoCotizante>
 */
class SubtipoCotizanteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->numerify('SUBTIPO-###'),
            'nombre' => fake()->randomElement(['Independiente', 'Contratista', 'Empleado', 'Servidor Público']),
        ];
    }
}
