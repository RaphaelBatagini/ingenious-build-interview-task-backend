<?php

declare(strict_types=1);

namespace App\Modules\Invoices\Application\Exceptions;

use RuntimeException;

class InvoiceNotFoundException extends RuntimeException
{
    protected $message = 'Invoice not found.';
}
