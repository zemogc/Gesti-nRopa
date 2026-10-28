<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CategoriaRequest extends ApiRequest
{
    protected function reglas(): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:50',
                Rule::unique('categorias', 'nombre')->ignore($this->route('categoria')),
            ],
            'descripcion' => ['required', 'string', 'max:1000'],
        ];
    }
}
