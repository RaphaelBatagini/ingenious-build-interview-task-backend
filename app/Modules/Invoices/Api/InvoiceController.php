<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Api;

use App\Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use App\Modules\Invoices\Application\InvoiceService;
use Illuminate\Routing\Controller;

class InvoiceController extends Controller
{
    private $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function show($id)
    {
        try {
            $invoice = $this->invoiceService->getInvoiceById($id);

            if (!$invoice) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }

            $invoice->load('company', 'products');

            $invoiceData = [
                'number' => $invoice->number,
                'status' => $invoice->status,
                'date' => $invoice->date,
                'due_date' => $invoice->due_date,
                'company' => [
                    'name' => $invoice->company->name,
                    'street_address' => $invoice->company->street_address,
                    'city' => $invoice->company->city,
                    'zip_code' => $invoice->company->zip_code,
                    'phone' => $invoice->company->phone,
                ],
                // 'billed_company' => [
                //     'name' => $invoice->billed_company->name,
                //     'street_address' => $invoice->billed_company->street_address,
                //     'city' => $invoice->billed_company->city,
                //     'zip_code' => $invoice->billed_company->zip_code,
                //     'phone' => $invoice->billed_company->phone,
                //     'email_address' => $invoice->billed_company->email_address,
                // ],
                'products' => [],
                'total_price' => 0,
            ];

            foreach ($invoice->products as $product) {
                $productData = [
                    'name' => $product->name,
                    'quantity' => $product->pivot->quantity,
                    'unit_price' => $product->price,
                    'total' => $product->pivot->quantity * $product->price,
                ];

                $invoiceData['products'][] = $productData;
                $invoiceData['total_price'] += $productData['total'];
            }

            return response()->json($invoiceData);
        } catch (InvoiceNotFoundException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }
    }
}
