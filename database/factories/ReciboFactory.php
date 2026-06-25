<?php

namespace Database\Factories;

use App\Models\Afiliado;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recibo>
 */
class ReciboFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ibc = fake()->numberBetween(1000000, 5000000);
        $eps = round($ibc * 0.04);
        $arl = round($ibc * 0.01);
        $pension = round($ibc * 0.16);
        $caja = round($ibc * 0.04);
        $admon = round($ibc * 0.04);

        return [
            'empresa_id' => Empresa::factory(),
            'afiliado_id' => Afiliado::factory(),
            'numero' => fake()->unique()->numberBetween(1, 99999),
            'fecha' => fake()->date(),
            'dias_liquidar' => 30,
            'ibc' => $ibc,
            'valor_eps' => $eps,
            'valor_arl' => $arl,
            'valor_pension' => $pension,
            'valor_caja' => $caja,
            'valor_admon' => $admon,
            'valor_servicios' => 0,
            'total' => $ibc - $eps - $arl - $pension - $caja - $admon,
            'novedad' => null,
            'fecha_retiro' => null,
        ];
    }
}
