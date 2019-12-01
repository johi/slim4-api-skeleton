<?php

namespace Tests\Application\Actions\User;

use App\Application\Actions\ActionPayload;
use App\Infrastructure\Persistence\User\UserRepository;
use Tests\ActionTestCase;

class LogoutActionTest extends ActionTestCase
{

    public function testAction()
    {
        $user = $this->getUser();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn($user)
            ->shouldBeCalledOnce();

        $userRepositoryProphecy
            ->invalidateUserLogins($user)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        $payload = $this->makeRequest('GET', '/users/logout');
        $serializedPayload = json_encode(
            new ActionPayload(200, ['success' => 'ok']),
            JSON_PRETTY_PRINT
        );
        $this->assertEquals($serializedPayload, $payload);
    }
    //findUserOfUuid throws some exception
}