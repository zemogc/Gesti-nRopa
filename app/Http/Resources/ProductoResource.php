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
            'sku' => $this->sku,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'talla' => $this->talla,
            'color' => $this->color,
            'precio' => $this->precio,
            'stock' => $this->stock,
            'activo' => $this->activo,
            'categoria_id' => $this->categoria_id,
            // Nombre plano de la categoria, como lo devolvia la API original
            'categoria' => $this->whenLoaded('categoria', fn () => $this->categoria?->nombre),
            'proveedor_id' => $this->proveedor_id,
            'proveedor' => $this->whenLoaded('proveedor', fn () => $this->proveedor?->nombre),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
