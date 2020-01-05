<?php
declare(strict_types=1);

// namespace Tests\Database\Seeds\User;

use App\Domain\Subscription\SubscriptionTopic;
use App\Domain\Subscription\Subscription;
use Phinx\Seed\AbstractSeed;

class BaseSubscriptionSeeder extends AbstractSeed
{
    const DEFAULT_SUBSCRIPTION_TOPIC_UUID = '00000000-0000-0000-0000-000000000000';
    const DEFAULT_SUBSCRIPTION_TOPIC_NAME = 'Weekly Newsletter';
    const DEFAULT_SUBSCRIPTION_TOPIC_DESCRIPTION = 'The most important take aways from the ongoing week';
    const DEFAULT_CREATED_AT = '2019-10-05 08:00:00+00';
    const DEFAULT_UPDATED_AT = '2019-10-05 08:00:00+00';
    const DEFAULT_SUBSCRIBER_UUID = '10000000-0000-0000-0000-000000000000';
    const DEFAULT_USER_UUID = '10000000-0000-0000-0000-000000000000';
    const DEFAULT_SUBSCRIBER_IS_ACTIVE = true;

    private static $subscriptionTopicsTable;
    private static $subscriptionsTable;

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        self::$subscriptionTopicsTable = $this->table('subscription_topics');
        self::$subscriptionsTable = $this->table('subscriptions');
    }

    public static function addSubscriptionTopic(array $overrides = []): SubscriptionTopic
    {
        $subscriptionArray = [
            'uuid' => $overrides['uuid'] ?? self::DEFAULT_SUBSCRIPTION_TOPIC_UUID,
            'name' => $overrides['name'] ?? self::DEFAULT_SUBSCRIPTION_TOPIC_NAME,
            'description' => $overrides['email'] ??  self::DEFAULT_SUBSCRIPTION_TOPIC_DESCRIPTION,
            'created_at' => $overrides['created_at'] ?? self::DEFAULT_CREATED_AT,
            'updated_at' => $overrides['updated_at'] ?? self::DEFAULT_UPDATED_AT
        ];
        self::$subscriptionTopicsTable->insert([
            $subscriptionArray
        ])->save();
        return new SubscriptionTopic(
            $subscriptionArray['uuid'],
            $subscriptionArray['name'],
            $subscriptionArray['description'],
            $subscriptionArray['created_at'],
            $subscriptionArray['updated_at']
        );
    }

    public static function addSubscription(array $overrides = []): Subscription
    {
        $subscriberArray = [
            'uuid' => $overrides['uuid'] ?? self::DEFAULT_SUBSCRIBER_UUID,
            'user_uuid' => $overrides['user_uuid'] ?? self::DEFAULT_USER_UUID,
            'subscription_topic_uuid' => $overrides['subscription_topic_uuid'] ?? self::DEFAULT_SUBSCRIPTION_TOPIC_UUID,
            'is_active' => $overrides['is_active'] ?? self::DEFAULT_SUBSCRIBER_IS_ACTIVE,
            'created_at' => $overrides['created_at'] ?? self::DEFAULT_CREATED_AT,
            'updated_at' => $overrides['updated_at'] ?? self::DEFAULT_UPDATED_AT
        ];
        self::$subscriptionsTable->insert([
            $subscriberArray
        ])->save();
        return new Subscription(
            $subscriberArray['uuid'],
            $subscriberArray['user_uuid'],
            $subscriberArray['subscription_topic_uuid'],
            $subscriberArray['is_active'],
            $subscriberArray['created_at'],
            $subscriberArray['updated_at']
        );
    }
}
