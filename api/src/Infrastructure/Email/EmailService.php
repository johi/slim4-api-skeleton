<?php

namespace App\Infrastructure\Email;

interface EmailService
{

    /**
     * @param EmailMessage $messageInterface
     * @param string $senderKey
     * @return void
     */
    public function send(EmailMessage $messageInterface, string $senderKey = 'default'): void;

}