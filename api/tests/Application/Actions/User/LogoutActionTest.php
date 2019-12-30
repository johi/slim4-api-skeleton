<?php

namespace Tests\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Infrastructure\Persistence\User\UserRepository;

class LogoutActionTest extends UserActionTestCase
{

    public function testLogoutAction()
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
        $responseCode = 0;
        $payload = $this->makeRequest('GET', '/users/logout', [], $responseCode);
        $serializedPayload = json_encode(
            new ActionPayload(Action::HTTP_OK, ['success' => 'ok']),
            JSON_PRETTY_PRINT
        );
        $this->assertEquals($serializedPayload, $payload);
        $this->assertEquals(Action::HTTP_OK, $responseCode);
    }

    public function testLogoutActionUserNotFound()
    {
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn(null)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('GET', '/users/logout', [], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::RESOURCE_NOT_FOUND, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_NOT_FOUND, $responseCode);
    }
}