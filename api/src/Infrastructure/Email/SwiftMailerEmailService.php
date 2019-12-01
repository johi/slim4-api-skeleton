<?php

namespace App\Infrastructure\Email;

use App\Application\Configuration\AppConfiguration;
use App\Infrastructure\Database\EmailServiceException;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class SwiftMailerEmailService implements EmailService
{
    private $host;
    private $port;
    private $mailer;
    private $senders;

    public function __construct()
    {
        $emailSettings = AppConfiguration::getKey('email');
        if (!isset($emailSettings['host'])) {
            throw new EmailServiceException('Missing smtp host');
        }
        $this->host = $emailSettings['host'];
        if (!isset($emailSettings['port'])) {
            throw new EmailServiceException('Missing port');
        }
        $this->port = $emailSettings['port'];
        $this->mailer = new Swift_Mailer(new Swift_SmtpTransport($this->host, $this->port));
        if (!isset($emailSettings['senders'])) {
            throw new EmailServiceException('Missing senders configuration');
        }
        $this->senders = $emailSettings['senders'];
    }

    public function send(EmailMessage $messageInterface, string $senderKey = 'default'): void
    {
        $message = new Swift_Message();
        $message->setContentType("text/html");
        $message->setSubject($messageInterface->getSubject());
        $message->setFrom($this->senders[$senderKey]);
        $message->setTo($messageInterface->getRecipients());
        $message->setBody($messageInterface->getBody());
        $this->mailer->send($message);
    }
}