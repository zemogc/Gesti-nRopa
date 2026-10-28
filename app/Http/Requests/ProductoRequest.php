<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => [
                'nullable', 'string', 'max:50', 'alpha_dash:ascii',
                Rule::unique('productos', 'sku')->ignore($this->route('producto')),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string', 'max:1000'],
            'talla' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:30'],
            // decimal(12, 2) en la base de datos
            'precio' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
