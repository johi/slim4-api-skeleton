<?php
declare(strict_types=1);

namespace App\Queries\User;

use App\Infrastructure\Persistence\User\UserRepository;
use App\Queries\Query;
use Psr\Log\LoggerInterface;

class ViewUserQuery extends Query
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

    public function run($uuid)
    {
        $user = $this->userRepository->findUserOfUuid($uuid);
        $this->logger->info("User of uuid `${uuid}` was viewed.");
        return $user;
    }
}