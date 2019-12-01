<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\ActionPayload;
use App\Infrastructure\Persistence\User\UserRepository;
use Tests\ActionTestCase;

class ConfirmPasswordResetActionTest extends ActionTestCase
{

    public function testAction()
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
        $serializedPayload = json_encode(new ActionPayload(200, ['success' => 'ok']), JSON_PRETTY_PRINT);
        $this->assertEquals($serializedPayload, $payload);
    }
}
