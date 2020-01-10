<?php
declare(strict_types=1);

namespace App\Queries\Subscription;

use App\Domain\Exception\DomainRecordNotFoundException;
use App\Infrastructure\Persistence\Subscription\SubscriptionRepository;
use App\Infrastructure\Persistence\User\UserRepository;
use App\Queries\Query;
use Psr\Log\LoggerInterface;

class ViewSubscriptionsQuery extends Query
{
    private $logger;
    private $subscriptionRepository;
    private $userRepository;

    public function __construct(
        LoggerInterface $logger,
        SubscriptionRepository $subscriptionRepository,
        UserRepository $userRepository
    )
    {
        $this->logger = $logger;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userRepository = $userRepository;
    }

    public function run($uuid)
    {
        $user = $this->userRepository->findUserOfUuid($uuid);
        if (is_null($user)) {
            throw new DomainRecordNotFoundException(sprintf('A user with uuid: %s could not be found for ViewSubscriptionsQuery', $uuid));
        }
        $subscriptions = $this->subscriptionRepository->findSubscriptionsOfUser($user);
        $this->logger->info(sprintf('Requested list of subscriptions for user %s.', $uuid));
        return $subscriptions;
    }
}