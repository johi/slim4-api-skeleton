<?php
declare(strict_types=1);

namespace Tests\Domain\Subscription;

use App\Domain\Subscription\SubscriptionTopic;
use Tests\TestCase;

class SubscriptionTopicTest extends TestCase
{
    public function subscriptionTopicProvider()
    {
        return [
            ['00000000-0000-0000-0000-000000000000', 'Weekly Newsletter', 'Biggest take aways of the week', '2019-10-07 10:00:00', null],
            ['00000000-0000-0000-0000-000000000001', 'Monthly Newsletter', 'Biggest take aways of the month', '2019-10-07 10:00:00', null],
            ['00000000-0000-0000-0000-000000000002', 'Quarterly Newsletter', 'Biggest take aways of the quarter', '2019-10-07 10:00:00', null],
        ];
    }

    /**
     * @dataProvider subscriptionTopicProvider
     * @param $uuid
     * @param $name
     * @param $description
     * @param $created
     * @param $updated
     */
    public function testGetters($uuid, $name, $description, $created, $updated)
    {
        $subscription = new SubscriptionTopic($uuid, $name, $description, $created, $updated);
        $this->assertEquals($uuid, $subscription->getUuid());
        $this->assertEquals($name, $subscription->getName());
        $this->assertEquals($description, $subscription->getDescription());
    }

    /**
     * @dataProvider subscriptionTopicProvider
     * @param $uuid
     * @param $name
     * @param $description
     * @param $created
     * @param $updated
     */
    public function testJsonSerialize($uuid, $name, $description, $created, $updated)
    {
        $subscription = new SubscriptionTopic($uuid, $name, $description, $created, $updated);
        $expectedPayload = json_encode([
            'uuid' => $uuid,
            'name' => $name,
            'description' => $description,
            'created' => $created,
            'updated' => $updated
        ]);
        $this->assertEquals($expectedPayload, json_encode($subscription));
    }
}
