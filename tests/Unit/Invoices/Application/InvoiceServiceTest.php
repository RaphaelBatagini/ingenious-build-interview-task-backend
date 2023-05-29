<?php

namespace Tests\Unit\Modules\Invoices\Application;

use App\Domain\Company;
use App\Domain\Enums\StatusEnum;
use App\Domain\Invoice;
use App\Domain\Product;
use App\Domain\Repositories\InvoiceRepositoryInterface;
use App\Modules\Invoices\Application\Dto\CompanyDto;
use App\Modules\Invoices\Application\Dto\InvoiceDto;
use App\Modules\Invoices\Application\Dto\ProductDto;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\InvoiceService;
use App\Modules\Invoices\Infrastructure\Repositories\EloquentInvoiceRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Ramsey\Uuid\Uuid;
use stdClass;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    protected InvoiceRepositoryInterface $invoiceRepository;
    protected InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceRepository = Mockery::mock(EloquentInvoiceRepository::class);
        $this->invoiceService = new InvoiceService($this->invoiceRepository);
    }

    public function testGetInvoiceByIdShouldReturnInvoiceDtoWhenInvoiceExists(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $invoiceNumber = 'INV-001';
        $invoiceStatus = StatusEnum::APPROVED->value;
        $invoiceDate = Carbon::now();
        $invoiceDueDate = Carbon::now()->addDays(7);

        $companyName = 'ACME Inc.';
        $companyStreet = '123 Main St';
        $companyCity = 'City';
        $companyZip = '12345';
        $companyPhone = '555-123-4567';
        $companyEmail = 'info@acme.com';

        $productName1 = 'Product 1';
        $productQuantity1 = 2;
        $productPrice1 = 10.99;

        $productName2 = 'Product 2';
        $productQuantity2 = 3;
        $productPrice2 = 15.99;

        $company = new Company();
        $company->name = $companyName;
        $company->street = $companyStreet;
        $company->city = $companyCity;
        $company->zip = $companyZip;
        $company->phone = $companyPhone;
        $company->email = $companyEmail;

        $product1 = new Product();
        $product1->name = $productName1;
        $product1->price = $productPrice1;
        $product1->pivot = new stdClass();
        $product1->pivot->quantity = $productQuantity1;

        $product2 = new Product();
        $product2->name = $productName2;
        $product2->price = $productPrice2;
        $product2->pivot = new stdClass();
        $product2->pivot->quantity = $productQuantity2;

        $invoice = new Invoice();
        $invoice->id = $invoiceId;
        $invoice->number = $invoiceNumber;
        $invoice->status = $invoiceStatus;
        $invoice->date = $invoiceDate;
        $invoice->due_date = $invoiceDueDate;
        $invoice->company = $company;
        $invoice->products = new Collection([$product1, $product2]);

        $returnedInvoiceMock = Mockery::mock($invoice);
        $returnedInvoiceMock->shouldReceive('with')
            ->once()
            ->with(['company', 'products'])
            ->andReturnSelf();

        $returnedInvoiceMock->shouldReceive('first')
            ->andReturn($invoice);

        $this->invoiceRepository->shouldReceive('findById')
            ->once()
            ->with($invoiceId)
            ->andReturn($returnedInvoiceMock);

        $result = $this->invoiceService->getInvoiceById($invoiceId);

        $expectedCompanyDto = new CompanyDto(
            $companyName,
            $companyStreet,
            $companyCity,
            $companyZip,
            $companyPhone,
            $companyEmail
        );

        $expectedProductDtos = [
            new ProductDto($productName1, $productQuantity1, $productPrice1),
            new ProductDto($productName2, $productQuantity2, $productPrice2),
        ];

        $expectedInvoiceDto = new InvoiceDto(
            $invoiceNumber,
            $invoiceStatus,
            $invoiceDate,
            $invoiceDueDate,
            $expectedCompanyDto,
            $expectedProductDtos,
            ($productQuantity1 * $productPrice1) + ($productQuantity2 * $productPrice2)
        );

        $this->assertEquals($expectedInvoiceDto, $result);
    }

    public function testGetInvoiceByIdShouldThrowInvoiceNotFoundExceptionWhenInvoiceNotFound(): void
    {
        $invoiceId = Uuid::uuid4()->toString();

        $returnedInvoiceMock = Mockery::mock(Invoice::class);
        $returnedInvoiceMock->shouldReceive('with')
            ->once()
            ->with(['company', 'products'])
            ->andReturnSelf();

        $returnedInvoiceMock->shouldReceive('first')
            ->andReturn(null);

        $this->invoiceRepository->shouldReceive('findById')
            ->once()
            ->with($invoiceId)
            ->andReturn($returnedInvoiceMock);

        $this->expectException(InvoiceNotFoundException::class);

        $this->invoiceService->getInvoiceById($invoiceId);
    }
}
