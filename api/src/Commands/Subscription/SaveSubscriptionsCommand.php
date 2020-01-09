<?php
declare(strict_types=1);

namespace App\Commands\Subscription;

use App\Commands\Command;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Infrastructure\Persistence\Subscription\SubscriptionRepository;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

class SaveSubscriptionsCommand extends Command
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

    public function run($data): array
    {
        $user = $this->userRepository->findUserOfUuid($data['userUuid']);
        if (is_null($user)) {
            throw new DomainRecordNotFoundException(sprintf('A user with uuid: %s could not be found for SaveSubscriptionCommand', $data['userUuid']));
        }
        return $this->subscriptionRepository->bulkSaveSubscriptions($user, $data['subscriptionTopics']);
    }
}