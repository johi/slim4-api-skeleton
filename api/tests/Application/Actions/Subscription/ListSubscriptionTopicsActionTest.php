<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Subscription;

use App\Application\Actions\Action;
use App\Application\Actions\ActionPayload;
use App\Infrastructure\Persistence\Subscription\SubscriptionRepository;
use Tests\ActionTestCase;

class ListSubscriptionTopicsActionTest extends ActionTestCase
{
    public function testListSubscriptionTopicsTest()
    {
        $subscriptionRepositoryProphecy = $this->prophesize(SubscriptionRepository::class);
        $subscriptionRepositoryProphecy
            ->findAllSubscriptionTopics()
            ->willReturn([])
            ->shouldBeCalledOnce();
        $this->container->set(SubscriptionRepository::class, $subscriptionRepositoryProphecy->reveal());

        //EXECUTION
        $payload = $this->makeRequest('GET', '/subscriptions/topics');
        $expectedPayload = new ActionPayload(Action::HTTP_OK, []);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);
        $this->assertEquals($serializedPayload, $payload);
    }
}