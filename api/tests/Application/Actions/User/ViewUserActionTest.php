<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Application\Actions\Action;
use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Infrastructure\Persistence\User\UserRepository;


class ViewUserActionTest extends UserActionTestCase
{

    public function testViewUserAction()
    {
        //TEST SETUP
        $user = $this->getUser();
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willReturn($user)
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        //EXECUTION
        $payload = $this->makeRequest('GET', '/users/' . self::UUID_UNDER_TEST);
        $expectedPayload = new ActionPayload(Action::HTTP_OK, $user);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);
        $this->assertEquals($serializedPayload, $payload);
    }

    public function testViewUserActionThrowsUserNotFoundException()
    {
        //TEST SETUP
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->findUserOfUuid(self::UUID_UNDER_TEST)
            ->willThrow(new DomainRecordNotFoundException())
            ->shouldBeCalledOnce();
        $this->container->set(UserRepository::class, $userRepositoryProphecy->reveal());

        //EXECUTION
        $payload = $this->makeRequest('GET', '/users/' . self::UUID_UNDER_TEST);
        $expectedError = new ActionError(
            ActionError::RESOURCE_NOT_FOUND,
            'Domain record not found'
        );
        $serializedPayload = json_encode(
            new ActionPayload(Action::HTTP_NOT_FOUND, null, $expectedError),
            JSON_PRETTY_PRINT
        );
        $this->assertEquals($serializedPayload, $payload);
    }
}
