<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use App\Services\VentaService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Carga datos de ejemplo de una tienda de ropa.
     */
    public function run(VentaService $ventas): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $catalogo = [
            'Camisetas' => ['Camiseta basica algodon' => 35000, 'Camiseta estampada' => 45000],
            'Pantalones' => ['Jean slim fit' => 120000, 'Pantalon cargo' => 98000],
            'Chaquetas' => ['Chaqueta de jean' => 165000, 'Chaqueta impermeable' => 210000],
            'Accesorios' => ['Gorra' => 30000, 'Cinturon de cuero' => 55000],
        ];

        foreach ($catalogo as $categoria => $productos) {
            $categoria = Categoria::create([
                'nombre' => $categoria,
                'descripcion' => "Linea de {$categoria}",
            ]);

            foreach ($productos as $nombre => $precio) {
                Producto::factory()->for($categoria)->create([
                    'nombre' => $nombre,
                    'precio' => $precio,
                    'stock' => fake()->numberBetween(3, 40),
                ]);
            }
        }

        Proveedor::factory(4)->create();
        $clientes = Cliente::factory(10)->create();

        // Las ventas pasan por el servicio para que el stock quede consistente
        foreach (range(1, 20) as $i) {
            $producto = Producto::where('stock', '>', 0)->inRandomOrder()->first();

            if (! $producto) {
                break;
            }

            $ventas->registrar([
                'producto_id' => $producto->id,
                'cliente_id' => $clientes->random()->id,
                'cantidad' => fake()->numberBetween(1, min(3, $producto->stock)),
                'fecha_venta' => fake()->dateTimeBetween('-2 months')->format('Y-m-d'),
            ]);
        }
    }
}
