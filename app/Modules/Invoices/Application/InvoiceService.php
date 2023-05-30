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
            ->with(['billedCompany', 'products'])
            ->first();

        if (!$invoice) {
            throw new InvoiceNotFoundException();
        }

        return $this->mapInvoiceToDto($invoice);
    }

    private function mapInvoiceToDto(Invoice $invoice): InvoiceDto
    {
        $companyDto = new CompanyDto(
            $invoice->company->id,
            $invoice->company->name,
            $invoice->company->street,
            $invoice->company->city,
            $invoice->company->zip,
            $invoice->company->phone,
            $invoice->company->email
        );

        $billedCompanyDto = new CompanyDto(
            $invoice->billedCompany->id,
            $invoice->billedCompany->name,
            $invoice->billedCompany->street,
            $invoice->billedCompany->city,
            $invoice->billedCompany->zip,
            $invoice->billedCompany->phone,
            $invoice->billedCompany->email
        );

        $productDtos = [];
        foreach ($invoice->products as $product) {
            $productDto = new ProductDto(
                $product->id,
                $product->name,
                $product->pivot->quantity,
                $product->price
            );
            $productDtos[] = $productDto;
        }

        return new InvoiceDto(
            $invoice->id,
            $invoice->number,
            $invoice->status,
            $invoice->date,
            $invoice->due_date,
            $companyDto,
            $billedCompanyDto,
            $productDtos,
            $invoice->total
        );
    }
}
