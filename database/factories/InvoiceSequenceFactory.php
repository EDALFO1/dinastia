<?php

namespace Database\Factories;

use App\Domains\Invoicing\Models\InvoiceSequence;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceSequenceFactory extends Factory
{
    protected $model = InvoiceSequence::class;

    public function definition(): array
    {
        $inicio = $this->faker->numberBetween(1000, 5000);
        $fin = $inicio + 9999;

        return [
            'empresa_id' => \App\Models\Empresa::factory(),
            'numero_resolucion' => 'RES-' . $this->faker->numerify('############'),
            'tipo_factura' => $this->faker->randomElement(['factura', 'nota_credito', 'nota_debito']),
            'rango_inicio' => $inicio,
            'rango_fin' => $fin,
            'proximo_numero' => $inicio,
            'fecha_vigencia_inicio' => now()->subMonths(3)->toDateString(),
            'fecha_vigencia_fin' => now()->addMonths(12)->toDateString(),
            'estado' => 'activa',
        ];
    }

    public function expired(): self
    {
        return $this->state(function () {
            return [
                'estado' => 'vencida',
                'fecha_vigencia_fin' => now()->subDay()->toDateString(),
            ];
        });
    }

    public function suspended(): self
    {
        return $this->state(function () {
            return [
                'estado' => 'suspendida',
            ];
        });
    }
}
