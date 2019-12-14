<?php

namespace Tests\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Infrastructure\Persistence\User\UserRepository;
use Tests\ActionTestCase;

class LoginActionTest extends ActionTestCase
{
    public function testLoginAction()
    {
        $user = $this->getUser(ActionTestCase::UPDATED_TIMESTAMP);
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->login($user, self::USER_PASSWORD)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        $payload = $this->makeRequest('POST', '/users/login', [
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD
        ]);
        $payloadDecoded = json_decode($payload, true);
        $this->assertIsArray($payloadDecoded);
        $this->assertIsString($payloadDecoded['jwt']);
    }

    public function testLoginActionUserNotFound()
    {
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn(null)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('POST', '/users/login', [
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD
        ], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::RESOURCE_NOT_FOUND, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_NOT_FOUND, $responseCode);
    }

    public function testLoginActionUserNotVerified()
    {
        $user = $this->getUser();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('POST', '/users/login', [
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD
        ], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::NOT_ACCEPTABLE, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_NOT_ACCEPTABLE, $responseCode);
    }

    //Wrong password

}