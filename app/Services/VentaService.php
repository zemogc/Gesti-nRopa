<?php

namespace App\Services;

use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Registra, modifica y anula ventas manteniendo consistente el stock de los productos.
 */
class VentaService
{
    public function __construct(private readonly InventarioService $inventario) {}

    public function registrar(array $datos): Venta
    {
        return DB::transaction(function () use ($datos) {
            $producto = $this->bloquearProducto($datos['producto_id']);
            $this->verificarActivo($producto);
            $this->descontarStock($producto, (int) $datos['cantidad']);

            $venta = Venta::create($this->completarDatos($datos, $producto));

            $this->inventario->registrar($producto, MovimientoStock::VENTA, -$venta->cantidad, ['venta_id' => $venta->id]);

            return $venta;
        });
    }

    public function actualizar(Venta $venta, array $datos): Venta
    {
        return DB::transaction(function () use ($venta, $datos) {
            $cantidadAnterior = $venta->cantidad;

            // Primero se devuelve el stock de la venta original y luego se descuenta el nuevo
            $anterior = $this->bloquearProducto($venta->producto_id);
            $anterior->increment('stock', $cantidadAnterior);

            $mismoProducto = $anterior->id === (int) $datos['producto_id'];
            $producto = $mismoProducto ? $anterior : $this->bloquearProducto($datos['producto_id']);

            // Una venta existente de un producto ya desactivado se puede corregir,
            // pero no se puede pasar a un producto inactivo
            if (! $mismoProducto) {
                $this->verificarActivo($producto);
            }

            $this->descontarStock($producto, (int) $datos['cantidad']);

            // Si el producto no cambia se respeta el precio con el que se vendio originalmente
            $precioUnitario = $mismoProducto ? $venta->precio_unitario : null;

            $datos['fecha_venta'] ??= $venta->fecha_venta?->toDateString();
            $venta->update($this->completarDatos($datos, $producto, $precioUnitario));

            $referencia = ['venta_id' => $venta->id];

            if ($mismoProducto) {
                $this->inventario->registrar($producto, MovimientoStock::EDICION_VENTA, $cantidadAnterior - $venta->cantidad, $referencia);
            } else {
                $this->inventario->registrar($anterior, MovimientoStock::EDICION_VENTA, $cantidadAnterior, $referencia);
                $this->inventario->registrar($producto, MovimientoStock::EDICION_VENTA, -$venta->cantidad, $referencia);
            }

            return $venta;
        });
    }

    public function anular(Venta $venta): void
    {
        DB::transaction(function () use ($venta) {
            $producto = $this->bloquearProducto($venta->producto_id);
            $producto->increment('stock', $venta->cantidad);

            $this->inventario->registrar($producto, MovimientoStock::ANULACION, $venta->cantidad, ['venta_id' => $venta->id]);

            $venta->delete();
        });
    }

    private function bloquearProducto(int|string $id): Producto
    {
        return Producto::lockForUpdate()->findOrFail($id);
    }

    private function verificarActivo(Producto $producto): void
    {
        if (! $producto->activo) {
            throw ValidationException::withMessages([
                'producto_id' => "El producto {$producto->nombre} esta desactivado y no se puede vender.",
            ]);
        }
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
