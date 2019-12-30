<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Infrastructure\Email\EmailService;
use App\Infrastructure\Email\SimpleEmailMessage;
use App\Infrastructure\Persistence\User\UserRepository;

class RequestPasswordResetActionTest extends UserActionTestCase
{

    public function testRequestPasswordResetAction()
    {
        $user = $this->getUser(self::UPDATED_TIMESTAMP);
        $passwordReset = $this->getPasswordReset();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->createPasswordReset($user)
            ->willReturn($passwordReset)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        $emailServiceProphecy = $this->prophesize(EmailService::class);
        $emailServiceProphecy->send( new SimpleEmailMessage('forgotPassword.html', [
            'name' => $user->getName(),
            'token' => $passwordReset->getToken()
        ],
            'Your password reset link',
            [$user->getEmail() => $user->getName()]
        ))
            ->shouldBeCalledOnce();
        $this->container->set(EmailService::class, $emailServiceProphecy->reveal());

        $payload = $this->makeRequest('POST', '/users/password', [
            'email' => self::USER_EMAIL
        ]);
        $serializedPayload = json_encode(
            new ActionPayload(Action::HTTP_OK, ['success' => 'ok', 'message' => 'email sent']),
            JSON_PRETTY_PRINT
        );
        $this->assertEquals($serializedPayload, $payload);
    }

    public function testRequestPasswordResetActionUserNotFound()
    {
        $user = $this->getUser(self::UPDATED_TIMESTAMP);
        $passwordReset = $this->getPasswordReset();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn(null)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('POST', '/users/password', [
            'email' => self::USER_EMAIL
        ], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::RESOURCE_NOT_FOUND, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_NOT_FOUND, $responseCode);
    }

    public function testRequestPasswordResetActionUserNotVerified()
    {
        $user = $this->getUser();
        $passwordReset = $this->getPasswordReset();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('POST', '/users/password', [
            'email' => self::USER_EMAIL
        ], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::NOT_ACCEPTABLE, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_NOT_ACCEPTABLE, $responseCode);
    }
}
