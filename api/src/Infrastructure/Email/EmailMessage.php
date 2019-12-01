<?php

namespace App\Infrastructure\Email;

interface EmailMessage
{

    /**
     * @return string
     */
    public function getSubject(): string;

    /**
     * @return string
     */
    public function getBody(): string;

    /**
     * @return array
     */
    public function getRecipients(): array;

}