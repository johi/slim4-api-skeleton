<?php

namespace Tests\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Infrastructure\Persistence\User\UserRepository;
use App\Infrastructure\Token\TokenService;
use Tests\ActionTestCase;

class PasswordResetActionTest extends ActionTestCase
{
    const NEW_PASSWORD = 'ILIKETOMOVEIT';

    public function testPasswordResetAction()
    {
        $user = $this->getUser();
        $passwordReset = $this->getPasswordReset();
        $token = $this->container->get(TokenService::class)->generateToken();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findPasswordResetOfToken($token)
            ->willReturn($passwordReset)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->passwordResetIsValid($passwordReset)
            ->willReturn(true)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->updatePassword($user, self::NEW_PASSWORD)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        $payload = $this->makeRequest('PUT', '/users/password', [
            'uuid' => self::UUID_UNDER_TEST,
            'token' => $token,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD
        ]);
        $payloadDecoded = json_decode($payload, true);
        $this->assertEquals(self::UUID_UNDER_TEST, $payloadDecoded['uuid']);
    }

    public function testPasswordResetActionPasswordResetNotFound()
    {
        $user = $this->getUser();
        $passwordReset = $this->getPasswordReset();
        $token = $this->container->get(TokenService::class)->generateToken();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findPasswordResetOfToken($token)
            ->willReturn(null)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('PUT', '/users/password', [
            'uuid' => self::UUID_UNDER_TEST,
            'token' => $token,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD
        ], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::RESOURCE_NOT_FOUND, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_NOT_FOUND, $responseCode);
    }

    //user not found
    public function testPasswordResetActionUserNotFound()
    {
        $user = $this->getUser();
        $passwordReset = $this->getPasswordReset();
        $token = $this->container->get(TokenService::class)->generateToken();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findPasswordResetOfToken($token)
            ->willReturn($passwordReset)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn(null)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('PUT', '/users/password', [
            'uuid' => self::UUID_UNDER_TEST,
            'token' => $token,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD
        ], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::RESOURCE_NOT_FOUND, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_NOT_FOUND, $responseCode);
    }

    //password reset not valid
    public function testPasswordResetActionInvalid()
    {
        $user = $this->getUser();
        $passwordReset = $this->getPasswordReset();
        $token = $this->container->get(TokenService::class)->generateToken();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findPasswordResetOfToken($token)
            ->willReturn($passwordReset)
            ->shouldBeCalledOnce();

        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->passwordResetIsValid($passwordReset)
            ->willReturn(false)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('PUT', '/users/password', [
            'uuid' => self::UUID_UNDER_TEST,
            'token' => $token,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD
        ], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::NOT_ALLOWED, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_NOT_ALLOWED, $responseCode);
    }
}