<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VentaRequest;
use App\Http\Resources\VentaResource;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VentasController extends Controller
{
    public function __construct(private readonly VentaService $ventas) {}

    /**
     * Filtros opcionales: ?cliente_id=, ?producto_id=, ?desde=YYYY-MM-DD, ?hasta=YYYY-MM-DD
     */
    public function index(Request $request): JsonResponse
    {
        return $this->listar($request, $this->filtrar($request), 'ventas', VentaResource::class);
    }

    /**
     * Descarga las ventas filtradas en CSV (separado por ";" y con BOM para que Excel lo abra bien).
     */
    public function exportar(Request $request): StreamedResponse
    {
        $ventas = $this->filtrar($request);

        return response()->streamDownload(function () use ($ventas) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['id', 'fecha', 'cliente', 'email', 'producto', 'sku', 'cantidad', 'total'], ';');

            foreach ($ventas->lazy() as $venta) {
                fputcsv($salida, [
                    $venta->id,
                    $venta->fecha_venta?->format('Y-m-d'),
                    trim("{$venta->cliente?->nombre} {$venta->cliente?->apellido}"),
                    $venta->cliente?->email,
                    $venta->producto?->nombre,
                    $venta->producto?->sku,
                    $venta->cantidad,
                    $venta->total,
                ], ';');
            }

            fclose($salida);
        }, 'ventas-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function store(VentaRequest $request): JsonResponse
    {
        $venta = $this->ventas->registrar($request->validated());

        return response()->json(['venta' => new VentaResource($venta->load('producto', 'cliente'))], 201);
    }

    public function show(Venta $venta): JsonResponse
    {
        return response()->json([
            'venta' => new VentaResource($venta->load('producto', 'cliente')),
            'productos' => Producto::orderBy('nombre')->get(),
            'clientes' => Cliente::orderBy('apellido')->orderBy('nombre')->get(),
        ]);
    }

    public function update(VentaRequest $request, Venta $venta): JsonResponse
    {
        $venta = $this->ventas->actualizar($venta, $request->validated());

        return response()->json(['venta' => new VentaResource($venta->load('producto', 'cliente'))]);
    }

    public function destroy(Venta $venta): JsonResponse
    {
        $this->ventas->anular($venta);

        return response()->json([
            'success' => true,
            'ventas' => VentaResource::collection($this->consulta()->get()),
        ]);
    }

    private function filtrar(Request $request): Builder
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        return $this->consulta()
            ->when($request->query('cliente_id'), fn ($query, $cliente) => $query->where('cliente_id', $cliente))
            ->when($request->query('producto_id'), fn ($query, $producto) => $query->where('producto_id', $producto))
            ->when($request->query('desde'), fn ($query, $desde) => $query->whereDate('fecha_venta', '>=', $desde))
            ->when($request->query('hasta'), fn ($query, $hasta) => $query->whereDate('fecha_venta', '<=', $hasta));
    }

    private function consulta(): Builder
    {
        return Venta::with('producto', 'cliente')->latest('fecha_venta')->latest('id');
    }
}
