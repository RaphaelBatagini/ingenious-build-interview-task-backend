<?php

declare(strict_types=1);

namespace Database\Factories\Domain;

use App\Domain\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'id' => $this->faker->uuid,
            'name' => $this->faker->words(2, true),
            'price' => $this->faker->randomNumber(4, false),
            'currency' => 'usd',
        ];
    }
}
