<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_las_categorias(): void
    {
        Categoria::factory(3)->create();

        $this->getJson('/api/categorias')
            ->assertOk()
            ->assertJsonCount(3, 'categorias');
    }

    public function test_crea_una_categoria(): void
    {
        $this->postJson('/api/categorias', ['nombre' => 'Camisetas', 'descripcion' => 'Ropa superior'])
            ->assertCreated()
            ->assertJsonPath('categoria.nombre', 'Camisetas');

        $this->assertDatabaseHas('categorias', ['nombre' => 'Camisetas']);
    }

    public function test_valida_los_datos_al_crear(): void
    {
        Categoria::factory()->create(['nombre' => 'Camisetas']);

        $this->postJson('/api/categorias', ['nombre' => 'Camisetas'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'descripcion']);
    }

    public function test_los_mensajes_de_validacion_estan_en_espanol(): void
    {
        $this->postJson('/api/categorias', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'El campo nombre es obligatorio. (y 1 error mas)')
            ->assertJsonPath('errors.descripcion.0', 'El campo descripcion es obligatorio.');
    }

    public function test_ignora_el_id_enviado_en_el_cuerpo(): void
    {
        $existente = Categoria::factory()->create();

        $this->postJson('/api/categorias', ['id' => $existente->id, 'nombre' => 'Nueva', 'descripcion' => 'x'])
            ->assertCreated();

        $this->assertDatabaseCount('categorias', 2);
    }

    public function test_muestra_una_categoria(): void
    {
        $categoria = Categoria::factory()->create();

        $this->getJson("/api/categorias/{$categoria->id}")
            ->assertOk()
            ->assertJsonPath('categoria.id', $categoria->id);
    }

    public function test_devuelve_404_en_json_si_no_existe(): void
    {
        foreach (['get', 'put', 'delete'] as $metodo) {
            $this->json($metodo, '/api/categorias/999', ['nombre' => 'x', 'descripcion' => 'x'])
                ->assertNotFound()
                ->assertJson(['message' => 'Recurso no encontrado.']);
        }
    }

    public function test_actualiza_conservando_su_propio_nombre(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Camisetas']);

        $this->putJson("/api/categorias/{$categoria->id}", ['nombre' => 'Camisetas', 'descripcion' => 'Nueva descripcion'])
            ->assertOk()
            ->assertJsonPath('categoria.descripcion', 'Nueva descripcion');
    }

    public function test_elimina_una_categoria_sin_productos(): void
    {
        $categoria = Categoria::factory()->create();

        $this->deleteJson("/api/categorias/{$categoria->id}")
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonCount(0, 'categorias');

        $this->assertModelMissing($categoria);
    }

    public function test_no_elimina_una_categoria_con_productos(): void
    {
        $producto = Producto::factory()->create();

        $this->deleteJson("/api/categorias/{$producto->categoria_id}")
            ->assertStatus(409);

        $this->assertModelExists($producto);
    }
}
