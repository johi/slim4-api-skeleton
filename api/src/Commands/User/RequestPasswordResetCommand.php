<?php
declare(strict_types=1);

namespace App\Commands\User;

use App\Commands\Command;
use App\Infrastructure\Email\EmailService;
use App\Infrastructure\Email\SimpleEmailMessage;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

class RequestPasswordResetCommand extends Command
{
    private $logger;
    private $userRepository;
    private $emailService;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository, EmailService $emailService)
    {
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }

    public function run($data)
    {
        $user = $this->userRepository->findUserOfEmail($data['email']);
        $passwordReset = $this->userRepository->createPasswordReset($user);
        $this->emailService->send(
            new SimpleEmailMessage('forgotPassword.html', [
                'name' => $user->getName(),
                'token' => $passwordReset->getToken()
            ],
                'Your password reset link',
                [$user->getEmail() => $user->getName()]
            )
        );
        $uuid = $passwordReset->getUserUuid();
        $this->logger->info("Forgot password link for uuid `${uuid}` was requested.");
        return $user;
    }
}