<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Documento;
use App\Models\SubtipoCotizante;
use App\Models\EmpresaLaboral;
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
            'empresa_laboral_id' => EmpresaLaboral::factory(),
            'asesor_id' => null,
            'documento_id' => Documento::factory(),
            'subtipo_cotizante_id' => SubtipoCotizante::factory(),
            'numero_documento' => fake()->numerify('##########'),
            'primer_nombre' => fake()->firstName(),
            'segundo_nombre' => fake()->optional()->firstName(),
            'primer_apellido' => fake()->lastName(),
            'segundo_apellido' => fake()->optional()->lastName(),
            'fecha_nacimiento' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'sexo' => fake()->randomElement(['M', 'F', 'Otro']),
            'correo' => fake()->optional()->email(),
            'telefono' => fake()->optional()->phoneNumber(),
            'direccion' => fake()->optional()->address(),
            'ciudad' => fake()->optional()->city(),
            'estado' => true,
        ];
    }
}
