<?php
declare(strict_types=1);

namespace Tests\Domain\Subscription;

use App\Domain\Subscription\Subscriber;
use Tests\TestCase;

class SubscriberTest extends TestCase
{
    public function subscriberProvider()
    {
        return [
            ['00000000-0000-0000-0000-000000000000', '10000000-0000-0000-0000-000000000000', '20000000-0000-0000-0000-000000000000', true, true, '2019-10-07 10:00:00', null],
            ['00000000-0000-0000-0000-000000000001', '10000000-0000-0000-0000-000000000001', '20000000-0000-0000-0000-000000000001', true, true, '2019-10-07 10:00:00', null],
            ['00000000-0000-0000-0000-000000000002', '10000000-0000-0000-0000-000000000002', '20000000-0000-0000-0000-000000000002', true, true, '2019-10-07 10:00:00', null],
        ];
    }

    /**
     * @dataProvider subscriberProvider
     * @param $uuid
     * @param $userUuid
     * @param $subscriptionUuid
     * @param $isConfirmed
     * @param $isActive
     * @param $created
     * @param $updated
     */
    public function testGetters($uuid, $userUuid, $subscriptionUuid, $isConfirmed, $isActive, $created, $updated)
    {
        $user = new Subscriber($uuid, $userUuid, $subscriptionUuid, $isConfirmed, $isActive, $created, $updated);
        $this->assertEquals($uuid, $user->getUuid());
        $this->assertEquals($userUuid, $user->getUserUuid());
        $this->assertEquals($subscriptionUuid, $user->getSubscriptionUuid());
        $this->assertEquals($isConfirmed, $user->isConfirmed());
        $this->assertEquals($isActive, $user->isActive());
    }

    /**
     * @dataProvider subscriberProvider
     * @param $uuid
     * @param $userUuid
     * @param $subscriptionUuid
     * @param $isConfirmed
     * @param $isActive
     * @param $created
     * @param $updated
     */
    public function testJsonSerialize($uuid, $userUuid, $subscriptionUuid, $isConfirmed, $isActive, $created, $updated)
    {
        $subscriber = new Subscriber($uuid, $userUuid, $subscriptionUuid, $isConfirmed, $isActive, $created, $updated);

        $expectedPayload = json_encode([
            'uuid' => $uuid,
            'userUuid' => $userUuid,
            'subscriptionUuid' => $subscriptionUuid,
            'isConfirmed' => $isConfirmed,
            'isActive' => $isActive,
            'created' => $created,
            'updated' => $updated
        ]);

        $this->assertEquals($expectedPayload, json_encode($subscriber));
    }
}
