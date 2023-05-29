<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Api;

use App\Modules\Approval\Application\Exceptions\ApprovalStatusAlreadyAssignedException;
use App\Modules\Invoices\Api\InvoiceApprovalController;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\InvoiceApprovalService;
use Illuminate\Http\JsonResponse;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class InvoiceApprovalControllerTest extends TestCase
{
    protected InvoiceApprovalService $approvalService;
    protected InvoiceApprovalController $invoiceApprovalController;

    protected const INVOICE_NOT_FOUND_ERROR = 'Invoice not found.';
    protected const APPROVAL_STATUS_ALREADY_ASSIGNED_ERROR = 'Approval status already assigned.';
    protected const INVOICE_APPROVED_MESSAGE = 'Invoice approved';
    protected const INVOICE_REJECTED_MESSAGE = 'Invoice rejected';

    protected function setUp(): void
    {
        parent::setUp();

        $this->approvalService = Mockery::mock(InvoiceApprovalService::class);
        $this->invoiceApprovalController = new InvoiceApprovalController($this->approvalService);
    }

    public function testApproveReturnsJsonResponseWithSuccessMessage(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $this->approvalService->shouldReceive('approveInvoice')->once()->with($invoiceId);

        $response = $this->invoiceApprovalController->approve($invoiceId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['message' => self::INVOICE_APPROVED_MESSAGE], $response->getData(true));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testApproveReturnsJsonResponseWithErrorWhenInvoiceNotFound(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $this->approvalService->shouldReceive('approveInvoice')->once()->with($invoiceId)
            ->andThrow(new InvoiceNotFoundException(self::INVOICE_NOT_FOUND_ERROR));

        $response = $this->invoiceApprovalController->approve($invoiceId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => self::INVOICE_NOT_FOUND_ERROR], $response->getData(true));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testApproveReturnsJsonResponseWithErrorWhenApprovalStatusAlreadyAssigned(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $this->approvalService->shouldReceive('approveInvoice')->once()->with($invoiceId)
            ->andThrow(new ApprovalStatusAlreadyAssignedException(self::APPROVAL_STATUS_ALREADY_ASSIGNED_ERROR));

        $response = $this->invoiceApprovalController->approve($invoiceId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => self::APPROVAL_STATUS_ALREADY_ASSIGNED_ERROR], $response->getData(true));
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testRejectReturnsJsonResponseWithSuccessMessage(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $this->approvalService->shouldReceive('rejectInvoice')->once()->with($invoiceId);

        $response = $this->invoiceApprovalController->reject($invoiceId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['message' => self::INVOICE_REJECTED_MESSAGE], $response->getData(true));
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRejectReturnsJsonResponseWithErrorWhenInvoiceNotFound(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $this->approvalService->shouldReceive('rejectInvoice')->once()->with($invoiceId)
            ->andThrow(new InvoiceNotFoundException(self::INVOICE_NOT_FOUND_ERROR));

        $response = $this->invoiceApprovalController->reject($invoiceId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => self::INVOICE_NOT_FOUND_ERROR], $response->getData(true));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testRejectReturnsJsonResponseWithErrorWhenApprovalStatusAlreadyAssigned(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $this->approvalService->shouldReceive('rejectInvoice')->once()->with($invoiceId)
            ->andThrow(new ApprovalStatusAlreadyAssignedException(self::APPROVAL_STATUS_ALREADY_ASSIGNED_ERROR));

        $response = $this->invoiceApprovalController->reject($invoiceId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => self::APPROVAL_STATUS_ALREADY_ASSIGNED_ERROR], $response->getData(true));
        $this->assertEquals(400, $response->getStatusCode());
    }
}
