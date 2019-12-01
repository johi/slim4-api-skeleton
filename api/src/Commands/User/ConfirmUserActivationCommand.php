<?php

declare(strict_types=1);

namespace App\Commands\User;

use App\Commands\Command;
use App\Domain\Exception\DomainRecordInvalidException;
use App\Domain\Exception\DomainRecordUpdateException;
use App\Domain\User\User;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

class ConfirmUserActivationCommand extends Command
{

    private $logger;
    private $userRepository;

    public function __construct(
        LoggerInterface $logger,
        UserRepository $userRepository
    )
    {
        $this->logger = $logger;
        $this->userRepository = $userRepository;
    }

    public function run($token): User
    {
        $userActivation = $this->userRepository->findUserActivationOfToken($token);
        $uuid = $userActivation->getUserUuid();
        $user = $this->userRepository->findUserOfUuid($uuid);
        if (is_null($user->getVerified())) {
            if ($this->userRepository->userActivationIsValid($userActivation)) {
                $user = $this->userRepository->activateUser($user);
                $this->logger->info("User of uuid `${uuid}` was activated.");
            } else {
                throw new DomainRecordInvalidException(sprintf('ConfirmUserActivationAction received an invalid token: %s', $token));
            }
        } else {
            throw new DomainRecordUpdateException(sprintf('User of uuid: %s has already been activated', $uuid));
        }
        return $user;
    }
}