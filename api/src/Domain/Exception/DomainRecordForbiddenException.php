<?php
declare(strict_types=1);

namespace App\Domain\Exception;

class DomainRecordForbiddenException extends DomainException
{
    const DEFAULT_MESSAGE = 'Insufficient rights for the requested resource.';

    public function __construct($message = null, $code = 0) {
        $messageToParent = $message ?? self::DEFAULT_MESSAGE;
        parent::__construct($messageToParent, $code);
    }
}
