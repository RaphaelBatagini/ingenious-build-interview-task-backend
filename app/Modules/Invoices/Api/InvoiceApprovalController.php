<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Api;

use App\Modules\Invoices\Application\InvoiceApprovalService;
use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Approval\Application\Exceptions\ApprovalStatusAlreadyAssignedException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class InvoiceApprovalController extends Controller
{
    private $approvalService;

    public function __construct(InvoiceApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function approve($invoiceId)
    {
        $validator = Validator::make(['value' => $invoiceId], [
            'value' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $this->approvalService->approveInvoice($invoiceId);
        } catch (InvoiceNotFoundException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        } catch (ApprovalStatusAlreadyAssignedException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }

        return response()->json(['message' => 'Invoice approved']);
    }

    public function reject($invoiceId)
    {
        $validator = Validator::make(['value' => $invoiceId], [
            'value' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $this->approvalService->rejectInvoice($invoiceId);
        } catch (InvoiceNotFoundException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        } catch (ApprovalStatusAlreadyAssignedException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }

        return response()->json(['message' => 'Invoice rejected']);
    }
}
