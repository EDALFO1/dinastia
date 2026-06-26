<?php

namespace App\Domains\Accounting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_asiento' => $this->numero_asiento,
            'fecha' => $this->fecha->format('Y-m-d'),
            'descripcion' => $this->descripcion,
            'referencia_documento' => $this->referencia_documento,
            'tipo_documento' => $this->tipo_documento,
            'estado' => $this->estado,
            'total_debito' => (float) $this->getTotalDebit(),
            'total_credito' => (float) $this->getTotalCredit(),
            'balanceado' => $this->isBalanced(),
            'usuario_creacion' => $this->whenLoaded('usuarioCreacion', fn () => [
                'id' => $this->usuarioCreacion->id,
                'nombre' => $this->usuarioCreacion->nombre,
            ]),
            'usuario_aprobacion' => $this->whenLoaded('usuarioAprobacion', fn () => [
                'id' => $this->usuarioAprobacion->id,
                'nombre' => $this->usuarioAprobacion->nombre,
            ]),
            'fecha_aprobacion' => $this->fecha_aprobacion?->format('Y-m-d H:i:s'),
            'lineas' => JournalLineResource::collection($this->whenLoaded('lines')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
