<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\ActionPayload;
use App\Infrastructure\Persistence\User\UserRepository;
use Tests\ActionTestCase;

class ConfirmUserActivationActionTest extends ActionTestCase
{

    public function testAction()
    {
        $user = $this->getUser();
        $userActivation = $this->getUserActivation();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserActivationOfToken($userActivation->getToken())
            ->willReturn($userActivation)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->userActivationIsValid($userActivation)
            ->willReturn(true)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->activateUser($user)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        $payload = $this->makeRequest('GET', '/users/activate/' . $userActivation->getToken());
        $serializedPayload = json_encode(new ActionPayload(200, $user), JSON_PRETTY_PRINT);
        $this->assertEquals($serializedPayload, $payload);
    }

    //findUserActivationOfToken not found throws some exception
    //user activation invalid throws some exception
    //user not found throws some exception
    //activate user throws some exception
}
