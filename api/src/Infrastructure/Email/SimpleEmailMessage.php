<?php
declare(strict_types=1);

namespace App\Infrastructure\Email;

class SimpleEmailMessage implements EmailMessage
{
    /**
     * @todo make it error save and write unit tests for it
     */

    const TEMPLATE_PATH = '/api/templates/email/';

    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $substitutions;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var array
     */
    private $recipients;

    public function __construct(string $template, array $substitutions, string $subject, array $recipients)
    {
        $this->template = $template;
        $this->substitutions = $substitutions;
        $this->subject = $subject;
        $this->recipients = $recipients;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): string
    {
        $body = file_get_contents(self::TEMPLATE_PATH . $this->template);
        foreach ($this->substitutions as $key => $value) {
            $body = str_replace('{{ ' . $key . ' }}', $value, $body);
        }
        return $body;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }
}