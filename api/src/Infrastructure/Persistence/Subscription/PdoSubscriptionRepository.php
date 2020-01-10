<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Subscription;

use App\Domain\Exception\DomainRecordDuplicateException;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\Exception\DomainServiceException;
use App\Domain\Subscription\Subscription;
use App\Domain\Subscription\SubscriptionTopic;
use App\Domain\User\User;
use App\Infrastructure\Database\PdoDatabaseService;
use PDO;
use PDOException;

class PdoSubscriptionRepository implements SubscriptionRepository
{

    private $pdoDatabaseConnection;
    private $pdoDatabaseService;

    public function __construct(PdoDatabaseService $service)
    {
        $this->pdoDatabaseService = $service;
        $this->pdoDatabaseConnection = $service->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function findAllSubscriptionTopics(): array
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from subscription_topics");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findAllSubscriptionTopics'));
        }
        $subscriptionTopics = [];
        foreach ($result as $row) {
            $subscriptionTopics[] = $this->getSubscriptionTopicFromRow($row);
        }
        return $subscriptionTopics;
    }

    /**
     * {@inheritdoc}
     */
    public function findSubscriptionTopicOfUuid(string $uuid): ?SubscriptionTopic
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from subscription_topics where uuid = :uuid");
            $statement->execute([
                ':uuid' => $uuid
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findSubscriptionTopicOfUuid with uuid: %s', $uuid));
        }
        if (isset($result['uuid'])) {
            return $this->getSubscriptionTopicFromRow($result);
        }
        return null;
    }

    public function findSubscriptionOfUuid(string $uuid): ?Subscription
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from subscriptions where uuid = :uuid");
            $statement->execute([
                ':uuid' => $uuid
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findSubscriptionOfUuid with uuid: %s', $uuid));
        }
        if (isset($result['uuid'])) {
            return $this->getSubscriptionFromRow($result);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findSubscriptionOfSubscriptionTopicAndUser(SubscriptionTopic $subscriptionTopic, User $user): ?Subscription
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from subscriptions where user_uuid = :userUuid and subscription_topic_uuid = :subscriptionTopicUuid");
            $statement->execute([
                ':userUuid' => $user->getUuid(),
                ':subscriptionTopicUuid' => $subscriptionTopic->getUuid()
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findSubscriptionOfUserUuidAndSubscriptionTopicUuid with user_uuid: %s and subscription_topic_uuid: %s', $user->getUuid(), $subscriptionTopic->getUuid()));
        }
        if (isset($result['uuid'])) {
            return $this->getSubscriptionFromRow($result);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findSubscriptionsOfUser(User $user): array
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from subscriptions where user_uuid = :userUuid");
            $statement->execute([
                ':userUuid' => $user->getUuid()
            ]);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findSubscriptionsOfUser for user_uuid: %s', $user->getUuid()));
        }
        $subscriptions = [];
        foreach ($result as $row) {
            $subscriptions[] = $this->getSubscriptionFromRow($row);
        }
        return $subscriptions;
    }

    /**
     * {@inheritdoc}
     */
    public function createSubscription(SubscriptionTopic $subscriptionTopic, User $user, bool $isActive): Subscription
    {
        if (is_null($this->findSubscriptionOfSubscriptionTopicAndUser($subscriptionTopic, $user))) {
            try {
                $uuid = $this->pdoDatabaseService->fetchUuid();
                $query = "insert into subscriptions (uuid, user_uuid, subscription_topic_uuid, is_active) values (:uuid, :userUuid, :subscriptionTopicUuid, :isActive)";
                $statement = $this->pdoDatabaseConnection->prepare($query);
                $statement->execute([
                    ':uuid' => $uuid,
                    ':userUuid' => $user->getUuid(),
                    ':subscriptionTopicUuid' => $subscriptionTopic->getUuid(),
                    ':isActive' => ($isActive) ? 't' : 'f'
                ]);
                return $this->findSubscriptionOfUuid($uuid);
            } catch (PDOException $e) {
                throw new DomainServiceException(sprintf('SQL query failed for createSubscription user_uuid: %s and subscription_topic_uuid: %s', $user->getUuid(), $subscriptionTopic->getUuid()));
            }
        } else {
            throw new DomainRecordDuplicateException(sprintf('A subscriber for user_uuid: %s and subscription_topic_uuid: %s already exists', $user->getUuid(), $subscriptionTopic->getUuid()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateSubscription(SubscriptionTopic $subscriptionTopic, User $user, bool $isActive): Subscription
    {
        $subscription = $this->findSubscriptionOfSubscriptionTopicAndUser($subscriptionTopic, $user);
        if (!is_null($subscription)) {
            try {
                $uuid = $subscription->getUuid();
                $query = "update subscriptions set is_active = :is_active where uuid = :uuid";
                $statement = $this->pdoDatabaseConnection->prepare($query);
                $statement->execute([
                    ':uuid' => $uuid,
                    ':is_active' => ($isActive) ? 't' : 'f'
                ]);
                return $this->findSubscriptionOfUuid($uuid);
            } catch (PDOException $e) {
                throw new DomainServiceException(sprintf('SQL query failed for updateSubscription user_uuid: %s and subscription_topic_uuid: %s', $user->getUuid(), $subscriptionTopic->getUuid()));
            }
        } else {
            throw new DomainRecordNotFoundException(sprintf('A subscriber for user_uuid: %s and subscription_topic_uuid: %s could not be found', $user->getUuid(), $subscriptionTopic->getUuid()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bulkSaveSubscriptions(User $user, array $subscriptionTopicUuidActivePair): array
    {
        $subscriptions = [];
        $this->pdoDatabaseService->startTransaction();
        try {
            foreach ($subscriptionTopicUuidActivePair as $pair) {
                $subscriptionTopic = $this->findSubscriptionTopicOfUuid($pair['uuid']);
                if (is_null($subscriptionTopic)) {
                    throw new DomainRecordNotFoundException(sprintf('SubscriptionTopic with uuid: %s could not be found for SaveSubscriptionCommand', $pair['uuid']));
                }
                $subscription = $this->findSubscriptionOfSubscriptionTopicAndUser($subscriptionTopic, $user);
                if (!is_null($subscription)) {
                    $subscriptions[] = $this->updateSubscription($subscriptionTopic, $user, $pair['active']);
                } else {
                    $subscriptions[] = $this->createSubscription($subscriptionTopic, $user, $pair['active']);
                }
            }
        } catch (DomainRecordNotFoundException $exception) {
            $this->pdoDatabaseService->rollbackTransaction();
            throw new DomainRecordNotFoundException($exception->getMessage());
        }
        $this->pdoDatabaseService->commitTransaction();
        return $subscriptions;
    }

    /**
     * @param array $result
     * @return SubscriptionTopic
     */
    private function getSubscriptionTopicFromRow(array $result): SubscriptionTopic
    {
        return new SubscriptionTopic(
            $result['uuid'],
            $result['name'],
            $result['description'],
            $result['created_at'],
            $result['updated_at']
        );
    }

    /**
     * @param array $result
     * @return Subscription
     */
    private function getSubscriptionFromRow(array $result): Subscription
    {
        return new Subscription(
            $result['uuid'],
            $result['user_uuid'],
            $result['subscription_topic_uuid'],
            $result['is_active'],
            $result['created_at'],
            $result['updated_at']
        );
    }
}