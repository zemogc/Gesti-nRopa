<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Registra, modifica y anula ventas manteniendo consistente el stock de los productos.
 */
class VentaService
{
    public function registrar(array $datos): Venta
    {
        return DB::transaction(function () use ($datos) {
            $producto = $this->bloquearProducto($datos['producto_id']);
            $this->descontarStock($producto, (int) $datos['cantidad']);

            return Venta::create($this->completarDatos($datos, $producto));
        });
    }

    public function actualizar(Venta $venta, array $datos): Venta
    {
        return DB::transaction(function () use ($venta, $datos) {
            // Primero se devuelve el stock de la venta original y luego se descuenta el nuevo
            $anterior = $this->bloquearProducto($venta->producto_id);
            $anterior->increment('stock', $venta->cantidad);

            $producto = $anterior->id === (int) $datos['producto_id']
                ? $anterior
                : $this->bloquearProducto($datos['producto_id']);

            $this->descontarStock($producto, (int) $datos['cantidad']);

            // Si el producto no cambia se respeta el precio con el que se vendio originalmente
            $precioUnitario = $producto->is($anterior) ? $venta->precio_unitario : null;

            $datos['fecha_venta'] ??= $venta->fecha_venta?->toDateString();
            $venta->update($this->completarDatos($datos, $producto, $precioUnitario));

            return $venta;
        });
    }

    public function anular(Venta $venta): void
    {
        DB::transaction(function () use ($venta) {
            Producto::whereKey($venta->producto_id)->increment('stock', $venta->cantidad);
            $venta->delete();
        });
    }

    private function bloquearProducto(int|string $id): Producto
    {
        return Producto::lockForUpdate()->findOrFail($id);
    }

    private function descontarStock(Producto $producto, int $cantidad): void
    {
        if ($producto->stock < $cantidad) {
            throw ValidationException::withMessages([
                'cantidad' => "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}.",
            ]);
        }

        $producto->decrement('stock', $cantidad);
    }

    private function completarDatos(array $datos, Producto $producto, ?string $precioUnitario = null): array
    {
        $precioUnitario ??= $producto->precio;

        return [
            'producto_id' => $producto->id,
            'cliente_id' => $datos['cliente_id'],
            'cantidad' => $datos['cantidad'],
            'precio_unitario' => $precioUnitario,
            'fecha_venta' => $datos['fecha_venta'] ?? now()->toDateString(),
            'total' => round((float) $precioUnitario * (int) $datos['cantidad'], 2),
        ];
    }
}
