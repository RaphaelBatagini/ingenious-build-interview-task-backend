<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Application\Dto;

final readonly class InvoiceDto
{
    public function __construct(
        public string $id,
        public string $number,
        public string $status,
        public string $date,
        public string $dueDate,
        public CompanyDto $company,
        public CompanyDto $billedCompany,
        public array $products,
        public float $totalPrice
    ) {}
}
