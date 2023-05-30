<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Application;

use App\Domain\Invoice;
use App\Domain\Repositories\InvoiceRepositoryInterface;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\InvoiceService;
use App\Modules\Invoices\Infrastructure\Repositories\EloquentInvoiceRepository;
use App\Modules\Invoices\Mappers\InvoiceMapper;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Tests\Unit\Traits\CreatesInvoices;

class InvoiceServiceTest extends TestCase
{
    use CreatesInvoices;

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

        $invoice = $this->createInvoice();

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

        $expectedInvoiceDto = InvoiceMapper::toDto($invoice);

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
