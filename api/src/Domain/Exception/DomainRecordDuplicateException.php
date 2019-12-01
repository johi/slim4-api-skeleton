<?php
declare(strict_types=1);

namespace App\Domain\Exception;

class DomainRecordDuplicateException extends DomainException
{
    const DEFAULT_MESSAGE = 'Domain record already exists';

    public function __construct($message = null, $code = 0) {
        $messageToParent = $message ?? self::DEFAULT_MESSAGE;
        parent::__construct($messageToParent, $code);
    }
}
