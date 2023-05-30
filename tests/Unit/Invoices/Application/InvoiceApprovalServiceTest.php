<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Application;

use App\Domain\Enums\StatusEnum;
use App\Domain\Company;
use App\Domain\Invoice;
use App\Domain\Product;
use App\Modules\Approval\Api\ApprovalFacadeInterface;
use App\Modules\Approval\Api\Dto\ApprovalDto;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\InvoiceApprovalService;
use App\Domain\Repositories\InvoiceRepositoryInterface;
use App\Modules\Invoices\Infrastructure\Repositories\EloquentInvoiceRepository;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

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
            ->create([
                'status' => StatusEnum::DRAFT->value,
            ]);

        $this->invoiceRepository->expects($this->once())
            ->method('findById')
            ->with($invoice->id)
            ->willReturn($invoice);

        $approvalDto = new ApprovalDto(
            Uuid::fromString($invoice->id),
            StatusEnum::DRAFT,
            Invoice::class
        );

        $this->approvalFacade->expects($this->once())
            ->method('approve')
            ->with($approvalDto);

        $this->invoiceApprovalService->approveInvoice($invoice->id);

        $this->assertSame(StatusEnum::APPROVED, $invoice->status);
    }

    public function testRejectInvoiceShouldUpdateInvoiceStatusAndCallApprovalFacadeWithApprovalDto(): void
    {
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
            ->create([
                'status' => StatusEnum::DRAFT->value,
            ]);

        $this->invoiceRepository->expects($this->once())
            ->method('findById')
            ->with($invoice->id)
            ->willReturn($invoice);

        $approvalDto = new ApprovalDto(
            Uuid::fromString($invoice->id),
            StatusEnum::DRAFT,
            Invoice::class
        );

        $this->approvalFacade->expects($this->once())
            ->method('reject')
            ->with($approvalDto);

        $this->invoiceApprovalService->rejectInvoice($invoice->id);

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
