<?php
declare(strict_types=1);

namespace Tests\Domain\Subscription;

use App\Domain\Subscription\Subscription;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    public function subscriptionProvider()
    {
        return [
            ['00000000-0000-0000-0000-000000000000', '10000000-0000-0000-0000-000000000000', '20000000-0000-0000-0000-000000000000', true, '2019-10-07 10:00:00', null],
            ['00000000-0000-0000-0000-000000000001', '10000000-0000-0000-0000-000000000001', '20000000-0000-0000-0000-000000000001', true, '2019-10-07 10:00:00', null],
            ['00000000-0000-0000-0000-000000000002', '10000000-0000-0000-0000-000000000002', '20000000-0000-0000-0000-000000000002', true, '2019-10-07 10:00:00', null],
        ];
    }

    /**
     * @dataProvider subscriptionProvider
     * @param $uuid
     * @param $userUuid
     * @param $subscriptionTopicUuid
     * @param $isActive
     * @param $created
     * @param $updated
     */
    public function testGetters($uuid, $userUuid, $subscriptionTopicUuid, $isActive, $created, $updated)
    {
        $subscription = new Subscription($uuid, $userUuid, $subscriptionTopicUuid, $isActive, $created, $updated);
        $this->assertEquals($uuid, $subscription->getUuid());
        $this->assertEquals($userUuid, $subscription->getUserUuid());
        $this->assertEquals($subscriptionTopicUuid, $subscription->getSubscriptionTopicUuid());
        $this->assertEquals($isActive, $subscription->isActive());
    }

    /**
     * @dataProvider subscriptionProvider
     * @param $uuid
     * @param $userUuid
     * @param $subscriptionTopicUuid
     * @param $isActive
     * @param $created
     * @param $updated
     */
    public function testJsonSerialize($uuid, $userUuid, $subscriptionTopicUuid, $isActive, $created, $updated)
    {
        $subscription = new Subscription($uuid, $userUuid, $subscriptionTopicUuid, $isActive, $created, $updated);

        $expectedPayload = json_encode([
            'uuid' => $uuid,
            'userUuid' => $userUuid,
            'subscriptionTopicUuid' => $subscriptionTopicUuid,
            'isActive' => $isActive,
            'created' => $created,
            'updated' => $updated
        ]);

        $this->assertEquals($expectedPayload, json_encode($subscription));
    }
}
