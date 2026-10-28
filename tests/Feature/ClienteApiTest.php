<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteApiTest extends TestCase
{
    use RefreshDatabase;

    private array $datos = [
        'nombre' => 'Ana',
        'apellido' => 'Gomez',
        'email' => 'ana@example.com',
        'telefono' => '300 123 4567',
    ];

    public function test_crea_un_cliente(): void
    {
        $this->postJson('/api/clientes', $this->datos)
            ->assertCreated()
            ->assertJsonPath('cliente.email', 'ana@example.com');
    }

    public function test_rechaza_email_invalido_o_repetido(): void
    {
        Cliente::factory()->create(['email' => 'ana@example.com']);

        $this->postJson('/api/clientes', $this->datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->postJson('/api/clientes', ['email' => 'no-es-email'] + $this->datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_rechaza_telefono_con_letras(): void
    {
        $this->postJson('/api/clientes', ['telefono' => 'abc'] + $this->datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('telefono');
    }

    public function test_busca_clientes(): void
    {
        Cliente::factory()->create(['nombre' => 'Ana', 'apellido' => 'Gomez']);
        Cliente::factory()->create(['nombre' => 'Luis', 'apellido' => 'Perez']);

        $this->getJson('/api/clientes?buscar=gom')
            ->assertOk()
            ->assertJsonCount(1, 'clientes')
            ->assertJsonPath('clientes.0.nombre', 'Ana');
    }

    public function test_actualiza_y_elimina(): void
    {
        $cliente = Cliente::factory()->create($this->datos);

        $this->putJson("/api/clientes/{$cliente->id}", ['telefono' => '311 000 0000'] + $this->datos)
            ->assertOk()
            ->assertJsonPath('cliente.telefono', '311 000 0000');

        $this->deleteJson("/api/clientes/{$cliente->id}")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertModelMissing($cliente);
    }
}
