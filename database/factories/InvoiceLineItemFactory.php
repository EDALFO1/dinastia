<?php

namespace Database\Factories;

use App\Domains\Invoicing\Models\InvoiceLineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceLineItemFactory extends Factory
{
    protected $model = InvoiceLineItem::class;

    public function definition(): array
    {
        $cantidad = $this->faker->randomFloat(2, 1, 100);
        $valorUnitario = $this->faker->numberBetween(1000, 50000) / 100;
        $bruto = $cantidad * $valorUnitario;
        $descuento = $this->faker->optional(0.6)->numberBetween(0, $bruto / 4) / 100 ?? 0;

        return [
            'empresa_id' => \App\Models\Empresa::factory(),
            'invoice_id' => \Database\Factories\InvoiceFactory::new(),
            'linea_numero' => $this->faker->numberBetween(1, 10),
            'descripcion' => $this->faker->sentence(),
            'cantidad' => $cantidad,
            'unidad' => $this->faker->randomElement(['unidad', 'kilogramo', 'litro']),
            'valor_unitario' => $valorUnitario,
            'descuento' => $descuento,
            'valor_linea' => $bruto - $descuento,
        ];
    }
}
