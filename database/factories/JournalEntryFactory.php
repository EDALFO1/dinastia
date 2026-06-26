<?php

namespace Database\Factories;

use App\Domains\Accounting\Models\JournalEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalEntryFactory extends Factory
{
    protected $model = JournalEntry::class;

    public function definition(): array
    {
        return [
            'numero_asiento' => $this->faker->unique()->numerify('202606-######'),
            'fecha' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'descripcion' => $this->faker->sentence(),
            'referencia_documento' => $this->faker->optional()->word(),
            'tipo_documento' => $this->faker->randomElement(['factura', 'recibo', 'comprobante', null]),
            'estado' => 'borrador',
            'usuario_creacion_id' => null,
        ];
    }

    public function posteado(): static
    {
        return $this->state([
            'estado' => 'posteado',
            'fecha_aprobacion' => now(),
            'usuario_aprobacion_id' => 1,
        ]);
    }

    public function rechazado(): static
    {
        return $this->state([
            'estado' => 'rechazado',
        ]);
    }
}
