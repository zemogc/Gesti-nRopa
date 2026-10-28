<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['categorias' => Categoria::orderBy('nombre')->get()]);
    }

    public function store(CategoriaRequest $request): JsonResponse
    {
        $categoria = Categoria::create($request->validated());

        return response()->json(['categoria' => $categoria], 201);
    }

    public function show(Categoria $categoria): JsonResponse
    {
        return response()->json(['categoria' => $categoria]);
    }

    public function update(CategoriaRequest $request, Categoria $categoria): JsonResponse
    {
        $categoria->update($request->validated());

        return response()->json(['categoria' => $categoria]);
    }

    public function destroy(Categoria $categoria): JsonResponse
    {
        $categoria->delete();

        return response()->json([
            'success' => true,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }
}
