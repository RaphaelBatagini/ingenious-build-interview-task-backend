<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Application\Dto;

class ProductDto
{
    public $name;
    public $quantity;
    public $price;
    public $total;

    public function __construct(
        string $name,
        int $quantity,
        float $price,
    ) {
        $this->name = $name;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->total = $quantity * $price;
    }
}
