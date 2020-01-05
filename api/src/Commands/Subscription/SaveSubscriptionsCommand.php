<?php
declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Commands\Command;
use App\Infrastructure\Persistence\Subscription\SubscriptionRepository;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

class SaveSubscriptionsCommand extends Command
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

    public function run($data): array
    {
        //input format is going to be a list
        //userUuid => <uuid>
        //subscriptions => [
        // subscriptionUuid => <uuid>
        // active => <active>
        //]
        //we got name, email, password and a set of subscription_topic_uuids
        //1. look up user on email
        $subscriptions = [];
        $user = $this->userRepository->findUserOfEmail($data['email']);
        //1.1 if not exists, create user, send confirmation email, add subscriptions
        if (is_null($user)) {
            $user = $this->userRepository->createUser($data['name'], $data['email'], $data['password']);
        } else {
            //1.2 if exists, look up subscriptions for user, if one or more exist, throw error, else create subscriptions
        }
        //2 return subscriptions as array
    }
}