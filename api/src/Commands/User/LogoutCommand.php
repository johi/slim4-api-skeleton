<?php
declare(strict_types=1);

namespace App\Commands\User;

use App\Commands\Command;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

class LogoutCommand extends Command
{
    private $logger;
    private $userRepository;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository)
    {
        $this->logger = $logger;
        $this->userRepository = $userRepository;
    }

    public function run($userUuid)
    {
        $user = $this->userRepository->findUserOfUuid($userUuid);
        if (is_null($user)) {
            throw new DomainRecordNotFoundException(sprintf('User of uuid: %s not found for LogoutAction', $userUuid));
        }
        $this->userRepository->invalidateUserLogins($user);
        $this->logger->info("User of uuid `${userUuid}` successfully logged out.");
        return $user;
    }
}