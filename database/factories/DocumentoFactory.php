<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Documento>
 */
class DocumentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->numerify('DOC-###'),
            'nombre' => fake()->randomElement(['Cédula de Ciudadanía', 'Pasaporte', 'Cédula de Extranjería', 'Tarjeta de Identidad']),
        ];
    }
}
