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
            'nombre' => ucfirst(fake()->words(3, true)),
            'descripcion' => fake()->sentence(),
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
