<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Subscription;

use App\Domain\Subscription\SubscriptionTopic;
use App\Domain\Subscription\Subscription;

interface SubscriptionRepository
{

    /**
     * @return array
     */
    public function findAllSubscriptionTopics(): array;

    /**
     * @param string $uuid
     * @return Subscription|null
     */
    public function findSubscriptionOfUuid(string $uuid): ?Subscription;

    /**
     * @param string $userUuid
     * @param string $subscriptionTopicUuid
     * @return Subscription
     */
    public function findSubscriptionOfUserUuidAndSubscriptionTopicUuid(string $userUuid, string $subscriptionTopicUuid): ?Subscription;

    /**
     * @param SubscriptionTopic $subscriptionTopic
     * @param string $userUuid
     * @param bool $is_confirmed
     * @param bool $is_active
     * @return Subscription
     */
    public function createSubscription(SubscriptionTopic $subscriptionTopic, string $userUuid, bool $is_confirmed, bool $is_active): Subscription;

    /**
     * @param SubscriptionTopic $subscriptionTopic
     * @param string $userUuid
     * @param bool $is_confirmed
     * @param bool $is_active
     * @return Subscription
     */
    public function updateSubscription(SubscriptionTopic $subscriptionTopic, string $userUuid, bool $is_confirmed, bool $is_active): Subscription;

}