<?php
declare(strict_types=1);

namespace App\Queries\User;

use App\Domain\Exception\DomainRecordInvalidException;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Infrastructure\Persistence\User\UserRepository;
use App\Queries\Query;
use Psr\Log\LoggerInterface;

class ConfirmPasswordResetQuery extends Query
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

    public function run($token)
    {
        $passwordReset = $this->userRepository->findPasswordResetOfToken($token);
        if (is_null($passwordReset)) {
            throw new DomainRecordNotFoundException(sprintf('PasswordReset of token: %s not found in ConfirmPasswordResetAction', $token));
        }
        if (!$this->userRepository->passwordResetIsValid($passwordReset)) {
            throw new DomainRecordInvalidException(sprintf('ConfirmPasswordResetAction received and invalid token: %s', $token));
        }
        $uuid = $passwordReset->getUserUuid();
        $this->logger->info("User of uuid: `${uuid}` confirmed password reset.");
        return $uuid;
    }
}