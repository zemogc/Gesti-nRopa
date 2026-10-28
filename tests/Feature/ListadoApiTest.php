<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListadoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sin_por_pagina_devuelve_todo_sin_paginacion(): void
    {
        Categoria::factory(3)->create();

        $this->getJson('/api/categorias')
            ->assertOk()
            ->assertJsonCount(3, 'categorias')
            ->assertJsonMissingPath('paginacion');
    }

    public function test_pagina_los_listados(): void
    {
        Producto::factory(5)->create();

        $this->getJson('/api/productos?por_pagina=2&pagina=3')
            ->assertOk()
            ->assertJsonCount(1, 'productos')
            ->assertJsonPath('paginacion', [
                'pagina_actual' => 3,
                'por_pagina' => 2,
                'total' => 5,
                'ultima_pagina' => 3,
            ]);
    }

    public function test_valida_los_parametros_de_paginacion(): void
    {
        $this->getJson('/api/clientes?por_pagina=500')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('por_pagina');
    }

    public function test_limita_las_peticiones_por_minuto(): void
    {
        config(['gestion.limite_por_minuto' => 2]);

        $this->getJson('/api/proveedores')->assertOk();
        $this->getJson('/api/proveedores')->assertOk();
        $this->getJson('/api/proveedores')->assertTooManyRequests();
    }
}
