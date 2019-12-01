<?php

namespace Tests\Application\Actions\User;

use App\Infrastructure\Persistence\User\UserRepository;
use App\Infrastructure\Token\TokenService;
use Tests\ActionTestCase;

class PasswordResetActionTest extends ActionTestCase
{
    const NEW_PASSWORD = 'ILIKETOMOVEIT';

    public function testAction()
    {
        $user = $this->getUser();
        $passwordReset = $this->getPasswordReset();
        $token = $this->container->get(TokenService::class)->generateToken();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->findPasswordResetOfToken($token)
            ->willReturn($passwordReset)
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
}