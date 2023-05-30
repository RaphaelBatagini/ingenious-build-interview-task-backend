<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Application\Dto;

class CompanyDto
{
    public $id;
    public $name;
    public $street;
    public $city;
    public $zip_code;
    public $phone;
    public $email;

    public function __construct(
        string $id,
        string $name,
        string $street,
        string $city,
        string $zip_code,
        string $phone,
        string $email
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->street = $street;
        $this->city = $city;
        $this->zip_code = $zip_code;
        $this->phone = $phone;
        $this->email = $email;
    }
}
