<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Application\Dto;

final readonly class CompanyDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $street,
        public string $city,
        public string $zipCode,
        public string $phone,
        public string $email
    ) {}
}
