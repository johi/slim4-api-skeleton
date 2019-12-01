<?php
declare(strict_types=1);

namespace App\Domain\Exception;

class DomainRecordInvalidException extends DomainException
{
    const DEFAULT_MESSAGE = 'The domain record is invalid';

    public function __construct($message = null, $code = 0) {
        $messageToParent = $message ?? self::DEFAULT_MESSAGE;
        parent::__construct($messageToParent, $code);
    }
}
