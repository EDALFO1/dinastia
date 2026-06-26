<?php

namespace App\Domains\Accounting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'cuenta' => $this->whenLoaded('account', function () {
                return [
                    'id' => $this->account->id,
                    'codigo' => $this->account->codigo,
                    'nombre' => $this->account->nombre,
                    'tipo' => $this->account->tipo_cuenta->value,
                ];
            }),
            'descripcion' => $this->descripcion,
            'tipo_movimiento' => $this->tipo_movimiento,
            'valor' => (float) $this->valor,
            'centro_costo_id' => $this->centro_costo_id,
            'referencia_documento' => $this->referencia_documento,
            'created_at' => $this->created_at,
        ];
    }
}
