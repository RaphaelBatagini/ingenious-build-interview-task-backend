<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Api;

use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class InvoiceController extends Controller
{
    private $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function show($id): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById($id);

            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }

            return response()->json($invoice);
        } catch (InvoiceNotFoundException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }
    }
}
