<?php

namespace App\Http\Requests;

class ProveedorRequest extends ApiRequest
{
    protected function reglas(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'contacto' => ['required', 'string', 'max:255'],
        ];
    }
}
