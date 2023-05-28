<?php

namespace App\Modules\Invoices\Application\Exceptions;

use RuntimeException;

class InvoiceNotFoundException extends RuntimeException
{
    protected $message = 'Invoice not found.';
}
