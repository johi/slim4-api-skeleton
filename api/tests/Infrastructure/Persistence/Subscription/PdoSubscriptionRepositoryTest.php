<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Subscription;

use UserSeeder;
use SubscriptionSeeder;
use App\Infrastructure\Persistence\Subscription\PdoSubscriptionRepository;
use Tests\DatabaseTestCase;

class PdoUserRepositoryTest extends DatabaseTestCase
{
    const NON_EXISTING_SUBSCRIPTION_UUID = '00000000-0000-0000-0000-111111111111';

    protected static $pdoSubscriptionRepository;

    protected static $seeds = ['User', 'Subscription'];

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
        self::$manager->seed('test', 'SubscriptionSeeder');
        $subscriptionTopics = self::$pdoSubscriptionRepository->findAllSubscriptionTopics();
        $this->assertCount(
            3, //schema migrated with 3 preset subscriptions
            $subscriptionTopics
        );
        foreach ($subscriptionTopics as $subscriptionTopic) {
            $this->assertInstanceOf('\App\Domain\Subscription\SubscriptionTopic', $subscriptionTopic);
        }
    }

    public function testFindSubscriptionTopicOfUuid()
    {
        self::$manager->seed('test', 'SubscriptionSeeder');
        $subscriptionTopic = SubscriptionSeeder::addSubscriptionTopic(); //only necessary when using foreign keys
        $this->assertEquals(
            $subscriptionTopic,
            self::$pdoSubscriptionRepository->findSubscriptionTopicOfUuid($subscriptionTopic->getUuid()));
        $this->assertNull(self::$pdoSubscriptionRepository->findSubscriptionTopicOfUuid(self::NON_EXISTING_SUBSCRIPTION_UUID));
    }

    public function testFindSubscriptionOfUuid()
    {
        self::$manager->seed('test', 'SubscriptionSeeder');
        $subscriptionTopic = SubscriptionSeeder::addSubscriptionTopic(); //only necessary when using foreign keys
        $subscription = SubscriptionSeeder::addSubscription();
        $this->assertEquals(
            $subscription,
            self::$pdoSubscriptionRepository->findSubscriptionOfUuid($subscription->getUuid()));
        $this->assertNull(self::$pdoSubscriptionRepository->findSubscriptionOfUuid(self::NON_EXISTING_SUBSCRIPTION_UUID));
    }

    public function testFindSubscriptionOfSubscriptionTopicAndUser()
    {
        self::$manager->seed('test', 'UserSeeder');
        self::$manager->seed('test', 'SubscriptionSeeder');
        $user = UserSeeder::addUser();
        $subscriptionTopic = SubscriptionSeeder::addSubscriptionTopic(); //only necessary when using foreign keys
        $subscription = SubscriptionSeeder::addSubscription(['user_uuid' => $user->getUuid()]);
        $this->assertEquals(
            $subscription,
            self::$pdoSubscriptionRepository->findSubscriptionOfSubscriptionTopicAndUser($subscriptionTopic, $user));
    }

    public function testFindSubscriptionsOfUser()
    {
        self::$manager->seed('test', 'SubscriptionSeeder');
        self::$manager->seed('test', 'UserSeeder');
        $user = UserSeeder::addUser();
        $subscriptionTopic = SubscriptionSeeder::addSubscriptionTopic();
        $subscriptions = self::$pdoSubscriptionRepository->findSubscriptionsOfUser($user);
        $this->assertEquals([], $subscriptions);
        $subscription = SubscriptionSeeder::addSubscription([
           'user_uuid' => $user->getUuid()
        ]);
        $subscriptions = self::$pdoSubscriptionRepository->findSubscriptionsOfUser($user);
        $this->assertEquals([$subscription], $subscriptions);
    }

    public function testCreateSubscription()
    {
        self::$manager->seed('test', 'SubscriptionSeeder');
        self::$manager->seed('test', 'UserSeeder');
        $user = UserSeeder::addUser();
        $subscriptionTopic = SubscriptionSeeder::addSubscriptionTopic();
        $subscription = self::$pdoSubscriptionRepository->createSubscription($subscriptionTopic, $user, false);
        $this->assertEquals($user->getUuid(), $subscription->getUserUuid());
        $this->assertFalse($subscription->isActive());
    }

    /**
     * @expectedException \App\Domain\Exception\DomainRecordDuplicateException
     */
    public function testCreateSubscriptionThrowsDomainRecordDuplicateException()
    {
        self::$manager->seed('test', 'SubscriptionSeeder');
        self::$manager->seed('test', 'UserSeeder');
        $user = UserSeeder::addUser();
        $subscriptionTopic = SubscriptionSeeder::addSubscriptionTopic();
        $subscription = self::$pdoSubscriptionRepository->createSubscription($subscriptionTopic, $user, false);
        $subscription = self::$pdoSubscriptionRepository->createSubscription($subscriptionTopic, $user, false);
    }

    public function testUpdateSubscription()
    {
        self::$manager->seed('test', 'SubscriptionSeeder');
        self::$manager->seed('test', 'UserSeeder');
        $user = UserSeeder::addUser();
        $subscriptionTopic = SubscriptionSeeder::addSubscriptionTopic();
        $subscription = SubscriptionSeeder::addSubscription(['user_uuid' => $user->getUuid(), 'is_confirmed' => false, 'is_active' => false]);
        $this->assertFalse($subscription->isActive());
        $subscription = self::$pdoSubscriptionRepository->updateSubscription($subscriptionTopic, $user, true);
        $this->assertTrue($subscription->isActive());
    }

    /**
     * @expectedException \App\Domain\Exception\DomainRecordNotFoundException
     */
    public function testUpdateSubscriberThrowsDomainRecordNotFoundException()
    {
        self::$manager->seed('test', 'SubscriptionSeeder');
        self::$manager->seed('test', 'UserSeeder');
        $user = UserSeeder::addUser();
        $subscriptionTopic = SubscriptionSeeder::addSubscriptionTopic();
        $subscription = self::$pdoSubscriptionRepository->updateSubscription($subscriptionTopic, $user, true);
    }

    public function testBulkSaveSubscriptions()
    {
        self::$manager->seed('test', 'SubscriptionSeeder');
        self::$manager->seed('test', 'UserSeeder');
        $user = UserSeeder::addUser();
        $subscriptionTopic = SubscriptionSeeder::addSubscriptionTopic();
        $subscription = self::$pdoSubscriptionRepository->bulkSaveSubscriptions($user, [
            [
                'uuid' => $subscriptionTopic->getUuid(),
                'active' => false
            ]
        ])[0];
        $this->assertEquals($subscriptionTopic->getUuid(), $subscription->getSubscriptionTopicUuid());
        $this->assertFalse($subscription->isActive());
        $subscriptionUuid = $subscription->getUuid();
        $subscription = self::$pdoSubscriptionRepository->bulkSaveSubscriptions($user, [
            [
                'uuid' => $subscriptionTopic->getUuid(),
                'active' => true
            ]
        ])[0];
        $this->assertEquals($subscriptionUuid, $subscription->getUuid());
        $this->assertTrue($subscription->isActive());
    }

    /**
     * @expectedException \App\Domain\Exception\DomainRecordNotFoundException
     */
    public function testBulkSaveSubscriptionsThrowsDomainRecordNotFoundException()
    {
        self::$manager->seed('test', 'SubscriptionSeeder');
        self::$manager->seed('test', 'UserSeeder');
        $user = UserSeeder::addUser();
        self::$pdoSubscriptionRepository->bulkSaveSubscriptions($user, [
            [
                'uuid' => self::NON_EXISTING_SUBSCRIPTION_UUID,
                'active' => false
            ]
        ]);
    }

}
