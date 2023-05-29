<?php

namespace App\Modules\Invoices\Application;

use App\Domain\Enums\StatusEnum;
use App\Modules\Approval\Api\ApprovalFacadeInterface;
use App\Modules\Approval\Api\Dto\ApprovalDto;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Domain\Invoice;
use App\Domain\Repositories\InvoiceRepositoryInterface;
use Ramsey\Uuid\Uuid;

class InvoiceApprovalService
{
    private $approvalFacade;
    private $invoiceRepository;

    public function __construct(ApprovalFacadeInterface $approvalFacade, InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->approvalFacade = $approvalFacade;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function approveInvoice(mixed $invoiceId): void
    {
        $this->updateInvoiceStatus($invoiceId, StatusEnum::APPROVED);
    }

    public function rejectInvoice(mixed $invoiceId): void
    {
        $this->updateInvoiceStatus($invoiceId, StatusEnum::REJECTED);
    }

    private function updateInvoiceStatus(mixed $invoiceId, StatusEnum $status): void
    {
        $invoice = $this->findInvoice($invoiceId);

        $approvalDto = new ApprovalDto(
            Uuid::fromString($invoiceId),
            StatusEnum::from($invoice->status),
            Invoice::class
        );

        if ($status === StatusEnum::APPROVED) {
            $this->approvalFacade->approve($approvalDto);
        } else {
            $this->approvalFacade->reject($approvalDto);
        }

        $invoice->setAttribute('status', $status);
        $this->invoiceRepository->save($invoice);
    }

    private function findInvoice(mixed $invoiceId): Invoice
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);

        if (!$invoice) {
            throw new InvoiceNotFoundException();
        }

        return $invoice;
    }
}
