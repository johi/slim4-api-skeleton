<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Infrastructure\Persistence\User\UserRepository;
use App\Infrastructure\Email\EmailService;
use App\Infrastructure\Email\SimpleEmailMessage;

class RegisterUserActionTest extends UserActionTestCase
{

    public function testRegisterUserAction()
    {
        $user = $this->getUser();
        $userActivation = $this->getUserActivation();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn(null)
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->createUser(self::USER_NAME, self::USER_EMAIL, self::USER_PASSWORD)
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

        $payload = $this->makeRequest('POST', '/users/register', [
            'name' => self::USER_NAME,
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
            'password_confirmation' => self::USER_PASSWORD
        ]);
        $serializedPayload = json_encode(new ActionPayload(Action::HTTP_CREATED, $user), JSON_PRETTY_PRINT);
        $this->assertEquals($serializedPayload, $payload);
    }

    public function testRegisterUserActionUserAlreadyExists()
    {
        $user = $this->getUser();
        $userActivation = $this->getUserActivation();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        $responseCode = 0;
        $payload = $this->makeRequest('POST', '/users/register', [
            'name' => self::USER_NAME,
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
            'password_confirmation' => self::USER_PASSWORD
        ], $responseCode);
        $decodedPayload = json_decode($payload, true);
        $this->assertTrue($decodedPayload['error']);
        $this->assertEquals(ActionError::CONFLICT, $decodedPayload['type']);
        $this->assertEquals(Action::HTTP_CONFLICT, $responseCode);
    }

}