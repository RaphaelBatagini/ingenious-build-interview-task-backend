<?php

namespace Tests\Unit\Modules\Invoices\Application;

use App\Domain\Enums\StatusEnum;
use App\Domain\Invoice;
use App\Modules\Approval\Api\ApprovalFacadeInterface;
use App\Modules\Approval\Api\Dto\ApprovalDto;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\InvoiceApprovalService;
use App\Domain\Repositories\InvoiceRepositoryInterface;
use App\Modules\Invoices\Infrastructure\Repositories\EloquentInvoiceRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class InvoiceApprovalServiceTest extends TestCase
{
    protected ApprovalFacadeInterface $approvalFacade;
    protected InvoiceRepositoryInterface $invoiceRepository;
    protected InvoiceApprovalService $invoiceApprovalService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->approvalFacade = $this->createMock(ApprovalFacadeInterface::class);
        $this->invoiceRepository = $this->createMock(EloquentInvoiceRepository::class);
        $this->invoiceApprovalService = new InvoiceApprovalService($this->approvalFacade, $this->invoiceRepository);
    }

    public function testApproveInvoiceShouldUpdateInvoiceStatusAndCallApprovalFacadeWithApprovalDto(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $invoice = new Invoice();
        $invoice->status = StatusEnum::DRAFT->value;

        $this->invoiceRepository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $approvalDto = new ApprovalDto(
            Uuid::fromString($invoiceId),
            StatusEnum::DRAFT,
            Invoice::class
        );

        $this->approvalFacade->expects($this->once())
            ->method('approve')
            ->with($approvalDto);

        $this->invoiceApprovalService->approveInvoice($invoiceId);

        $this->assertSame(StatusEnum::APPROVED, $invoice->status);
    }

    public function testRejectInvoiceShouldUpdateInvoiceStatusAndCallApprovalFacadeWithApprovalDto(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $invoice = new Invoice();
        $invoice->status = StatusEnum::DRAFT->value;

        $this->invoiceRepository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $approvalDto = new ApprovalDto(
            Uuid::fromString($invoiceId),
            StatusEnum::DRAFT,
            Invoice::class
        );

        $this->approvalFacade->expects($this->once())
            ->method('reject')
            ->with($approvalDto);

        $this->invoiceApprovalService->rejectInvoice($invoiceId);

        $this->assertSame(StatusEnum::REJECTED, $invoice->status);
    }

    public function testApproveInvoiceShouldThrowInvoiceNotFoundExceptionWhenInvoiceNotFound(): void
    {
        $invoiceId = Uuid::uuid4()->toString();

        $this->invoiceRepository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn(null);

        $this->expectException(InvoiceNotFoundException::class);

        $this->invoiceApprovalService->approveInvoice($invoiceId);
    }
}
