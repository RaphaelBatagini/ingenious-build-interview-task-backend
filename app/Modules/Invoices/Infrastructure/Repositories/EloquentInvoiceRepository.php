<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Infrastructure\Repositories;

use App\Domain\Invoice;
use App\Domain\Repositories\InvoiceRepositoryInterface;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(string $invoiceId): ?Invoice
    {
        return Invoice::find($invoiceId);
    }

    public function save(Invoice $invoice): void
    {
        $invoice->save();
    }
}
