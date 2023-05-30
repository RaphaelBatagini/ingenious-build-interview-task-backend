<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Application\Dto;

class InvoiceDto
{
    public $id;
    public $number;
    public $status;
    public $date;
    public $dueDate;
    public $company;
    public $billedCompany;
    public $products;
    public $totalPrice;

    public function __construct(
        string $id,
        string $number,
        string $status,
        string $date,
        string $dueDate,
        CompanyDto $company,
        CompanyDto $billedCompany,
        array $products,
        float $totalPrice
    ) {
        $this->id = $id;
        $this->number = $number;
        $this->status = $status;
        $this->date = $date;
        $this->dueDate = $dueDate;
        $this->company = $company;
        $this->billedCompany = $billedCompany;
        $this->products = $products;
        $this->totalPrice = $totalPrice;
    }
}
