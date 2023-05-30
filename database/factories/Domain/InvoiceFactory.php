<?php

declare(strict_types=1);

namespace Database\Factories\Domain;

use App\Domain\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'id' => $this->faker->uuid,
            'number' => $this->faker->uuid,
            'status' => $this->faker->randomElement(['draft', 'approved', 'rejected']),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
        ];
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
            ];
        });
    }

    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
            ];
        });
    }
}
