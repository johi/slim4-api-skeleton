<?php
declare(strict_types=1);

namespace App\Domain\Exception;

class DomainServiceException extends DomainException
{
    const DEFAULT_MESSAGE = 'A domain service encountered a severe error';

    public function __construct($message = null, $code = 0) {
        $messageToParent = $message ?? self::DEFAULT_MESSAGE;
        parent::__construct($messageToParent, $code);
    }
}
