<?php
declare(strict_types=1);

namespace App\Queries\Subscription;

use App\Infrastructure\Persistence\Subscription\SubscriptionRepository;
use App\Infrastructure\Persistence\User\UserRepository;
use App\Queries\Query;
use Psr\Log\LoggerInterface;

class ListSubscriptionTopicsQuery extends Query
{
    private $logger;
    private $userRepository;
    private $subscriptionRepository;

    public function __construct(
        LoggerInterface $logger,
        UserRepository $userRepository,
        SubscriptionRepository $subscriptionRepository
    )
    {
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function run($data)
    {
        $subscriptionTopics = $this->subscriptionRepository->findAllSubscriptionTopics();
        $this->logger->info("Requested list of subscription topics.");
        return $subscriptionTopics;
    }
}