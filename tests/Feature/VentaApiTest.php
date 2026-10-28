<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaApiTest extends TestCase
{
    use RefreshDatabase;

    private function registrarVenta(Producto $producto, int $cantidad, array $extra = [])
    {
        return $this->postJson('/api/ventas', [
            'producto_id' => $producto->id,
            'cliente_id' => Cliente::factory()->create()->id,
            'cantidad' => $cantidad,
        ] + $extra);
    }

    public function test_registra_venta_calcula_total_y_descuenta_stock(): void
    {
        $producto = Producto::factory()->create(['precio' => 25.50, 'stock' => 10]);

        $this->registrarVenta($producto, 3, ['total' => 1])
            ->assertCreated()
            ->assertJsonPath('venta.total', '76.50')
            ->assertJsonPath('venta.cantidad', 3)
            ->assertJsonPath('venta.producto_nombre', $producto->nombre)
            ->assertJsonPath('venta.fecha_venta', now()->toDateString());

        $this->assertSame(7, $producto->fresh()->stock);
    }

    public function test_rechaza_venta_sin_stock_suficiente(): void
    {
        $producto = Producto::factory()->create(['stock' => 2]);

        $this->registrarVenta($producto, 3)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cantidad');

        $this->assertSame(2, $producto->fresh()->stock);
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_valida_los_datos_de_la_venta(): void
    {
        $this->postJson('/api/ventas', [
            'producto_id' => 999,
            'cliente_id' => 999,
            'cantidad' => 0,
            'fecha_venta' => now()->addDay()->toDateString(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['producto_id', 'cliente_id', 'cantidad', 'fecha_venta']);
    }

    public function test_actualizar_ajusta_el_stock_del_mismo_producto(): void
    {
        $producto = Producto::factory()->create(['precio' => 10, 'stock' => 10]);
        $id = $this->registrarVenta($producto, 4)->json('venta.id');
        $venta = Venta::find($id);

        $this->putJson("/api/ventas/{$id}", [
            'producto_id' => $producto->id,
            'cliente_id' => $venta->cliente_id,
            'cantidad' => 9,
        ])
            ->assertOk()
            ->assertJsonPath('venta.total', '90.00')
            ->assertJsonPath('venta.fecha_venta', $venta->fecha_venta->toDateString());

        $this->assertSame(1, $producto->fresh()->stock);
    }

    public function test_actualizar_cambiando_de_producto_devuelve_el_stock_al_anterior(): void
    {
        $anterior = Producto::factory()->create(['stock' => 5]);
        $nuevo = Producto::factory()->create(['stock' => 5]);
        $id = $this->registrarVenta($anterior, 2)->json('venta.id');

        $this->putJson("/api/ventas/{$id}", [
            'producto_id' => $nuevo->id,
            'cliente_id' => Venta::find($id)->cliente_id,
            'cantidad' => 3,
        ])->assertOk();

        $this->assertSame(5, $anterior->fresh()->stock);
        $this->assertSame(2, $nuevo->fresh()->stock);
    }

    public function test_actualizar_sin_stock_no_modifica_nada(): void
    {
        $producto = Producto::factory()->create(['stock' => 5]);
        $id = $this->registrarVenta($producto, 2)->json('venta.id');

        $this->putJson("/api/ventas/{$id}", [
            'producto_id' => $producto->id,
            'cliente_id' => Venta::find($id)->cliente_id,
            'cantidad' => 6,
        ])->assertUnprocessable();

        $this->assertSame(3, $producto->fresh()->stock);
        $this->assertSame(2, Venta::find($id)->cantidad);
    }

    public function test_anular_venta_devuelve_el_stock(): void
    {
        $producto = Producto::factory()->create(['stock' => 5]);
        $id = $this->registrarVenta($producto, 2)->json('venta.id');

        $this->deleteJson("/api/ventas/{$id}")
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(0, 'ventas');

        $this->assertSame(5, $producto->fresh()->stock);
    }

    public function test_filtra_ventas_por_cliente_y_fechas(): void
    {
        $cliente = Cliente::factory()->create();
        Venta::factory()->for($cliente)->create(['fecha_venta' => '2026-01-10']);
        Venta::factory()->for($cliente)->create(['fecha_venta' => '2026-03-10']);
        Venta::factory()->create(['fecha_venta' => '2026-01-15']);

        $this->getJson("/api/ventas?cliente_id={$cliente->id}")->assertJsonCount(2, 'ventas');
        $this->getJson('/api/ventas?desde=2026-01-01&hasta=2026-01-31')->assertJsonCount(2, 'ventas');
        $this->getJson('/api/ventas?desde=2026-02-01&hasta=2026-01-01')->assertUnprocessable();
    }

    public function test_muestra_venta_con_productos_y_clientes(): void
    {
        $venta = Venta::factory()->create();

        $this->getJson("/api/ventas/{$venta->id}")
            ->assertOk()
            ->assertJsonPath('venta.id', $venta->id)
            ->assertJsonCount(1, 'productos')
            ->assertJsonCount(1, 'clientes');
    }
}
