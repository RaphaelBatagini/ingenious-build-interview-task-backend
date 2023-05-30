<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Application;

use App\Domain\Company;
use App\Domain\Invoice;
use App\Domain\Product;
use App\Domain\Repositories\InvoiceRepositoryInterface;
use App\Modules\Invoices\Application\Dto\CompanyDto;
use App\Modules\Invoices\Application\Dto\InvoiceDto;
use App\Modules\Invoices\Application\Dto\ProductDto;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\InvoiceService;
use App\Modules\Invoices\Infrastructure\Repositories\EloquentInvoiceRepository;
use Mockery;
use Ramsey\Uuid\Uuid;
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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetInvoiceByIdShouldReturnInvoiceDtoWhenInvoiceExists(): void
    {
        $invoiceId = Uuid::uuid4()->toString();

        $invoice = Invoice::factory()
            ->for(Company::factory()->create(), 'billedCompany')
            ->hasAttached(
                Product::factory()->count(3),
                function ($product) {
                    return [
                        'id' => Uuid::uuid4()->toString(),
                        'quantity' => 2,
                    ];
                },
                'products'
            )
            ->approved()
            ->create();

        $returnedInvoiceMock = Mockery::mock($invoice);
        $returnedInvoiceMock->shouldReceive('with')
            ->once()
            ->with(['billedCompany', 'products'])
            ->andReturnSelf();

        $returnedInvoiceMock->shouldReceive('first')
            ->andReturn($invoice);

        $this->invoiceRepository->shouldReceive('findById')
            ->once()
            ->with($invoiceId)
            ->andReturn($returnedInvoiceMock);

        $result = $this->invoiceService->getInvoiceById($invoiceId);

        $productDtos = $invoice->products->map(function ($product) {
            return new ProductDto(
                $product->id,
                $product->name,
                $product->pivot->quantity,
                $product->price,
                $product->total
            );
        });

        $expectedInvoiceDto = new InvoiceDto(
            $invoice->id,
            $invoice->number,
            $invoice->status,
            $invoice->date,
            $invoice->due_date,
            new CompanyDto(
                $invoice->company->id,
                $invoice->company->name,
                $invoice->company->street,
                $invoice->company->city,
                $invoice->company->zip,
                $invoice->company->phone,
                $invoice->company->email,
            ),
            new CompanyDto(
                $invoice->billedCompany->id,
                $invoice->billedCompany->name,
                $invoice->billedCompany->street,
                $invoice->billedCompany->city,
                $invoice->billedCompany->zip,
                $invoice->billedCompany->phone,
                $invoice->billedCompany->email,
            ),
            $productDtos->toArray(),
            $invoice->total
        );

        $this->assertEquals($expectedInvoiceDto, $result);
    }

    public function testGetInvoiceByIdShouldThrowInvoiceNotFoundExceptionWhenInvoiceNotFound(): void
    {
        $invoiceId = Uuid::uuid4()->toString();

        $returnedInvoiceMock = Mockery::mock(Invoice::class);
        $returnedInvoiceMock->shouldReceive('with')
            ->once()
            ->with(['billedCompany', 'products'])
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
