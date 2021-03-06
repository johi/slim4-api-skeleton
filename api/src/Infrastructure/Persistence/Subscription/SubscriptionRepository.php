<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Subscription;

use App\Domain\Subscription\SubscriptionTopic;
use App\Domain\Subscription\Subscription;
use App\Domain\User\User;

interface SubscriptionRepository
{

    /**
     * @return array
     */
    public function findAllSubscriptionTopics(): array;

    /**
     * @param string $uuid
     * @return SubscriptionTopic|null
     */
    public function findSubscriptionTopicOfUuid(string $uuid): ?SubscriptionTopic;

    /**
     * @param string $uuid
     * @return Subscription|null
     */
    public function findSubscriptionOfUuid(string $uuid): ?Subscription;

    /**
     * @param SubscriptionTopic $subscriptionTopic
     * @param User $user
     * @return Subscription|null
     */
    public function findSubscriptionOfSubscriptionTopicAndUser(SubscriptionTopic $subscriptionTopic, User $user): ?Subscription;

    /**
     * @param User $user
     * @return array
     */
    public function findSubscriptionsOfUser(User $user): array;

    /**
     * @param SubscriptionTopic $subscriptionTopic
     * @param User $user
     * @param bool $is_active
     * @return Subscription
     */
    public function createSubscription(SubscriptionTopic $subscriptionTopic, User $user, bool $is_active): Subscription;

    /**
     * @param SubscriptionTopic $subscriptionTopic
     * @param User $user
     * @param bool $is_active
     * @return Subscription
     */
    public function updateSubscription(SubscriptionTopic $subscriptionTopic, User $user, bool $is_active): Subscription;

    /**
     * @param User $user
     * @param array $subscriptionTopicUuidActivePair
     * @return array
     */
    public function bulkSaveSubscriptions(User $user, array $subscriptionTopicUuidActivePair): array;
}