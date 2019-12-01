<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\ActionPayload;
use App\Infrastructure\Email\EmailService;
use App\Infrastructure\Email\SimpleEmailMessage;
use App\Infrastructure\Persistence\User\UserRepository;
use Tests\ActionTestCase;

class RequestPasswordResetActionTest extends ActionTestCase
{

    public function testAction()
    {
        $user = $this->getUser();
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
            new ActionPayload(200, ['success' => 'ok', 'message' => 'email sent']),
            JSON_PRETTY_PRINT
        );
        $this->assertEquals($serializedPayload, $payload);
    }

    //findUserOfEmail throws some exception
    //createPasswordReset throws some exception

}
