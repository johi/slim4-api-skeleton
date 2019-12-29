<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Infrastructure\Persistence\User\UserRepository;

class ConfirmPasswordResetActionTest extends UserActionTestCase
{

    public function testConfirmPasswordResetAction()
    {
        $user = $this->getUser();
        $passwordReset = $this->getPasswordReset();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findPasswordResetOfToken($passwordReset->getToken())
            ->willReturn($passwordReset)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->passwordResetIsValid($passwordReset)
            ->willReturn(true)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $payload = $this->makeRequest('GET', '/users/password/' . $passwordReset->getToken());
        $serializedPayload = json_encode(new ActionPayload(Action::HTTP_OK, ['success' => 'ok']), JSON_PRETTY_PRINT);
        $this->assertEquals($serializedPayload, $payload);
    }

    public function testConfirmPasswordResetActionTokenNotFound()
    {
        $user = $this->getUser();
        $passwordReset = $this->getPasswordReset();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findPasswordResetOfToken($passwordReset->getToken())
            ->willReturn(null)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('GET', '/users/password/' . $passwordReset->getToken(), [], $responseCode);
        $decodePayload = json_decode($payload, true);
        $this->assertTrue($decodePayload['error']);
        $this->assertEquals(ActionError::RESOURCE_NOT_FOUND, $decodePayload['type']);
        $this->assertEquals(Action::HTTP_NOT_FOUND, $responseCode );
    }

    public function testConfirmPasswordResetInvalid()
    {
        $user = $this->getUser();
        $passwordReset = $this->getPasswordReset();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findPasswordResetOfToken($passwordReset->getToken())
            ->willReturn($passwordReset)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->passwordResetIsValid($passwordReset)
            ->willReturn(false)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('GET', '/users/password/' . $passwordReset->getToken(), [], $responseCode);
        $decodePayload = json_decode($payload, true);
        $this->assertTrue($decodePayload['error']);
        $this->assertEquals(ActionError::NOT_ALLOWED, $decodePayload['type']);
        $this->assertEquals(Action::HTTP_NOT_ALLOWED, $responseCode);
    }
}
