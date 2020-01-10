<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Subscription;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Infrastructure\Persistence\Subscription\SubscriptionRepository;
use App\Infrastructure\Persistence\User\UserRepository;

class ViewSubscriptionsActionTest extends SubscriptionActionTestCase
{
    public function testSubscriptionsAction()
    {
        $user = $this->getUser();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $subscriptionTopic = $this->getSubscriptionTopic();
        $subscription = $this->getSubscription();
        $subscriptionRepositoryProphecy = $this->prophesize(SubscriptionRepository::class);
        $subscriptionRepositoryProphecy
            ->findSubscriptionsOfUser($user)
            ->willReturn([$subscription])
            ->shouldBeCalledOnce();
        $this->container->set(SubscriptionRepository::class, $subscriptionRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('GET', '/subscriptions/' . $user->getUuid(), [], $responseCode);
        $expectedPayload = new ActionPayload(Action::HTTP_OK, [$subscription]);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);
        $this->assertEquals($serializedPayload, $payload);
        $this->assertEquals(Action::HTTP_OK, $responseCode);
    }

    public function testSubscriptionsActionUserNotFound()
    {
        $user = $this->getUser();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn(null)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        $responseCode = 0;
        $payload = $this->makeRequest('GET', '/subscriptions/' . $user->getUuid(), [], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::RESOURCE_NOT_FOUND, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_NOT_FOUND, $responseCode);
    }
}