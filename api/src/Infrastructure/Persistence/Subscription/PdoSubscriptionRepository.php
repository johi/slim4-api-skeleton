<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Subscription;

use App\Domain\Exception\DomainRecordDuplicateException;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\Exception\DomainServiceException;
use App\Domain\Subscription\Subscription;
use App\Domain\Subscription\SubscriptionTopic;
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
        $subscriptions = [];
        foreach ($result as $row) {
            $subscriptions[] = $this->getSubscriptionTopicFromRow($row);
        }
        return $subscriptions;
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
    public function findSubscriptionOfUserUuidAndSubscriptionTopicUuid(string $userUuid, string $subscriptionTopicUuid): ?Subscription
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from subscriptions where user_uuid = :userUuid and subscription_topic_uuid = :subscriptionTopicUuid");
            $statement->execute([
                ':userUuid' => $userUuid,
                ':subscriptionTopicUuid' => $subscriptionTopicUuid
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findSubscriptionOfUserUuidAndSubscriptionTopicUuid with user_uuid: %s and subscription_topic_uuid: %s', $userUuid, $subscriptionTopicUuid));
        }
        if (isset($result['uuid'])) {
            return $this->getSubscriptionFromRow($result);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function createSubscription(SubscriptionTopic $subscriptionTopic, string $userUuid, bool $isActive): Subscription
    {
        if (is_null($this->findSubscriptionOfUserUuidAndSubscriptionTopicUuid($userUuid, $subscriptionTopic->getUuid()))) {
            try {
                $uuid = $this->pdoDatabaseService->fetchUuid();
                $query = "insert into subscriptions (uuid, user_uuid, subscription_topic_uuid, is_active) values (:uuid, :userUuid, :subscriptionTopicUuid, :isActive)";
                $statement = $this->pdoDatabaseConnection->prepare($query);
                $statement->execute([
                    ':uuid' => $uuid,
                    ':userUuid' => $userUuid,
                    ':subscriptionTopicUuid' => $subscriptionTopic->getUuid(),
                    ':isActive' => ($isActive) ? 't' : 'f'
                ]);
                return $this->findSubscriptionOfUuid($uuid);
            } catch (PDOException $e) {
                var_dump($e->getMessage());
                throw new DomainServiceException(sprintf('SQL query failed for createSubscription user_uuid: %s and subscription_topic_uuid: %s', $userUuid, $subscriptionTopic->getUuid()));
            }
        } else {
            throw new DomainRecordDuplicateException(sprintf('A subscriber for user_uuid: %s and subscription_topic_uuid: %s already exists', $userUuid, $subscriptionTopic->getUuid()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateSubscription(SubscriptionTopic $subscriptionTopic, string $userUuid, bool $isActive): Subscription
    {
        $subscription = $this->findSubscriptionOfUserUuidAndSubscriptionTopicUuid($userUuid, $subscriptionTopic->getUuid());
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
                throw new DomainServiceException(sprintf('SQL query failed for updateSubscription user_uuid: %s and subscription_topic_uuid: %s', $userUuid, $subscriptionTopic->getUuid()));
            }
        } else {
            throw new DomainRecordNotFoundException(sprintf('A subscriber for user_uuid: %s and subscription_topic_uuid: %s could not be found', $userUuid, $subscriptionTopic->getUuid()));
        }
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