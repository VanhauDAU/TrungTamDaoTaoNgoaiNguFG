<?php

namespace App\Exceptions;

use RuntimeException;

class MaintenanceConflictException extends RuntimeException
{
    public function __construct(
        protected array $impact,
        string $message = 'Phòng học đang có lịch học sắp diễn ra.'
    ) {
        parent::__construct($message);
    }

    public function getImpact(): array
    {
        return $this->impact;
    }
}
