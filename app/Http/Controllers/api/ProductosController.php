<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductosController extends Controller
{
    /**
     * Filtros opcionales: ?buscar=, ?categoria_id=, ?stock_bajo=5
     */
    public function index(Request $request): JsonResponse
    {
        $productos = $this->consulta()
            ->when($request->query('buscar'), fn ($query, $buscar) => $query->where('nombre', 'like', "%{$buscar}%"))
            ->when($request->query('categoria_id'), fn ($query, $categoria) => $query->where('categoria_id', $categoria))
            ->when($request->filled('stock_bajo'), fn ($query) => $query->stockBajo($request->integer('stock_bajo')))
            ->get();

        return response()->json(['productos' => ProductoResource::collection($productos)]);
    }

    public function store(ProductoRequest $request): JsonResponse
    {
        $producto = Producto::create($request->validated())->load('categoria');

        return response()->json(['producto' => new ProductoResource($producto)], 201);
    }

    public function show(Producto $producto): JsonResponse
    {
        return response()->json([
            'producto' => new ProductoResource($producto->load('categoria')),
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function update(ProductoRequest $request, Producto $producto): JsonResponse
    {
        $producto->update($request->validated());

        return response()->json(['producto' => new ProductoResource($producto->load('categoria'))]);
    }

    public function destroy(Producto $producto): JsonResponse
    {
        $producto->delete();

        return response()->json([
            'success' => true,
            'productos' => ProductoResource::collection($this->consulta()->get()),
        ]);
    }

    private function consulta(): Builder
    {
        return Producto::with('categoria')->orderBy('nombre');
    }
}
