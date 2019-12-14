<?php
declare(strict_types=1);

namespace App\Commands\User;

use App\Commands\Command;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\Exception\DomainRecordRequestException;
use App\Domain\Exception\DomainRecordUpdateException;
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
        if (is_null($user)) {
            throw new DomainRecordNotFoundException(sprintf('User of email: %s not found for LoginAction', $data['email']));
        }
        if (is_null($user->getVerified())) {
            throw new DomainRecordUpdateException(sprintf('User of uuid: %s has not been activated yet for LoginAction', $user->getUuid()));
        }
        if (!$this->userRepository->verifyPassword($data['email'], $data['password'])) {
            throw new DomainRecordRequestException(sprintf('Password validation failed for user of uuid: %s for LoginAction', $user->getUuid()));
        }
        $jwt = $this->userRepository->login($user);
        //@todo need to know if its the first login, if so send welcome email
        //@todo need to know when the email bounces, marking email as sent
        $uuid = $user->getUuid();
        $this->logger->info("User of uuid `${uuid}` successfully logged in.");
        return $jwt;
    }
}