<?php

namespace Tests\Application\Actions\User;

use App\Infrastructure\Persistence\User\UserRepository;
use Tests\ActionTestCase;

class LoginActionTest extends ActionTestCase
{
    public function testAction()
    {
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfEmail(self::USER_EMAIL)
            ->willReturn($this->getUser())
            ->shouldBeCalledOnce();
        $userRepositoryProphecy
            ->login($this->getUser(), self::USER_PASSWORD)
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
    //findUserOfEmail not found throws some exception
    //login throws some exception
}