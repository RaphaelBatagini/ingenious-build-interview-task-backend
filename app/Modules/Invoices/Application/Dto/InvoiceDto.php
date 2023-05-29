<?php

namespace App\Modules\Invoices\Application\Dto;

class InvoiceDto
{
    public $number;
    public $status;
    public $date;
    public $due_date;
    public $company;
    public $products;
    public $total_price;

    public function __construct(
        string $number,
        string $status,
        string $date,
        string $due_date,
        CompanyDto $company,
        array $products,
        float $total_price
    ) {
        $this->number = $number;
        $this->status = $status;
        $this->date = $date;
        $this->due_date = $due_date;
        $this->company = $company;
        $this->products = $products;
        $this->total_price = $total_price;
    }
}
