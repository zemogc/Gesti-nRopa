<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NormalizacionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_email_se_guarda_en_minusculas_y_no_admite_duplicados_por_mayusculas(): void
    {
        $datos = ['nombre' => 'Ana', 'apellido' => 'Gómez', 'telefono' => '300 000 0000'];

        $this->postJson('/api/clientes', ['email' => 'Ana@Mail.COM'] + $datos)
            ->assertCreated()
            ->assertJsonPath('cliente.email', 'ana@mail.com');

        $this->postJson('/api/clientes', ['email' => 'ANA@mail.com'] + $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_sku_y_talla_se_guardan_en_mayusculas(): void
    {
        $datos = [
            'nombre' => 'Jean',
            'descripcion' => 'x',
            'precio' => 10,
            'categoria_id' => Categoria::factory()->create()->id,
            'stock' => 1,
        ];

        $this->postJson('/api/productos', ['sku' => 'jean-32', 'talla' => 'xl'] + $datos)
            ->assertCreated()
            ->assertJsonPath('producto.sku', 'JEAN-32')
            ->assertJsonPath('producto.talla', 'XL');

        $this->postJson('/api/productos', ['sku' => 'Jean-32'] + $datos)
            ->assertJsonValidationErrors('sku');
    }

    public function test_las_respuestas_no_escapan_tildes(): void
    {
        Cliente::factory()->create(['apellido' => 'Gómez Muñoz']);

        $contenido = $this->getJson('/api/clientes')->assertOk()->getContent();

        $this->assertStringContainsString('Gómez Muñoz', $contenido);
    }
}
