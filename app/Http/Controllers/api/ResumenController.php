<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumenController extends Controller
{
    /**
     * Indicadores generales del negocio. Filtro opcional: ?stock_minimo=5
     */
    public function __invoke(Request $request): JsonResponse
    {
        $stockMinimo = $request->integer('stock_minimo', 5);

        $masVendidos = Venta::query()
            ->selectRaw('producto_id, SUM(cantidad) as unidades, SUM(total) as ingresos')
            ->groupBy('producto_id')
            ->orderByDesc('unidades')
            ->limit(5)
            ->with('producto:id,nombre')
            ->get()
            ->map(fn (Venta $fila) => [
                'producto_id' => $fila->producto_id,
                'producto' => $fila->producto?->nombre,
                'unidades' => (int) $fila->unidades,
                'ingresos' => round((float) $fila->ingresos, 2),
            ]);

        return response()->json([
            'resumen' => [
                'total_clientes' => Cliente::count(),
                'total_productos' => Producto::count(),
                'total_ventas' => Venta::count(),
                'ingresos_totales' => round((float) Venta::sum('total'), 2),
                'unidades_en_stock' => (int) Producto::sum('stock'),
                // Los productos desactivados no se reponen, asi que no generan alerta
                'productos_stock_bajo' => Producto::where('activo', true)->stockBajo($stockMinimo)->orderBy('stock')->get(['id', 'nombre', 'stock']),
                'mas_vendidos' => $masVendidos,
            ],
        ]);
    }
}
