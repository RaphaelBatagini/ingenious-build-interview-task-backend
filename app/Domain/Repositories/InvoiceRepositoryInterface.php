<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Invoice;

interface InvoiceRepositoryInterface
{
    public function findById(string $invoiceId): ?Invoice;
}
