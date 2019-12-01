<?php
declare(strict_types=1);

namespace App\Domain\Exception;

class DomainRecordNotFoundException extends DomainException
{
    const DEFAULT_MESSAGE = 'Domain record not found';

    public function __construct($message = null, $code = 0) {
        $messageToParent = $message ?? self::DEFAULT_MESSAGE;
        parent::__construct($messageToParent, $code);
    }
}
