<?php

namespace App\Domain\Exception;

class DomainRecordRequestException extends DomainException
{
    const DEFAULT_MESSAGE = 'Error requesting domain record';

    public function __construct($message = null, $code = 0) {
        $messageToParent = $message ?? self::DEFAULT_MESSAGE;
        parent::__construct($messageToParent, $code);
    }
}