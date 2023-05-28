<?php

namespace App\Modules\Invoices\Infrastructure\Repositories;

use App\Domain\Invoice;
use App\Domain\Repositories\InvoiceRepositoryInterface;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(string $invoiceId): ?Invoice
    {
        return Invoice::find($invoiceId);
    }
}
