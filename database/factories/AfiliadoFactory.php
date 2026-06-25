<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Afiliado>
 */
class AfiliadoFactory extends Factory
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
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'numero_documento' => fake()->numerify('##########'),
            'tipo_documento' => 'CC',
            'estado' => 'activo',
            'fecha_ingreso' => fake()->date(),
            'salario' => fake()->numberBetween(1000000, 5000000),
        ];
    }
}
