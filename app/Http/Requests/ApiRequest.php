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
     * Campos que se guardan en mayusculas o minusculas, para que valores como
     * "jean-32" y "JEAN-32" no se consideren distintos (y la regla unique funcione).
     */
    protected array $mayusculas = [];

    protected array $minusculas = [];

    /**
     * Reglas completas, tal como aplican en POST y PUT.
     */
    abstract protected function reglas(): array;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizados = [];

        foreach ([...$this->mayusculas, ...$this->minusculas] as $campo) {
            if (is_string($valor = $this->input($campo))) {
                $normalizados[$campo] = in_array($campo, $this->mayusculas, true)
                    ? mb_strtoupper($valor)
                    : mb_strtolower($valor);
            }
        }

        $this->merge($normalizados);
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
