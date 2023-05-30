<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Application;

use App\Domain\Repositories\InvoiceRepositoryInterface;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\Dto\InvoiceDto;
use App\Modules\Invoices\Mappers\InvoiceMapper;

class InvoiceService
{
    private $invoiceRepository;

    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function getInvoiceById(string $invoiceId): InvoiceDto
    {
        $invoice = $this->invoiceRepository
            ->findById($invoiceId)
            ->with(['billedCompany', 'products'])
            ->first();

        if (!$invoice) {
            throw new InvoiceNotFoundException();
        }

        return InvoiceMapper::toDto($invoice);
    }
}
