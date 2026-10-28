<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ClienteRequest extends ApiRequest
{
    protected function reglas(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('clientes', 'email')->ignore($this->route('cliente')),
            ],
            'telefono' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
        ];
    }
}
