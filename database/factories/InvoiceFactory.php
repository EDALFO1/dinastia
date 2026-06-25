<?php

namespace Database\Factories;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Enums\InvoiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(1000, 100000) / 100 * 100;
        $descuento = $this->faker->optional(0.7)->numberBetween(0, $subtotal / 4) / 100 * 100 ?? 0;
        $iva = ($subtotal - $descuento) * 0.19;
        $total = $subtotal - $descuento + $iva;

        return [
            'empresa_id' => \App\Models\Empresa::factory(),
            'invoice_sequence_id' => \Database\Factories\InvoiceSequenceFactory::new(),
            'numero' => $this->faker->unique()->numberBetween(1000, 999999),
            'tipo_documento' => InvoiceType::FACTURA,
            'cliente_nit' => $this->faker->numerify('##########'),
            'cliente_nombre' => $this->faker->company(),
            'fecha_emision' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(30)->toDateString(),
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'total_impuestos' => $iva,
            'total' => $total,
            'observaciones' => $this->faker->optional()->sentence(),
            'estado' => 'borrador',
        ];
    }

    public function sent(): self
    {
        return $this->state(function () {
            return [
                'estado' => 'enviada',
                'uuid_dian' => $this->faker->uuid(),
            ];
        });
    }

    public function accepted(): self
    {
        return $this->state(function () {
            return [
                'estado' => 'aceptada',
                'uuid_dian' => $this->faker->uuid(),
            ];
        });
    }
}
