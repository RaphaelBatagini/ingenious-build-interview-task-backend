<?php

declare(strict_types=1);

namespace App\Modules\Approval\Application\Exceptions;

use RuntimeException;

class ApprovalStatusAlreadyAssignedException extends RuntimeException
{
    protected $message = 'Approval status is already assigned.';
}
