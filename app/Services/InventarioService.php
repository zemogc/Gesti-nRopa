<?php

namespace App\Services;

use App\Models\MovimientoStock;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;

/**
 * Registra en el historial (kardex) cada cambio de stock de un producto.
 */
class InventarioService
{
    /**
     * Debe llamarse despues de modificar el stock: toma $producto->stock como stock resultante.
     */
    public function registrar(Producto $producto, string $tipo, int $cantidad, array $extra = []): ?MovimientoStock
    {
        if ($cantidad === 0) {
            return null;
        }

        return MovimientoStock::create([
            'producto_id' => $producto->id,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'stock_resultante' => $producto->stock,
            'user_id' => Auth::guard('sanctum')->id(),
        ] + $extra);
    }

    public function reabastecer(Producto $producto, int $cantidad, ?int $proveedorId = null, ?string $nota = null): Producto
    {
        $producto->increment('stock', $cantidad, array_filter(['proveedor_id' => $proveedorId]));

        $this->registrar($producto, MovimientoStock::REABASTECIMIENTO, $cantidad, [
            'proveedor_id' => $proveedorId,
            'nota' => $nota,
        ]);

        return $producto;
    }
}
