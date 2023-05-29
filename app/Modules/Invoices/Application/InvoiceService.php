<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Application;

use App\Domain\Invoice;
use App\Domain\Repositories\InvoiceRepositoryInterface;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\Dto\InvoiceDto;
use App\Modules\Invoices\Application\Dto\ProductDto;
use App\Modules\Invoices\Application\Dto\CompanyDto;

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
            ->with(['company', 'products'])
            ->first();

        if (!$invoice) {
            throw new InvoiceNotFoundException();
        }

        return $this->mapInvoiceToDto($invoice);
    }

    private function mapInvoiceToDto(Invoice $invoice): InvoiceDto
    {
        $invoiceTotalPrice = 0;

        $companyDto = new CompanyDto(
            $invoice->company->name,
            $invoice->company->street,
            $invoice->company->city,
            $invoice->company->zip,
            $invoice->company->phone,
            $invoice->company->email
        );

        $productDtos = [];
        foreach ($invoice->products as $product) {
            $productDto = new ProductDto(
                $product->name,
                $product->pivot->quantity,
                $product->price
            );
            $productDtos[] = $productDto;
            $invoiceTotalPrice += $productDto->total;
        }

        return new InvoiceDto(
            $invoice->number,
            $invoice->status,
            $invoice->date,
            $invoice->due_date,
            $companyDto,
            $productDtos,
            $invoiceTotalPrice
        );
    }
}
