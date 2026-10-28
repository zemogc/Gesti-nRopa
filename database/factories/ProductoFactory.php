<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->bothify('ROP-####-??'),
            'nombre' => ucfirst(fake()->words(3, true)),
            'descripcion' => fake()->sentence(),
            'talla' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']),
            'color' => fake()->randomElement(['Negro', 'Blanco', 'Azul', 'Rojo', 'Gris']),
            'precio' => fake()->randomFloat(2, 10, 500),
            'categoria_id' => Categoria::factory(),
            'stock' => fake()->numberBetween(0, 100),
        ];
    }

    public function sinStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
