<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string', 'max:1000'],
            // decimal(8, 2) en la base de datos
            'precio' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
