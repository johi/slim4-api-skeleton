<?php
declare(strict_types=1);

namespace App\Commands\User;

use App\Commands\Command;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

class LoginCommand extends Command
{
    private $logger;
    private $userRepository;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository)
    {
        $this->logger = $logger;
        $this->userRepository = $userRepository;
    }

    public function run($data)
    {
        $user = $this->userRepository->findUserOfEmail($data['email']);
        $jwt = $this->userRepository->login($user, $data['password']);
        //@todo need to know if its the first login, if so send welcome email
        //@todo need to know when the email bounces, marking email as sent
//            $this->emailService->send(
//                new SimpleEmailMessage('confirm.html', [
//                    'name' => $user->getName(),
//                    'token' => $userActivation->getToken()
//                ],
//                    'Please confirm your email',
//                    [$user->getEmail() => $user->getName()]
//                )
//            );
        //finally logging the registration
        $uuid = $user->getUuid();
        $this->logger->info("User of uuid `${uuid}` successfully logged in.");
        return $jwt;
    }
}