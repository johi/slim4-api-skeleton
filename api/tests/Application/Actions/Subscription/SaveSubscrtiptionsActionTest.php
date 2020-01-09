<?php
declare(strict_types=1);

namespace Tests\Application\Actions\Subscription;

use App\Application\Actions\Action;
use App\Application\Actions\ActionPayload;
use App\Infrastructure\Persistence\Subscription\SubscriptionRepository;
use App\Infrastructure\Persistence\User\UserRepository;

class SaveSubscriptionsActionTest extends SubscriptionActionTestCase
{
    public function testSaveSubscriptionsAction()
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
        $subscriptionTopicsPayload = [
            [
                'uuid' => $subscriptionTopic->getUuid(),
                'active' => true
            ]
        ];
        $subscriptionRepositoryProphecy = $this->prophesize(SubscriptionRepository::class);
        $subscriptionRepositoryProphecy
            ->bulkSaveSubscriptions($user, $subscriptionTopicsPayload)
            ->willReturn([$subscription])
            ->shouldBeCalledOnce();
        $this->container->set(SubscriptionRepository::class, $subscriptionRepositoryProphecy->reveal());

        //EXECUTION
        $responseCode = 0;
        $payload = $this->makeRequest('POST', '/subscriptions', [
            'userUuid' => $user->getUuid(),
            'subscriptionTopics' => $subscriptionTopicsPayload
        ], $responseCode);
        $expectedPayload = new ActionPayload(Action::HTTP_OK, [$subscription]);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);
        $this->assertEquals($serializedPayload, $payload);
        $this->assertEquals(Action::HTTP_OK, $responseCode);
    }
}