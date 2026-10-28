<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_devuelve_los_indicadores(): void
    {
        $camiseta = Producto::factory()->create(['nombre' => 'Camiseta', 'precio' => 10, 'stock' => 2]);
        $jean = Producto::factory()->create(['nombre' => 'Jean', 'precio' => 50, 'stock' => 30]);
        Venta::factory()->for($camiseta)->create(['cantidad' => 4]);
        Venta::factory()->for($jean)->create(['cantidad' => 1]);

        $this->getJson('/api/resumen')
            ->assertOk()
            ->assertJsonPath('resumen.total_ventas', 2)
            ->assertJsonPath('resumen.total_productos', 2)
            ->assertJsonPath('resumen.ingresos_totales', 90)
            ->assertJsonPath('resumen.unidades_en_stock', 32)
            ->assertJsonCount(1, 'resumen.productos_stock_bajo')
            ->assertJsonPath('resumen.mas_vendidos.0.producto', 'Camiseta')
            ->assertJsonPath('resumen.mas_vendidos.0.unidades', 4);
    }
}
