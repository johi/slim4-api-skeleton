<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\ActionPayload;
use App\Infrastructure\Email\EmailService;
use App\Infrastructure\Email\SimpleEmailMessage;
use App\Infrastructure\Persistence\User\UserRepository;
use Tests\ActionTestCase;

class RequestUserActivationActionTest extends ActionTestCase
{

    public function testAction()
    {
        $user = $this->getUser();
        $userActivation = $this->getUserActivation();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn($user)
            ->shouldBeCalledOnce();

        $userRepositoryProphecy
            ->createUserActivation($user)
            ->willReturn($userActivation)
            ->shouldBeCalledOnce();

        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $emailServiceProphecy = $this->prophesize(EmailService::class);
        $emailServiceProphecy->send( new SimpleEmailMessage('confirm.html', [
            'name' => $user->getName(),
            'token' => $userActivation->getToken()
        ],
            'Please confirm your email',
            [$user->getEmail() => $user->getName()]
        ))
            ->shouldBeCalledOnce();
        $this->container->set(EmailService::class, $emailServiceProphecy->reveal());

        $payload = $this->makeRequest('POST', '/users/activation',[
            'email' => self::USER_EMAIL
        ]);
        $serializedPayload = json_encode(
            new ActionPayload(200, ['success' => 'ok', 'message' => 'email sent']),
            JSON_PRETTY_PRINT
        );
        $this->assertEquals($serializedPayload, $payload);
    }

    //findUserOfUuid throws some exception

}
