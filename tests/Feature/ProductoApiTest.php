<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_productos_con_el_nombre_de_su_categoria(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Camisetas']);
        Producto::factory()->for($categoria)->create();

        $this->getJson('/api/productos')
            ->assertOk()
            ->assertJsonPath('productos.0.categoria', 'Camisetas');
    }

    public function test_filtra_por_busqueda_categoria_y_stock_bajo(): void
    {
        $camisetas = Categoria::factory()->create();
        Producto::factory()->for($camisetas)->create(['nombre' => 'Camiseta roja', 'stock' => 2]);
        Producto::factory()->for($camisetas)->create(['nombre' => 'Camiseta azul', 'stock' => 50]);
        Producto::factory()->create(['nombre' => 'Jean', 'stock' => 1]);

        $this->getJson('/api/productos?buscar=camiseta')->assertJsonCount(2, 'productos');
        $this->getJson("/api/productos?categoria_id={$camisetas->id}")->assertJsonCount(2, 'productos');
        $this->getJson('/api/productos?stock_bajo=5')->assertJsonCount(2, 'productos');
    }

    public function test_crea_un_producto(): void
    {
        $categoria = Categoria::factory()->create();

        $this->postJson('/api/productos', [
            'nombre' => 'Jean slim',
            'descripcion' => 'Azul oscuro',
            'precio' => 120000,
            'categoria_id' => $categoria->id,
            'stock' => 10,
        ])
            ->assertCreated()
            ->assertJsonPath('producto.nombre', 'Jean slim')
            ->assertJsonPath('producto.categoria', $categoria->nombre);
    }

    public function test_valida_precio_stock_y_categoria(): void
    {
        $this->postJson('/api/productos', [
            'nombre' => 'Jean',
            'descripcion' => 'x',
            'precio' => -1,
            'categoria_id' => 999,
            'stock' => -5,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['precio', 'categoria_id', 'stock']);
    }

    public function test_muestra_producto_con_categorias_disponibles(): void
    {
        $producto = Producto::factory()->create();
        Categoria::factory()->create();

        $this->getJson("/api/productos/{$producto->id}")
            ->assertOk()
            ->assertJsonPath('producto.id', $producto->id)
            ->assertJsonCount(2, 'categorias');
    }

    public function test_actualiza_y_elimina(): void
    {
        $producto = Producto::factory()->create();

        $this->putJson("/api/productos/{$producto->id}", [
            'nombre' => 'Nuevo nombre',
            'descripcion' => 'x',
            'precio' => 10,
            'categoria_id' => $producto->categoria_id,
            'stock' => 3,
        ])
            ->assertOk()
            ->assertJsonPath('producto.nombre', 'Nuevo nombre')
            ->assertJsonPath('producto.stock', 3);

        $this->deleteJson("/api/productos/{$producto->id}")
            ->assertOk()
            ->assertJsonCount(0, 'productos');
    }

    public function test_asocia_un_proveedor_y_filtra_por_el(): void
    {
        $proveedor = Proveedor::factory()->create(['nombre' => 'Textiles SA']);
        $categoria = Categoria::factory()->create();
        Producto::factory()->create();

        $this->postJson('/api/productos', [
            'nombre' => 'Jean',
            'descripcion' => 'x',
            'precio' => 10,
            'categoria_id' => $categoria->id,
            'proveedor_id' => $proveedor->id,
            'stock' => 1,
        ])
            ->assertCreated()
            ->assertJsonPath('producto.proveedor', 'Textiles SA');

        $this->getJson("/api/productos?proveedor_id={$proveedor->id}")->assertJsonCount(1, 'productos');
        $this->getJson("/api/proveedores/{$proveedor->id}")->assertJsonCount(1, 'productos');
    }

    public function test_al_eliminar_el_proveedor_el_producto_queda_sin_proveedor(): void
    {
        $producto = Producto::factory()->for(Proveedor::factory())->create();

        $this->deleteJson("/api/proveedores/{$producto->proveedor_id}")->assertOk();

        $this->assertNull($producto->fresh()->proveedor_id);
    }

    public function test_reabastece_el_stock(): void
    {
        $producto = Producto::factory()->create(['stock' => 2]);
        $proveedor = Proveedor::factory()->create();

        $this->postJson("/api/productos/{$producto->id}/reabastecer", ['cantidad' => 10, 'proveedor_id' => $proveedor->id])
            ->assertOk()
            ->assertJsonPath('producto.stock', 12)
            ->assertJsonPath('producto.proveedor_id', $proveedor->id);

        $this->postJson("/api/productos/{$producto->id}/reabastecer", ['cantidad' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cantidad');
    }

    public function test_guarda_talla_color_y_sku(): void
    {
        $categoria = Categoria::factory()->create();
        $datos = [
            'sku' => 'JEAN-SLIM-32',
            'nombre' => 'Jean slim',
            'descripcion' => 'x',
            'talla' => '32',
            'color' => 'Azul',
            'precio' => 10,
            'categoria_id' => $categoria->id,
            'stock' => 1,
        ];

        $id = $this->postJson('/api/productos', $datos)
            ->assertCreated()
            ->assertJsonPath('producto.sku', 'JEAN-SLIM-32')
            ->assertJsonPath('producto.talla', '32')
            ->assertJsonPath('producto.color', 'Azul')
            ->json('producto.id');

        // El SKU es unico, pero el propio producto puede conservarlo al actualizar
        $this->postJson('/api/productos', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sku');
        $this->putJson("/api/productos/{$id}", $datos)->assertOk();

        $this->postJson('/api/productos', ['sku' => 'con espacios'] + $datos)
            ->assertJsonValidationErrors('sku');
    }

    public function test_filtra_por_talla_color_y_sku(): void
    {
        Producto::factory()->create(['sku' => 'CAM-M-NEG', 'talla' => 'M', 'color' => 'Negro']);
        Producto::factory()->create(['sku' => 'CAM-L-NEG', 'talla' => 'L', 'color' => 'Negro']);
        Producto::factory()->create(['sku' => 'CAM-M-BLA', 'talla' => 'M', 'color' => 'Blanco']);

        $this->getJson('/api/productos?talla=M')->assertJsonCount(2, 'productos');
        $this->getJson('/api/productos?talla=M&color=Negro')->assertJsonCount(1, 'productos');
        $this->getJson('/api/productos?buscar=CAM-L-NEG')
            ->assertJsonCount(1, 'productos')
            ->assertJsonPath('productos.0.talla', 'L');
    }

    public function test_desactivar_un_producto_impide_venderlo(): void
    {
        $producto = Producto::factory()->create(['stock' => 10]);
        $cliente = Cliente::factory()->create();
        $venta = ['producto_id' => $producto->id, 'cliente_id' => $cliente->id, 'cantidad' => 1];

        $this->postJson('/api/productos', [])->assertJsonMissingValidationErrors('activo');
        $ventaId = $this->postJson('/api/ventas', $venta)->assertCreated()->json('venta.id');

        $this->patchJson("/api/productos/{$producto->id}", ['activo' => false])
            ->assertOk()
            ->assertJsonPath('producto.activo', false);

        $this->postJson('/api/ventas', $venta)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('producto_id');

        // Las ventas ya registradas se pueden seguir corrigiendo
        $this->patchJson("/api/ventas/{$ventaId}", ['cantidad' => 2])->assertOk();
    }

    public function test_filtra_por_activo_y_excluye_inactivos_del_stock_bajo(): void
    {
        Producto::factory()->create(['stock' => 1]);
        Producto::factory()->create(['stock' => 1, 'activo' => false]);

        $this->getJson('/api/productos?activo=1')->assertJsonCount(1, 'productos');
        $this->getJson('/api/productos?activo=0')->assertJsonCount(1, 'productos');
        $this->getJson('/api/productos')->assertJsonCount(2, 'productos');
        $this->getJson('/api/resumen')->assertJsonCount(1, 'resumen.productos_stock_bajo');
    }

    public function test_no_elimina_un_producto_con_ventas(): void
    {
        $venta = Venta::factory()->create();

        $this->deleteJson("/api/productos/{$venta->producto_id}")
            ->assertStatus(409);

        $this->assertModelExists($venta);
    }
}
