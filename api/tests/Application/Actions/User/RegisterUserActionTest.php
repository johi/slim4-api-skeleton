<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\ActionPayload;
use App\Infrastructure\Persistence\User\UserRepository;
use App\Infrastructure\Email\EmailService;
use App\Infrastructure\Email\SimpleEmailMessage;
use Tests\ActionTestCase;

class RegisterUserActionTest extends ActionTestCase
{

    public function testAction()
    {
        $user = $this->getUser();
        $userActivation = $this->getUserActivation();

        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
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
        $serializedPayload = json_encode(new ActionPayload(201, $user), JSON_PRETTY_PRINT);
        $this->assertEquals($serializedPayload, $payload);
    }

    //createUser throws some exception
    //createUserActivation throws some exception
}