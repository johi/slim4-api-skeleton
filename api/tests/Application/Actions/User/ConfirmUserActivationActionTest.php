<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Infrastructure\Persistence\User\UserRepository;
use Tests\ActionTestCase;

class ConfirmUserActivationActionTest extends ActionTestCase
{

    public function testConfirmUserActivationAction()
    {
        $user = $this->getUser();
        $userActivation = $this->getUserActivation();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserActivationOfToken($userActivation->getToken())
            ->willReturn($userActivation)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->userActivationIsValid($userActivation)
            ->willReturn(true)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->activateUser($user)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        $payload = $this->makeRequest('GET', '/users/activate/' . $userActivation->getToken());
        $serializedPayload = json_encode(new ActionPayload(Action::HTTP_OK, $user), JSON_PRETTY_PRINT);
        $this->assertEquals($serializedPayload, $payload);
    }

    public function testConfirmUserActivationActionTokenNotFound()
    {
        $user = $this->getUser();
        $userActivation = $this->getUserActivation();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserActivationOfToken($userActivation->getToken())
            ->willReturn(null)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('GET', '/users/activate/' . $userActivation->getToken(), [], $responseCode);
        $decodePayload = json_decode($payload, true);
        $this->assertTrue($decodePayload['error']);
        $this->assertEquals(ActionError::RESOURCE_NOT_FOUND, $decodePayload['type']);
        $this->assertEquals(Action::HTTP_NOT_FOUND, $responseCode);
    }

    public function testConfirmUserActivationActionUserNotFound()
    {
        $user = $this->getUser();
        $userActivation = $this->getUserActivation();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserActivationOfToken($userActivation->getToken())
            ->willReturn($userActivation)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn(null)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('GET', '/users/activate/' . $userActivation->getToken(), [], $responseCode);
        $decodePayload = json_decode($payload, true);
        $this->assertTrue($decodePayload['error']);
        $this->assertEquals(ActionError::RESOURCE_NOT_FOUND, $decodePayload['type']);
        $this->assertEquals(Action::HTTP_NOT_FOUND, $responseCode);
    }

    public function testConfirmUserActivationActionInvalid()
    {
        $user = $this->getUser();
        $userActivation = $this->getUserActivation();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserActivationOfToken($userActivation->getToken())
            ->willReturn($userActivation)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->userActivationIsValid($userActivation)
            ->willReturn(false)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('GET', '/users/activate/' . $userActivation->getToken(), [], $responseCode);
        $decodePayload = json_decode($payload, true);
        $this->assertTrue($decodePayload['error']);
        $this->assertEquals(ActionError::NOT_ALLOWED, $decodePayload['type']);
        $this->assertEquals(Action::HTTP_NOT_ALLOWED, $responseCode);
    }

    public function testConfirmUserActivationActionAlreadyVerified()
    {
        $user = $this->getUser(ActionTestCase::UPDATED_TIMESTAMP);
        $userActivation = $this->getUserActivation();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserActivationOfToken($userActivation->getToken())
            ->willReturn($userActivation)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('GET', '/users/activate/' . $userActivation->getToken(), [], $responseCode);
        $decodePayload = json_decode($payload, true);
        $this->assertTrue($decodePayload['error']);
        $this->assertEquals(ActionError::NOT_ACCEPTABLE, $decodePayload['type']);
        $this->assertEquals(Action::HTTP_NOT_ACCEPTABLE, $responseCode);
    }
}
