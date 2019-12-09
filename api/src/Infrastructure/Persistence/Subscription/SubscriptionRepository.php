<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Subscription;

use App\Domain\Subscription\Subscription;
use App\Domain\Subscription\Subscriber;

interface SubscriptionRepository
{

    /**
     * @return array
     */
    public function findAllSubscriptions(): array;

    /**
     * @param string $uuid
     * @return Subscriber|null
     */
    public function findSubscriberOfUuid(string $uuid): ?Subscriber;

    /**
     * @param string $userUuid
     * @param string $subscriptionUuid
     * @return Subscriber
     */
    public function findSubscriberOfUserUuidAndSubscriptionUuid(string $userUuid, string $subscriptionUuid): ?Subscriber;

    /**
     * @param Subscription $subscription
     * @param string $userUuid
     * @param bool $is_confirmed
     * @param bool $is_active
     * @return Subscriber
     */
    public function createSubscriber(Subscription $subscription, string $userUuid, bool $is_confirmed, bool $is_active): Subscriber;

    /**
     * @param Subscription $subscription
     * @param string $userUuid
     * @param bool $is_confirmed
     * @param bool $is_active
     * @return Subscriber
     */
    public function updateSubscriber(Subscription $subscription, string $userUuid, bool $is_confirmed, bool $is_active): Subscriber;

}