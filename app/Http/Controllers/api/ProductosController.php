<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Models\Categoria;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\InventarioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductosController extends Controller
{
    public function __construct(private readonly InventarioService $inventario) {}

    /**
     * Filtros opcionales: ?buscar= (nombre o SKU), ?categoria_id=, ?proveedor_id=, ?talla=, ?color=, ?stock_bajo=5, ?activo=1|0
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['activo' => ['nullable', 'boolean']]);

        $productos = $this->consulta()
            ->when($request->filled('activo'), fn ($query) => $query->where('activo', $request->boolean('activo')))
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
        $producto = DB::transaction(function () use ($request) {
            $producto = Producto::create($request->validated());
            $this->inventario->registrar($producto, MovimientoStock::INICIAL, $producto->stock);

            return $producto;
        });

        return response()->json(['producto' => new ProductoResource($producto->load('categoria', 'proveedor'))], 201);
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
        DB::transaction(function () use ($request, $producto) {
            $stockAnterior = $producto->stock;
            $producto->update($request->validated());

            // Un cambio directo del stock queda registrado como ajuste manual
            $this->inventario->registrar($producto, MovimientoStock::AJUSTE, $producto->stock - $stockAnterior);
        });

        return response()->json(['producto' => new ProductoResource($producto->load('categoria', 'proveedor'))]);
    }

    /**
     * Suma unidades al stock (ingreso de mercancia). Opcionalmente registra el proveedor y una nota.
     */
    public function reabastecer(Request $request, Producto $producto): JsonResponse
    {
        $datos = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:100000'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedores,id'],
            'nota' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(fn () => $this->inventario->reabastecer(
            $producto,
            $datos['cantidad'],
            $datos['proveedor_id'] ?? null,
            $datos['nota'] ?? null,
        ));

        return response()->json(['producto' => new ProductoResource($producto->load('categoria', 'proveedor'))]);
    }

    /**
     * Historial de cambios de stock del producto, del mas reciente al mas antiguo. Filtro opcional: ?tipo=
     */
    public function movimientos(Request $request, Producto $producto): JsonResponse
    {
        $request->validate(['tipo' => ['nullable', Rule::in(MovimientoStock::TIPOS)]]);

        $movimientos = $producto->movimientos()
            ->with('proveedor:id,nombre', 'usuario:id,name')
            ->when($request->query('tipo'), fn ($query, $tipo) => $query->where('tipo', $tipo))
            ->latest()
            ->latest('id');

        return $this->listar($request, $movimientos, 'movimientos');
    }

    public function destroy(Producto $producto): JsonResponse
    {
        // La llave foranea borra en cascada: se impide para no perder el historial de ventas
        if ($producto->ventas()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar el producto porque tiene ventas registradas. Puedes desactivarlo con "activo": false.',
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
