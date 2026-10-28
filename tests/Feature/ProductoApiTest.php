<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
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

    public function test_no_elimina_un_producto_con_ventas(): void
    {
        $venta = Venta::factory()->create();

        $this->deleteJson("/api/productos/{$venta->producto_id}")
            ->assertStatus(409);

        $this->assertModelExists($venta);
    }
}
