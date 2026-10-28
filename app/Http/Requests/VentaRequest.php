<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * El total no se recibe: se calcula a partir del precio del producto.
     */
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'fecha_venta' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }
}
