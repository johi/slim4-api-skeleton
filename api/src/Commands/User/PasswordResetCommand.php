<?php
declare(strict_types=1);

namespace App\Commands\User;

use App\Commands\Command;
use App\Domain\Exception\DomainRecordInvalidException;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

class PasswordResetCommand extends Command
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
        $token = $data['token'];
        $passwordRequest = $this->userRepository->findPasswordResetOfToken($token);
        if (is_null($passwordRequest)) {
            throw new DomainRecordNotFoundException(sprintf('PasswordReset of token: %s not found for PasswordResetCommand', $token));
        }
        $user = $this->userRepository->findUserOfUuid($passwordRequest->getUserUuid());
        if (is_null($user)) {
            throw new DomainRecordNotFoundException(sprintf('User of uuid: %s not found for PasswordResetCommand', $passwordRequest->getUserUuid()));
        }
        if ($this->userRepository->passwordResetIsValid($passwordRequest)) {
            $user = $this->userRepository->updatePassword($user, $data['password']);
            $uuid = $user->getUuid();
            $this->logger->info("User password `${uuid}` was updated using PasswordReset action.");
        } else {
            throw new DomainRecordInvalidException(sprintf('PasswordResetAction received an invalid token: %s', $token));
        }
        return $user;
    }
}