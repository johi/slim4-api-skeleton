<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Infrastructure\Email\EmailService;
use App\Infrastructure\Email\SimpleEmailMessage;
use App\Infrastructure\Persistence\User\UserRepository;

class RequestUserActivationActionTest extends UserActionTestCase
{

    public function testRequestUserActivationAction()
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

    public function testRequestUserActivationActionUserNotFound()
    {
        $user = $this->getUser();
        $userActivation = $this->getUserActivation();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn(null)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('POST', '/users/activation',[
            'email' => self::USER_EMAIL
        ], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::RESOURCE_NOT_FOUND, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_NOT_FOUND, $responseCode);
    }

    public function testRequestUserActivateActionAlreadyActivated()
    {
        $user = $this->getUser(self::UPDATED_TIMESTAMP);
        $userActivation = $this->getUserActivation();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('POST', '/users/activation',[
            'email' => self::USER_EMAIL
        ], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::NOT_ACCEPTABLE, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_NOT_ACCEPTABLE, $responseCode);
    }

}
