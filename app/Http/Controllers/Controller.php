<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class Controller
{
    /**
     * Responde un listado bajo la clave indicada. Si la peticion trae ?por_pagina=N
     * se pagina (con ?pagina=M) y se agrega la clave "paginacion"; si no, se devuelve todo.
     *
     * @param  class-string<JsonResource>|null  $resource
     */
    protected function listar(Request $request, Builder $consulta, string $clave, ?string $resource = null): JsonResponse
    {
        $request->validate([
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
            'pagina' => ['nullable', 'integer', 'min:1'],
        ]);

        $formatear = fn ($items) => $resource ? $resource::collection($items) : $items;

        if (! $request->filled('por_pagina')) {
            return response()->json([$clave => $formatear($consulta->get())]);
        }

        $pagina = $consulta->paginate($request->integer('por_pagina'), pageName: 'pagina');

        return response()->json([
            $clave => $formatear($pagina->getCollection()),
            'paginacion' => [
                'pagina_actual' => $pagina->currentPage(),
                'por_pagina' => $pagina->perPage(),
                'total' => $pagina->total(),
                'ultima_pagina' => $pagina->lastPage(),
            ],
        ]);
    }
}
