<?php
declare(strict_types=1);

// namespace Tests\Database\Seeds\User;

use App\Domain\Subscription\Subscription;
use App\Domain\Subscription\Subscriber;
use Phinx\Seed\AbstractSeed;

class BaseSubscriptionSeeder extends AbstractSeed
{
    const DEFAULT_SUBSCRIPTION_UUID = '00000000-0000-0000-0000-000000000000';
    const DEFAULT_SUBSCRIPTION_NAME = 'Weekly Newsletter';
    const DEFAULT_SUBSCRIPTION_DESCRIPTION = 'The most important take aways from the ongoing week';
    const DEFAULT_CREATED_AT = '2019-10-05 08:00:00+00';
    const DEFAULT_UPDATED_AT = '2019-10-05 08:00:00+00';
    const DEFAULT_SUBSCRIBER_UUID = '10000000-0000-0000-0000-000000000000';
    const DEFAULT_USER_UUID = '10000000-0000-0000-0000-000000000000';
    const DEFAULT_SUBSCRIBER_IS_CONFIRMED = true;
    const DEFAULT_SUBSCRIBER_IS_ACTIVE = true;

    private static $subscriptionsTable;
    private static $subscribersTable;

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
        self::$subscriptionsTable = $this->table('subscriptions');
        self::$subscribersTable = $this->table('subscribers');
    }

    public static function addSubscription(array $overrides = []): Subscription
    {
        $subscriptionArray = [
            'uuid' => $overrides['uuid'] ?? self::DEFAULT_SUBSCRIPTION_UUID,
            'name' => $overrides['name'] ?? self::DEFAULT_SUBSCRIPTION_NAME,
            'description' => $overrides['email'] ??  self::DEFAULT_SUBSCRIPTION_DESCRIPTION,
            'created_at' => $overrides['created_at'] ?? self::DEFAULT_CREATED_AT,
            'updated_at' => $overrides['updated_at'] ?? self::DEFAULT_UPDATED_AT
        ];
        self::$subscriptionsTable->insert([
            $subscriptionArray
        ])->save();
        return new Subscription(
            $subscriptionArray['uuid'],
            $subscriptionArray['name'],
            $subscriptionArray['description'],
            $subscriptionArray['created_at'],
            $subscriptionArray['updated_at']
        );
    }

    public static function addSubscriber(array $overrides = []): Subscriber
    {
        $subscriberArray = [
            'uuid' => $overrides['uuid'] ?? self::DEFAULT_SUBSCRIBER_UUID,
            'user_uuid' => $overrides['user_uuid'] ?? self::DEFAULT_USER_UUID,
            'subscription_uuid' => $overrides['subscription_uuid'] ?? self::DEFAULT_SUBSCRIPTION_UUID,
            'is_confirmed' => $overrides['is_confirmed'] ?? self::DEFAULT_SUBSCRIBER_IS_CONFIRMED,
            'is_active' => $overrides['is_active'] ?? self::DEFAULT_SUBSCRIBER_IS_ACTIVE,
            'created_at' => $overrides['created_at'] ?? self::DEFAULT_CREATED_AT,
            'updated_at' => $overrides['updated_at'] ?? self::DEFAULT_UPDATED_AT
        ];
        self::$subscribersTable->insert([
            $subscriberArray
        ])->save();
        return new Subscriber(
            $subscriberArray['uuid'],
            $subscriberArray['user_uuid'],
            $subscriberArray['subscription_uuid'],
            $subscriberArray['is_confirmed'],
            $subscriberArray['is_active'],
            $subscriberArray['created_at'],
            $subscriberArray['updated_at']
        );
    }
}
