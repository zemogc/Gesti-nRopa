<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MovimientoStockApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * [tipo, cantidad, stock_resultante] de los movimientos del producto, del mas antiguo al mas reciente.
     */
    private function kardex(Producto $producto): array
    {
        return $producto->movimientos()->orderBy('id')->get()
            ->map(fn (MovimientoStock $m) => [$m->tipo, $m->cantidad, $m->stock_resultante])
            ->all();
    }

    private function crearProducto(int $stock): Producto
    {
        $id = $this->postJson('/api/productos', [
            'nombre' => 'Jean',
            'descripcion' => 'x',
            'precio' => 10,
            'categoria_id' => Categoria::factory()->create()->id,
            'stock' => $stock,
        ])->json('producto.id');

        return Producto::find($id);
    }

    public function test_registra_cada_cambio_de_stock(): void
    {
        $producto = $this->crearProducto(10);
        $cliente = Cliente::factory()->create();

        $ventaId = $this->postJson('/api/ventas', ['producto_id' => $producto->id, 'cliente_id' => $cliente->id, 'cantidad' => 3])
            ->json('venta.id');
        $this->patchJson("/api/ventas/{$ventaId}", ['cantidad' => 1]);
        $this->postJson("/api/productos/{$producto->id}/reabastecer", ['cantidad' => 5]);
        $this->patchJson("/api/productos/{$producto->id}", ['stock' => 12]);
        $this->deleteJson("/api/ventas/{$ventaId}");

        $this->assertSame([
            ['inicial', 10, 10],
            ['venta', -3, 7],
            ['edicion_venta', 2, 9],
            ['reabastecimiento', 5, 14],
            ['ajuste', -2, 12],
            ['anulacion', 1, 13],
        ], $this->kardex($producto));

        $this->assertSame(13, $producto->fresh()->stock);
        // La anulacion conserva la referencia a la venta eliminada
        $this->assertSame($ventaId, $producto->movimientos()->where('tipo', 'anulacion')->value('venta_id'));
    }

    public function test_cambiar_el_producto_de_una_venta_registra_ambos_movimientos(): void
    {
        $anterior = $this->crearProducto(5);
        $nuevo = $this->crearProducto(5);
        $ventaId = $this->postJson('/api/ventas', [
            'producto_id' => $anterior->id,
            'cliente_id' => Cliente::factory()->create()->id,
            'cantidad' => 2,
        ])->json('venta.id');

        $this->patchJson("/api/ventas/{$ventaId}", ['producto_id' => $nuevo->id, 'cantidad' => 4])->assertOk();

        $this->assertSame([['inicial', 5, 5], ['venta', -2, 3], ['edicion_venta', 2, 5]], $this->kardex($anterior));
        $this->assertSame([['inicial', 5, 5], ['edicion_venta', -4, 1]], $this->kardex($nuevo));
    }

    public function test_una_venta_rechazada_no_deja_movimientos(): void
    {
        $producto = $this->crearProducto(1);

        $this->postJson('/api/ventas', [
            'producto_id' => $producto->id,
            'cliente_id' => Cliente::factory()->create()->id,
            'cantidad' => 5,
        ])->assertUnprocessable();

        $this->assertSame([['inicial', 1, 1]], $this->kardex($producto));
    }

    public function test_consulta_el_historial_con_proveedor_usuario_y_filtro(): void
    {
        $producto = $this->crearProducto(0);
        $proveedor = Proveedor::factory()->create(['nombre' => 'Textiles SA']);
        Sanctum::actingAs($usuario = User::factory()->create());

        $this->postJson("/api/productos/{$producto->id}/reabastecer", [
            'cantidad' => 8,
            'proveedor_id' => $proveedor->id,
            'nota' => 'Factura 123',
        ])->assertOk();
        $this->patchJson("/api/productos/{$producto->id}", ['stock' => 6]);

        $this->getJson("/api/productos/{$producto->id}/movimientos")
            ->assertOk()
            ->assertJsonCount(2, 'movimientos')
            ->assertJsonPath('movimientos.0.tipo', 'ajuste')
            ->assertJsonPath('movimientos.1.tipo', 'reabastecimiento')
            ->assertJsonPath('movimientos.1.proveedor.nombre', 'Textiles SA')
            ->assertJsonPath('movimientos.1.usuario.id', $usuario->id)
            ->assertJsonPath('movimientos.1.nota', 'Factura 123');

        $this->getJson("/api/productos/{$producto->id}/movimientos?tipo=ajuste")->assertJsonCount(1, 'movimientos');
        $this->getJson("/api/productos/{$producto->id}/movimientos?tipo=robo")->assertJsonValidationErrors('tipo');
    }
}
