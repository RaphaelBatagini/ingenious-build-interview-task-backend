<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Mappers;

use App\Modules\Invoices\Application\Dto\InvoiceDto;
use App\Modules\Invoices\Application\Dto\ProductDto;
use App\Modules\Invoices\Application\Dto\CompanyDto;
use App\Domain\Invoice;

class InvoiceMapper
{
    public static function toDto(Invoice $invoice): InvoiceDto
    {
        $productDtos = $invoice->products->map(function ($product) {
            return new ProductDto(
                $product->id,
                $product->name,
                $product->pivot->quantity,
                $product->price,
                $product->total
            );
        });

        return new InvoiceDto(
            $invoice->id,
            $invoice->number,
            $invoice->status,
            $invoice->date,
            $invoice->due_date,
            self::mapCompanyToDto($invoice->company),
            self::mapCompanyToDto($invoice->billedCompany),
            $productDtos->toArray(),
            $invoice->total
        );
    }

    private static function mapCompanyToDto($company): CompanyDto
    {
        return new CompanyDto(
            $company->id,
            $company->name,
            $company->street,
            $company->city,
            $company->zip,
            $company->phone,
            $company->email
        );
    }
}
