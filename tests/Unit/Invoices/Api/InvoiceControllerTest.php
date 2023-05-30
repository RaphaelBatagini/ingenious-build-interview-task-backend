<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Api;

use App\Modules\Invoices\Api\InvoiceController;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\InvoiceService;
use App\Modules\Invoices\Mappers\InvoiceMapper;
use Illuminate\Http\JsonResponse;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Tests\Unit\Traits\CreatesInvoices;

class InvoiceControllerTest extends TestCase
{
    use CreatesInvoices;

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
        $invoice = $this->createInvoice();

        $invoiceDto = InvoiceMapper::toDto($invoice);

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
