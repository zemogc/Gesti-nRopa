<?php

namespace App\Http\Requests;

class VentaRequest extends ApiRequest
{
    /**
     * El total no se recibe: se calcula a partir del precio del producto.
     */
    protected function reglas(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'fecha_venta' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }
}
