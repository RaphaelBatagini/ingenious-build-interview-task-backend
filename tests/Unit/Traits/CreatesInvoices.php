<?php

declare(strict_types=1);

namespace Tests\Unit\Traits;

use App\Domain\Company;
use App\Domain\Invoice;
use App\Domain\Product;
use Ramsey\Uuid\Uuid;
use Faker\Factory as FakerFactory;

trait CreatesInvoices
{
    protected function createInvoice(array $overrides = []): Invoice
    {
        $faker = FakerFactory::create();

        $quantity = $faker->randomDigitNotNull;
        $productCount = $faker->numberBetween(1, 5);

        return Invoice::factory()
            ->for(Company::factory()->create(), 'billedCompany')
            ->hasAttached(
                Product::factory()->count($productCount),
                function () use ($quantity) {
                    return [
                        'id' => Uuid::uuid4()->toString(),
                        'quantity' => $quantity,
                    ];
                },
                'products'
            )
            ->approved()
            ->create($overrides);
    }
}
