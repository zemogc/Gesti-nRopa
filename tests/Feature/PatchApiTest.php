<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_patch_actualiza_solo_los_campos_enviados(): void
    {
        $cliente = Cliente::factory()->create(['nombre' => 'Ana', 'telefono' => '300 000 0000']);

        $this->patchJson("/api/clientes/{$cliente->id}", ['telefono' => '311 111 1111'])
            ->assertOk()
            ->assertJsonPath('cliente.telefono', '311 111 1111')
            ->assertJsonPath('cliente.nombre', 'Ana');
    }

    public function test_patch_sigue_validando_los_campos_enviados(): void
    {
        $producto = Producto::factory()->create();

        $this->patchJson("/api/productos/{$producto->id}", ['precio' => -5, 'nombre' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['precio', 'nombre']);
    }

    public function test_put_sigue_exigiendo_todos_los_campos(): void
    {
        $producto = Producto::factory()->create();

        $this->putJson("/api/productos/{$producto->id}", ['stock' => 3])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'precio', 'categoria_id']);
    }

    public function test_patch_de_venta_ajusta_stock_y_total_con_los_datos_actuales(): void
    {
        $producto = Producto::factory()->create(['precio' => 20, 'stock' => 10]);
        $venta = Venta::find($this->postJson('/api/ventas', [
            'producto_id' => $producto->id,
            'cliente_id' => Cliente::factory()->create()->id,
            'cantidad' => 2,
        ])->json('venta.id'));

        $this->patchJson("/api/ventas/{$venta->id}", ['cantidad' => 5])
            ->assertOk()
            ->assertJsonPath('venta.cantidad', 5)
            ->assertJsonPath('venta.cliente_id', $venta->cliente_id)
            ->assertJsonPath('venta.total', '100.00');

        $this->assertSame(5, $producto->fresh()->stock);
    }
}
