<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_devuelve_un_token_valido(): void
    {
        $usuario = User::factory()->create(['email' => 'admin@example.com']);

        $token = $this->postJson('/api/auth/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('tipo', 'Bearer')
            ->assertJsonPath('usuario.id', $usuario->id)
            ->json('token');

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', 'admin@example.com');
    }

    public function test_login_rechaza_credenciales_incorrectas(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        $this->postJson('/api/auth/login', ['email' => 'admin@example.com', 'password' => 'incorrecta'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_logout_revoca_el_token(): void
    {
        $usuario = User::factory()->create();
        $token = $usuario->createToken('api')->plainTextToken;

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

        $this->assertSame(0, $usuario->tokens()->count());
    }

    public function test_la_api_es_publica_si_no_se_exige_autenticacion(): void
    {
        config(['gestion.requiere_autenticacion' => false]);

        $this->getJson('/api/productos')->assertOk();
    }

    public function test_la_api_exige_token_si_se_activa_la_autenticacion(): void
    {
        config(['gestion.requiere_autenticacion' => true]);

        $this->getJson('/api/productos')->assertUnauthorized();
        $this->postJson('/api/categorias', [])->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/productos')->assertOk();
    }

    public function test_comando_para_crear_usuarios(): void
    {
        $this->artisan('usuario:crear', ['nombre' => 'Admin', 'email' => 'admin@example.com', '--password' => 'secreto123'])
            ->assertSuccessful();

        $this->artisan('usuario:crear', ['nombre' => 'Otro', 'email' => 'admin@example.com', '--password' => 'secreto123'])
            ->assertFailed();

        $this->postJson('/api/auth/login', ['email' => 'admin@example.com', 'password' => 'secreto123'])->assertOk();
    }
}
