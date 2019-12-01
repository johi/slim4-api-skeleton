<?php
declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Infrastructure\Persistence\User\UserRepository;
use App\Infrastructure\Email\EmailService;
use Psr\Log\LoggerInterface;

abstract class UserActionWithEmail extends UserAction
{
    protected $emailService;

    /**
     * @param LoggerInterface $logger
     * @param UserRepository $userRepository
     * @param EmailService $emailService
     */
    public function __construct(LoggerInterface $logger, UserRepository $userRepository, EmailService $emailService)
    {
        parent::__construct($logger, $userRepository);
        $this->emailService = $emailService;
    }
}
