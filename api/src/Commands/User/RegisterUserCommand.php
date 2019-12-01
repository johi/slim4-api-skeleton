<?php
declare(strict_types=1);

namespace App\Commands\User;

use App\Commands\Command;
use App\Domain\User\User;
use App\Infrastructure\Email\EmailService;
use App\Infrastructure\Email\SimpleEmailMessage;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

class RegisterUserCommand extends Command
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

    public function run($data): User
    {
        $user = $this->userRepository->createUser($data['name'], $data['email'], $data['password']);
        $userActivation = $this->userRepository->createUserActivation($user);
        //@todo need to know when the email bounces, marking email as sent, need to rethink domain model?
        $this->emailService->send(
            new SimpleEmailMessage('confirm.html', [
                'name' => $user->getName(),
                'token' => $userActivation->getToken()
            ],
                'Please confirm your email',
                [$user->getEmail() => $user->getName()]
            )
        );
        //finally logging the registration
        $uuid = $user->getUuid();
        $this->logger->info("User of uuid `${uuid}` was registered.");
        return $user;
    }
}