<?php

namespace Database\Factories;

use App\Domains\Invoicing\Models\InvoiceTax;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceTaxFactory extends Factory
{
    protected $model = InvoiceTax::class;

    public function definition(): array
    {
        $base = $this->faker->numberBetween(10000, 100000) / 100;
        $porcentaje = $this->faker->randomElement([0, 5, 19]); // IVA rates in Colombia

        return [
            'empresa_id' => \App\Models\Empresa::factory(),
            'invoice_id' => \Database\Factories\InvoiceFactory::new(),
            'invoice_line_item_id' => null,
            'tipo_impuesto' => $this->faker->randomElement(['iva', 'impuesto_consumo']),
            'porcentaje' => $porcentaje,
            'base' => $base,
            'valor' => ($base * $porcentaje) / 100,
        ];
    }

    public function iva(): self
    {
        return $this->state(function () {
            return [
                'tipo_impuesto' => 'iva',
                'porcentaje' => 19,
            ];
        });
    }

    public function exempted(): self
    {
        return $this->state(function () {
            return [
                'tipo_impuesto' => 'iva',
                'porcentaje' => 0,
            ];
        });
    }
}
