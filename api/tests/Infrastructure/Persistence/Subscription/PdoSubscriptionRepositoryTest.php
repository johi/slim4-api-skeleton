<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Subscription;

use BaseSubscriptionSeeder;
use App\Infrastructure\Persistence\Subscription\PdoSubscriptionRepository;
use Tests\DatabaseTestCase;

class PdoUserRepositoryTest extends DatabaseTestCase
{
    const NON_EXISTING_SUBSCRIBER_UUID = '00000000-0000-0000-0000-111111111111';

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

    public function testFindAllSubscriptions()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscriptions = self::$pdoSubscriptionRepository->findAllSubscriptions();
        $this->assertCount(
            3, //schema migrated with 3 preset subscriptions
            $subscriptions
        );
        foreach ($subscriptions as $subscription) {
            $this->assertInstanceOf('\App\Domain\Subscription\Subscription', $subscription);
        }
    }

    public function testFindSubscriberOfUuid()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscription = BaseSubscriptionSeeder::addSubscription(); //only necessary when using foreign keys
        $subscriber = BaseSubscriptionSeeder::addSubscriber();
        $this->assertEquals(
            $subscriber,
            self::$pdoSubscriptionRepository->findSubscriberOfUuid($subscriber->getUuid()));
        $this->assertNull(self::$pdoSubscriptionRepository->findSubscriberOfUuid(self::NON_EXISTING_SUBSCRIBER_UUID));
    }

    public function testFindSubscriberOfUserUuidAndSubscriptionUuid()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscription = BaseSubscriptionSeeder::addSubscription(); //only necessary when using foreign keys
        $subscriber = BaseSubscriptionSeeder::addSubscriber();
        $this->assertEquals(
            $subscriber,
            self::$pdoSubscriptionRepository->findSubscriberOfUserUuidAndSubscriptionUuid($subscriber->getUserUuid(), $subscriber->getSubscriptionUuid()));
    }

    public function testCreateSubscriber()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscription = BaseSubscriptionSeeder::addSubscription();
        $subscriber = self::$pdoSubscriptionRepository->createSubscriber($subscription, self::NON_EXISTING_SUBSCRIBER_UUID, false, false);
        $this->assertEquals(self::NON_EXISTING_SUBSCRIBER_UUID, $subscriber->getUserUuid());
        $this->assertFalse($subscriber->isConfirmed());
        $this->assertFalse($subscriber->isActive());
    }

    /**
     * @expectedException \App\Domain\Exception\DomainRecordDuplicateException
     */
    public function testCreateSubscriberThrowsDomainRecordDuplicateException()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscription = BaseSubscriptionSeeder::addSubscription();
        $subscriber = self::$pdoSubscriptionRepository->createSubscriber($subscription, self::NON_EXISTING_SUBSCRIBER_UUID, false, false);
        $subscriber = self::$pdoSubscriptionRepository->createSubscriber($subscription, self::NON_EXISTING_SUBSCRIBER_UUID, false, false);
    }

    public function testUpdateSubscriber()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscription = BaseSubscriptionSeeder::addSubscription();
        $subscriber = self::$pdoSubscriptionRepository->createSubscriber($subscription, self::NON_EXISTING_SUBSCRIBER_UUID, false, false);
        $subscriber = self::$pdoSubscriptionRepository->updateSubscriber($subscription, self::NON_EXISTING_SUBSCRIBER_UUID, true, true);
        $this->assertEquals(self::NON_EXISTING_SUBSCRIBER_UUID, $subscriber->getUserUuid());
        $this->assertTrue($subscriber->isConfirmed());
        $this->assertTrue($subscriber->isActive());
    }

    /**
     * @expectedException \App\Domain\Exception\DomainRecordNotFoundException
     */
    public function testUpdateSubscriberThrowsDomainRecordNotFoundException()
    {
        self::$manager->seed('test', 'BaseSubscriptionSeeder');
        $subscription = BaseSubscriptionSeeder::addSubscription();
        $subscriber = self::$pdoSubscriptionRepository->updateSubscriber($subscription, self::NON_EXISTING_SUBSCRIBER_UUID, true, true);
    }
}
