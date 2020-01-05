<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Subscription;

use BaseSubscriptionSeeder;
use App\Infrastructure\Persistence\Subscription\PdoSubscriptionRepository;
use Tests\DatabaseTestCase;

class PdoUserRepositoryTest extends DatabaseTestCase
{
    const NON_EXISTING_SUBSCRIPTION_UUID = '00000000-0000-0000-0000-111111111111';

    protected static $pdoSubscriptionRepository;

    protected static $seedSubPath = '/Subscription';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$pdoSubscriptionRepository = new PdoSubscriptionRepository(self::$databaseService);
    }

    /**
     * setUp migrates test db migrations for each test, it would be better doing it in the superclass DatabaseTestCase
     * setUpBeforeClass method, but since phinx does not support truncating tables with foreign key constraints,
     * it is done here
     */
    public function setUp()
    {
        self::$manager->migrate('test');
    }

    /**
     * tearDown rolls back migrations to origin for test db for each test,
     * it would be better doing it in the superclass DatabaseTestCase
     * tearDownAfterClass method, but since phinx does not support truncating tables with foreign key constraints,
     * it is done here
     */
    public function tearDown()
    {
        self::$manager->rollback('test', 0);
    }

    public function testFindAllSubscriptionTopics()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscriptionTopics = self::$pdoSubscriptionRepository->findAllSubscriptionTopics();
        $this->assertCount(
            3, //schema migrated with 3 preset subscriptions
            $subscriptionTopics
        );
        foreach ($subscriptionTopics as $subscriptionTopic) {
            $this->assertInstanceOf('\App\Domain\Subscription\SubscriptionTopic', $subscriptionTopic);
        }
    }

    public function testFindSubscriptionOfUuid()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscriptionTopic = BaseSubscriptionSeeder::addSubscriptionTopic(); //only necessary when using foreign keys
        $subscription = BaseSubscriptionSeeder::addSubscription();
        $this->assertEquals(
            $subscription,
            self::$pdoSubscriptionRepository->findSubscriptionOfUuid($subscription->getUuid()));
        $this->assertNull(self::$pdoSubscriptionRepository->findSubscriptionOfUuid(self::NON_EXISTING_SUBSCRIPTION_UUID));
    }

    public function testFindSubscriptionOfUserUuidAndSubscriptionTopicUuid()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscriptionTopic = BaseSubscriptionSeeder::addSubscriptionTopic(); //only necessary when using foreign keys
        $subscription = BaseSubscriptionSeeder::addSubscription();
        $this->assertEquals(
            $subscription,
            self::$pdoSubscriptionRepository->findSubscriptionOfUserUuidAndSubscriptionTopicUuid($subscription->getUserUuid(), $subscription->getSubscriptionTopicUuid()));
    }

    public function testCreateSubscription()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscriptionTopic = BaseSubscriptionSeeder::addSubscriptionTopic();
        $subscription = self::$pdoSubscriptionRepository->createSubscription($subscriptionTopic, self::NON_EXISTING_SUBSCRIPTION_UUID, false);
        $this->assertEquals(self::NON_EXISTING_SUBSCRIPTION_UUID, $subscription->getUserUuid());
        $this->assertFalse($subscription->isActive());
    }

    /**
     * @expectedException \App\Domain\Exception\DomainRecordDuplicateException
     */
    public function testCreateSubscriptionThrowsDomainRecordDuplicateException()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscriptionTopic = BaseSubscriptionSeeder::addSubscriptionTopic();
        $subscription = self::$pdoSubscriptionRepository->createSubscription($subscriptionTopic, self::NON_EXISTING_SUBSCRIPTION_UUID, false);
        $subscription = self::$pdoSubscriptionRepository->createSubscription($subscriptionTopic, self::NON_EXISTING_SUBSCRIPTION_UUID, false);
    }

    public function testUpdateSubscription()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscriptionTopic = BaseSubscriptionSeeder::addSubscriptionTopic();
        $subscription = BaseSubscriptionSeeder::addSubscription(['is_confirmed' => false, 'is_active' => false]);
        $this->assertFalse($subscription->isActive());
        $subscription = self::$pdoSubscriptionRepository->updateSubscription($subscriptionTopic, $subscription->getUserUuid(), true);
        $this->assertTrue($subscription->isActive());
    }

    /**
     * @expectedException \App\Domain\Exception\DomainRecordNotFoundException
     */
    public function testUpdateSubscriberThrowsDomainRecordNotFoundException()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscriptionTopic = BaseSubscriptionSeeder::addSubscriptionTopic();
        $subscription = self::$pdoSubscriptionRepository->updateSubscription($subscriptionTopic, self::NON_EXISTING_SUBSCRIPTION_UUID, true);
    }
}
