<?php

namespace App\Modules\Invoices\Application;

use App\Domain\Invoice;
use App\Domain\Repositories\InvoiceRepositoryInterface;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;

class InvoiceService
{
    private $invoiceRepository;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function getInvoiceById(string $invoiceId): Invoice
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);

        if (!$invoice) {
            throw new InvoiceNotFoundException();
        }

        return $invoice;
    }
}
