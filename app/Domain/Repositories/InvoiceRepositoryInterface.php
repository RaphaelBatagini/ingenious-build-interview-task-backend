<?php

namespace App\Domain\Repositories;

use App\Domain\Invoice;

interface InvoiceRepositoryInterface
{
    public function findById(string $invoiceId): ?Invoice;
}
