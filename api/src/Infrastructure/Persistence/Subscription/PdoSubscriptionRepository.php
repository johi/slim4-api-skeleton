<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Subscription;

use App\Domain\Exception\DomainRecordDuplicateException;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\Exception\DomainServiceException;
use App\Domain\Subscription\Subscriber;
use App\Domain\Subscription\Subscription;
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
    public function findAllSubscriptions(): array
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from subscriptions");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findAllSubscriptions'));
        }
        $subscriptions = [];
        foreach ($result as $row) {
            $subscriptions[] = $this->getSubscriptionFromRow($row);
        }
        return $subscriptions;
    }

    public function findSubscriberOfUuid(string $uuid): ?Subscriber
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from subscribers where uuid = :uuid");
            $statement->execute([
                ':uuid' => $uuid
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findSubscriberOfUuid with uuid: %s', $uuid));
        }
        if (isset($result['uuid'])) {
            return $this->getSubscriberFromRow($result);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findSubscriberOfUserUuidAndSubscriptionUuid(string $userUuid, string $subscriptionUuid): ?Subscriber
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from subscribers where user_uuid = :userUuid and subscription_uuid = :subscriptionUuid");
            $statement->execute([
                ':userUuid' => $userUuid,
                ':subscriptionUuid' => $subscriptionUuid
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findSubscriberByUserUuidAndSubscriptionUuid with user_uuid: %s and subscription_uuid: %s', $userUuid, $subscriptionUuid));
        }
        if (isset($result['uuid'])) {
            return $this->getSubscriberFromRow($result);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function createSubscriber(Subscription $subscription, string $userUuid, bool $isConfirmed, bool $isActive): Subscriber
    {
        if (is_null($this->findSubscriberOfUserUuidAndSubscriptionUuid($userUuid, $subscription->getUuid()))) {
            try {
                $uuid = $this->pdoDatabaseService->fetchUuid();
                $query = "insert into subscribers (uuid, user_uuid, subscription_uuid, is_confirmed, is_active) values (:uuid, :userUuid, :subscriptionUuid, :isConfirmed, :isActive)";
                $statement = $this->pdoDatabaseConnection->prepare($query);
                $statement->execute([
                    ':uuid' => $uuid,
                    ':userUuid' => $userUuid,
                    ':subscriptionUuid' => $subscription->getUuid(),
                    ':isConfirmed' => ($isConfirmed) ? 't' : 'f',
                    ':isActive' => ($isActive) ? 't' : 'f'
                ]);
                return $this->findSubscriberOfUuid($uuid);
            } catch (PDOException $e) {
                var_dump($e->getMessage());
                throw new DomainServiceException(sprintf('SQL query failed for createSubscriber user_uuid: %s and subscription_uuid: %s', $userUuid, $subscription->getUuid()));
            }
        } else {
            throw new DomainRecordDuplicateException(sprintf('A subscriber for user_uuid: %s and subscription_uuid: %s already exists', $userUuid, $subscription->getUuid()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateSubscriber(Subscription $subscription, string $userUuid, bool $isConfirmed, bool $isActive): Subscriber
    {
        $subscriber = $this->findSubscriberOfUserUuidAndSubscriptionUuid($userUuid, $subscription->getUuid());
        if (!is_null($subscriber)) {
            try {
                $uuid = $subscriber->getUuid();
                $query = "update subscribers set is_confirmed = :is_confirmed, is_active = :is_active where uuid = :uuid";
                $statement = $this->pdoDatabaseConnection->prepare($query);
                $statement->execute([
                    ':uuid' => $uuid,
                    ':is_confirmed' => ($isConfirmed) ? 't' : 'f',
                    ':is_active' => ($isActive) ? 't' : 'f'
                ]);
                return $this->findSubscriberOfUuid($uuid);
            } catch (PDOException $e) {
                var_dump($e->getMessage());
                throw new DomainServiceException(sprintf('SQL query failed for updateSubscriber user_uuid: %s and subscription_uuid: %s', $userUuid, $subscription->getUuid()));
            }
        } else {
            throw new DomainRecordNotFoundException(sprintf('A subscriber for user_uuid: %s and subscription_uuid: %s could not be found', $userUuid, $subscription->getUuid()));
        }
    }

    /**
     * @param array $result
     * @return Subscription
     */
    private function getSubscriptionFromRow(array $result): Subscription
    {
        return new Subscription(
            $result['uuid'],
            $result['name'],
            $result['description'],
            $result['created_at'],
            $result['updated_at']
        );
    }

    /**
     * @param array $result
     * @return Subscriber
     */
    private function getSubscriberFromRow(array $result): Subscriber
    {
        return new Subscriber(
            $result['uuid'],
            $result['user_uuid'],
            $result['subscription_uuid'],
            $result['is_confirmed'],
            $result['is_active'],
            $result['created_at'],
            $result['updated_at']
        );
    }
}