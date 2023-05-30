<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Application\Dto;

final readonly class ProductDto
{
    public function __construct(
        public string $id,
        public string $name,
        public int $quantity,
        public float $price,
    ) {}
}
