<?php

namespace Database\Factories;

use App\Models\ExportBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExportBatchFactory extends Factory
{
    protected $model = ExportBatch::class;

    public function definition(): array
    {
        return [
            'empresa_id' => 1,
            'codigo' => 'BATCH-' . fake()->numerify('####'),
            'periodo' => '202606',
            'recibos_count' => 0,
            'total' => 0,
        ];
    }
}
