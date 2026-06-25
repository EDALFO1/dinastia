<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Documento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmpresaLaboral>
 */
class EmpresaLaboralFactory extends Factory
{
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'documento_id' => Documento::factory(),
            'numero_documento' => fake()->numerify('##########'),
            'nombre' => fake()->company(),
        ];
    }
}
