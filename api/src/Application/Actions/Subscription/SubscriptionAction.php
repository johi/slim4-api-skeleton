<?php
declare(strict_types=1);

namespace App\Application\Actions\Subscription;

use App\Application\Actions\Action;
use App\Infrastructure\Persistence\Subscription\SubscriptionRepository;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

abstract class SubscriptionAction extends Action
{

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @param LoggerInterface $logger
     * @param SubscriptionRepository $subscriptionRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        LoggerInterface $logger,
        SubscriptionRepository $subscriptionRepository,
        UserRepository $userRepository
    )
    {
        parent::__construct($logger);
        $this->subscriptionRepository = $subscriptionRepository;
        $this->userRepository = $userRepository;
    }
}