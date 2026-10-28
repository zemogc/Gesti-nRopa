<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venta>
 */
class VentaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'cliente_id' => Cliente::factory(),
            'cantidad' => fake()->numberBetween(1, 5),
            'fecha_venta' => fake()->dateTimeBetween('-3 months')->format('Y-m-d'),
            'total' => fn (array $venta) => round(Producto::find($venta['producto_id'])->precio * $venta['cantidad'], 2),
        ];
    }
}
