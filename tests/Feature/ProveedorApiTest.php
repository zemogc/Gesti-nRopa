<?php

namespace Tests\Feature;

use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProveedorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_completo(): void
    {
        $id = $this->postJson('/api/proveedores', ['nombre' => 'Textiles SA', 'contacto' => 'ventas@textiles.test'])
            ->assertCreated()
            ->json('proveedor.id');

        $this->getJson('/api/proveedores')->assertJsonCount(1, 'proveedores');

        $this->getJson("/api/proveedores/{$id}")
            ->assertOk()
            ->assertJsonPath('proveedor.nombre', 'Textiles SA');

        $this->putJson("/api/proveedores/{$id}", ['nombre' => 'Textiles SAS', 'contacto' => '601 555 0000'])
            ->assertOk()
            ->assertJsonPath('proveedor.nombre', 'Textiles SAS');

        $this->deleteJson("/api/proveedores/{$id}")
            ->assertOk()
            ->assertJson(['success' => true, 'proveedores' => []]);
    }

    public function test_valida_campos_obligatorios(): void
    {
        $proveedor = Proveedor::factory()->create();

        $this->putJson("/api/proveedores/{$proveedor->id}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'contacto']);
    }
}
