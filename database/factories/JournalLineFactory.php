<?php

namespace Database\Factories;

use App\Domains\Accounting\Models\JournalLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalLineFactory extends Factory
{
    protected $model = JournalLine::class;

    public function definition(): array
    {
        return [
            'descripcion' => $this->faker->optional()->sentence(),
            'tipo_movimiento' => $this->faker->randomElement(['debito', 'credito']),
            'valor' => $this->faker->numberBetween(10000, 1000000),
        ];
    }

    public function debito(): static
    {
        return $this->state([
            'tipo_movimiento' => 'debito',
        ]);
    }

    public function credito(): static
    {
        return $this->state([
            'tipo_movimiento' => 'credito',
        ]);
    }
}
