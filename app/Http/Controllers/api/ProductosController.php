<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductosController extends Controller
{
    /**
     * Filtros opcionales: ?buscar= (nombre o SKU), ?categoria_id=, ?proveedor_id=, ?talla=, ?color=, ?stock_bajo=5
     */
    public function index(Request $request): JsonResponse
    {
        $productos = $this->consulta()
            ->when($request->query('buscar'), fn ($query, $buscar) => $query->where(
                fn ($query) => $query->where('nombre', 'like', "%{$buscar}%")->orWhere('sku', $buscar)
            ))
            ->when($request->query('talla'), fn ($query, $talla) => $query->where('talla', $talla))
            ->when($request->query('color'), fn ($query, $color) => $query->where('color', $color))
            ->when($request->query('categoria_id'), fn ($query, $categoria) => $query->where('categoria_id', $categoria))
            ->when($request->query('proveedor_id'), fn ($query, $proveedor) => $query->where('proveedor_id', $proveedor))
            ->when($request->filled('stock_bajo'), fn ($query) => $query->stockBajo($request->integer('stock_bajo')));

        return $this->listar($request, $productos, 'productos', ProductoResource::class);
    }

    public function store(ProductoRequest $request): JsonResponse
    {
        $producto = Producto::create($request->validated())->load('categoria', 'proveedor');

        return response()->json(['producto' => new ProductoResource($producto)], 201);
    }

    public function show(Producto $producto): JsonResponse
    {
        return response()->json([
            'producto' => new ProductoResource($producto->load('categoria', 'proveedor')),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'proveedores' => Proveedor::orderBy('nombre')->get(),
        ]);
    }

    public function update(ProductoRequest $request, Producto $producto): JsonResponse
    {
        $producto->update($request->validated());

        return response()->json(['producto' => new ProductoResource($producto->load('categoria', 'proveedor'))]);
    }

    /**
     * Suma unidades al stock (ingreso de mercancia). Opcionalmente registra el proveedor.
     */
    public function reabastecer(Request $request, Producto $producto): JsonResponse
    {
        $datos = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:100000'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
        ]);

        $producto->increment('stock', $datos['cantidad'], array_filter([
            'proveedor_id' => $datos['proveedor_id'] ?? null,
        ]));

        return response()->json(['producto' => new ProductoResource($producto->load('categoria', 'proveedor'))]);
    }

    public function destroy(Producto $producto): JsonResponse
    {
        // La llave foranea borra en cascada: se impide para no perder el historial de ventas
        if ($producto->ventas()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el producto porque tiene ventas registradas.',
            ], 409);
        }

        $producto->delete();

        return response()->json([
            'success' => true,
            'productos' => ProductoResource::collection($this->consulta()->get()),
        ]);
    }

    private function consulta(): Builder
    {
        return Producto::with('categoria', 'proveedor')->orderBy('nombre');
    }
}
