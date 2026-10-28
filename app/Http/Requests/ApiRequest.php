<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base de las Form Requests de la API. En PATCH todas las reglas se vuelven "sometimes",
 * de modo que solo se validan (y actualizan) los campos enviados.
 */
abstract class ApiRequest extends FormRequest
{
    /**
     * Reglas completas, tal como aplican en POST y PUT.
     */
    abstract protected function reglas(): array;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $reglas = $this->reglas();

        if (! $this->isMethod('PATCH')) {
            return $reglas;
        }

        return array_map(fn (array $regla) => ['sometimes', ...$regla], $reglas);
    }
}
