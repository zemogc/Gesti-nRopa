<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteVentasApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_agrupa_por_dia_incluyendo_dias_sin_ventas(): void
    {
        $producto = Producto::factory()->create(['precio' => 10]);
        Venta::factory()->for($producto)->create(['fecha_venta' => '2026-03-01', 'cantidad' => 2]);
        Venta::factory()->for($producto)->create(['fecha_venta' => '2026-03-01', 'cantidad' => 1]);
        Venta::factory()->for($producto)->create(['fecha_venta' => '2026-03-03', 'cantidad' => 5]);
        Venta::factory()->for($producto)->create(['fecha_venta' => '2026-03-10', 'cantidad' => 9]);

        $this->getJson('/api/resumen/ventas?desde=2026-03-01&hasta=2026-03-03')
            ->assertOk()
            ->assertJsonPath('agrupar', 'dia')
            ->assertJsonPath('totales', ['ventas' => 3, 'unidades' => 8, 'ingresos' => 80])
            ->assertJsonPath('periodos', [
                ['periodo' => '2026-03-01', 'ventas' => 2, 'unidades' => 3, 'ingresos' => 30],
                ['periodo' => '2026-03-02', 'ventas' => 0, 'unidades' => 0, 'ingresos' => 0],
                ['periodo' => '2026-03-03', 'ventas' => 1, 'unidades' => 5, 'ingresos' => 50],
            ]);
    }

    public function test_agrupa_por_mes(): void
    {
        $producto = Producto::factory()->create(['precio' => 100]);
        Venta::factory()->for($producto)->create(['fecha_venta' => '2026-01-15', 'cantidad' => 1]);
        Venta::factory()->for($producto)->create(['fecha_venta' => '2026-03-20', 'cantidad' => 2]);

        $this->getJson('/api/resumen/ventas?agrupar=mes&desde=2026-01-20&hasta=2026-03-31')
            ->assertOk()
            ->assertJsonPath('desde', '2026-01-01')
            ->assertJsonCount(3, 'periodos')
            ->assertJsonPath('periodos.0.ingresos', 100)
            ->assertJsonPath('periodos.1.ventas', 0)
            ->assertJsonPath('periodos.2.unidades', 2);
    }

    public function test_por_defecto_devuelve_los_ultimos_30_dias(): void
    {
        $this->getJson('/api/resumen/ventas')
            ->assertOk()
            ->assertJsonCount(30, 'periodos')
            ->assertJsonPath('hasta', today()->toDateString());
    }

    public function test_valida_parametros(): void
    {
        $this->getJson('/api/resumen/ventas?agrupar=semana')->assertJsonValidationErrors('agrupar');
        $this->getJson('/api/resumen/ventas?desde=2020-01-01&hasta=2026-01-01')->assertJsonValidationErrors('desde');
    }

    public function test_exporta_las_ventas_filtradas_en_csv(): void
    {
        $cliente = Cliente::factory()->create(['nombre' => 'Ana', 'apellido' => 'Gómez']);
        $producto = Producto::factory()->create(['nombre' => 'Jean', 'sku' => 'JEAN-32', 'precio' => 50]);
        Venta::factory()->for($cliente)->for($producto)->create(['fecha_venta' => '2026-02-01', 'cantidad' => 2]);
        Venta::factory()->create(['fecha_venta' => '2026-05-01']);

        $respuesta = $this->get('/api/ventas/exportar?hasta=2026-02-28')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertDownload('ventas-'.now()->format('Y-m-d').'.csv');

        $lineas = explode("\n", trim($respuesta->streamedContent()));

        $this->assertCount(2, $lineas);
        $this->assertSame("\u{FEFF}id;fecha;cliente;email;producto;sku;cantidad;total", $lineas[0]);
        $this->assertStringContainsString(';2026-02-01;"Ana Gómez";', $lineas[1]);
        $this->assertStringEndsWith(';Jean;JEAN-32;2;100.00', $lineas[1]);
    }
}
