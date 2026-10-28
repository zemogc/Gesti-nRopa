<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'stock' => $this->stock,
            'categoria_id' => $this->categoria_id,
            // Nombre plano de la categoria, como lo devolvia la API original
            'categoria' => $this->whenLoaded('categoria', fn () => $this->categoria?->nombre),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
