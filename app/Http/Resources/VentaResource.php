<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'producto_id' => $this->producto_id,
            'cliente_id' => $this->cliente_id,
            'cantidad' => $this->cantidad,
            'fecha_venta' => $this->fecha_venta?->format('Y-m-d'),
            'total' => $this->total,
            // Campos planos, como los devolvia la API original
            'producto_nombre' => $this->whenLoaded('producto', fn () => $this->producto?->nombre),
            'cliente_nombre' => $this->whenLoaded('cliente', fn () => $this->cliente?->nombre),
            'cliente_apellido' => $this->whenLoaded('cliente', fn () => $this->cliente?->apellido),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
