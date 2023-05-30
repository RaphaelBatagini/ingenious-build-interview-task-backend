<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Api;

use App\Domain\Company;
use App\Domain\Invoice;
use App\Domain\Product;
use App\Modules\Invoices\Api\InvoiceController;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\InvoiceService;
use App\Modules\Invoices\Application\Dto\CompanyDto;
use App\Modules\Invoices\Application\Dto\InvoiceDto;
use App\Modules\Invoices\Application\Dto\ProductDto;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;

class InvoiceControllerTest extends TestCase
{
    protected $invoiceService;
    protected $invoiceController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceService = Mockery::mock(InvoiceService::class);
        $this->invoiceController = new InvoiceController($this->invoiceService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testShowReturnsJsonResponseWithInvoiceData()
    {
        $invoice = Invoice::factory()
            ->for(Company::factory()->create())
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

        $productDtos = $invoice->products->map(function ($product) {
            return new ProductDto(
                $product->id,
                $product->name,
                $product->pivot->quantity,
                $product->price,
                $product->total
            );
        });

        $invoiceDto = new InvoiceDto(
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
            $productDtos->toArray(),
            $invoice->total
        );

        $expectedInvoiceObject = json_decode(json_encode($invoiceDto), true);

        $this->invoiceService
            ->shouldReceive('getInvoiceById')
            ->once()
            ->with($invoice->id)
            ->andReturn($invoiceDto);

        $response = $this->invoiceController->show($invoice->id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($expectedInvoiceObject, $response->getData(true));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShowReturnsJsonResponseWithErrorWhenInvoiceNotFound()
    {
        $invoiceId = Uuid::uuid4()->toString();

        $this->invoiceService
            ->shouldReceive('getInvoiceById')
            ->once()
            ->with($invoiceId)
            ->andThrow(new InvoiceNotFoundException('Invoice not found.'));

        $response = $this->invoiceController->show($invoiceId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => 'Invoice not found.'], $response->getData(true));
        $this->assertEquals(404, $response->getStatusCode());
    }
}
