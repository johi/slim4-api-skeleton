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
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @param LoggerInterface $logger
     * @param UserRepository  $userRepository
     */
    public function __construct(LoggerInterface $logger, UserRepository $userRepository, SubscriptionRepository $subscriptionRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
        $this->subscriptionRepository = $subscriptionRepository;
    }
}