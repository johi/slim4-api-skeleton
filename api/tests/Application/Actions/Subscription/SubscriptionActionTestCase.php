<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Subscription;

use App\Domain\Subscription\Subscription;
use App\Domain\Subscription\SubscriptionTopic;
use Tests\Application\Actions\User\UserActionTestCase;

abstract class SubscriptionActionTestCase extends UserActionTestCase
{
    const SUBSCRIPTION_TOPIC_NAME = 'newsletter';
    const SUBSCRIPTION_TOPIC_DESCRIPTION  = 'description';

    public function getSubscriptionTopic() {
        return new SubscriptionTopic(
            self::UUID_UNDER_TEST,
            self::SUBSCRIPTION_TOPIC_NAME,
            self::SUBSCRIPTION_TOPIC_DESCRIPTION,
            self::CREATED_TIMESTAMP,
            self::UPDATED_TIMESTAMP
        );
    }

    public function getSubscription() {
        return new Subscription(
            self::UUID_UNDER_TEST,
            self::UUID_UNDER_TEST,
            self::UUID_UNDER_TEST,
            true,
            self::CREATED_TIMESTAMP,
            self::UPDATED_TIMESTAMP
        );
    }
}