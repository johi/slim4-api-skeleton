<?php
declare(strict_types=1);

namespace Tests\Application\Actions\User;

use App\Domain\User\PasswordReset;
use App\Domain\User\User;
use App\Domain\User\UserActivation;
use App\Infrastructure\Token\TokenService;
use Tests\ActionTestCase;

abstract class UserActionTestCase extends ActionTestCase
{
    const USER_NAME = 'bill.gates';
    const USER_EMAIL = 'bill@example.com';
    const USER_PASSWORD = 'abcdefg';
    const CREATED_TIMESTAMP = '2019-10-05 08:00:00';
    const UPDATED_TIMESTAMP = '2019-10-05 10:00:00';

    public function getToken()
    {
        return $this->container->get(TokenService::class)->generateToken();
    }

    public function getUser($verified = null)
    {
        return new User(
            self::UUID_UNDER_TEST,
            self::USER_NAME,
            self::USER_EMAIL,
            self::USER_PASSWORD,
            $verified,
            self::CREATED_TIMESTAMP,
            null
        );
    }

    public function getUserActivation()
    {
        return new UserActivation(
            self::UUID_UNDER_TEST,
            self::UUID_UNDER_TEST,
            $this->getToken(),
            self::CREATED_TIMESTAMP,
            true,
            self::UPDATED_TIMESTAMP
        );
    }

    public function getPasswordReset()
    {
        return new PasswordReset(
            self::UUID_UNDER_TEST,
            self::UUID_UNDER_TEST,
            $this->getToken(),
            self::CREATED_TIMESTAMP,
            true,
            self::UPDATED_TIMESTAMP
        );
    }

}