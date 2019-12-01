<?php
declare(strict_types=1);

namespace Tests\Domain\User;

use App\Domain\User\PasswordReset;
use App\Infrastructure\Token\SimpleTokenService;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    public function passwordResetProvider()
    {
        return [
            ['00000000-0000-0000-0000-100000000000', '00000000-0000-0000-0000-000000000000', SimpleTokenService::generateToken(), '2029-10-07 12:00:00', true, '2019-10-07 10:00:00'],
            ['00000000-0000-0000-0000-100000000001', '00000000-0000-0000-0000-000000000001', SimpleTokenService::generateToken(), '2029-10-07 12:00:00', true, '2019-10-07 10:00:00'],
        ];
    }

    /**
     * @dataProvider passwordResetProvider
     * @param $uuid
     * @param $userUuid
     * @param $token
     * @param $expires
     * @param $active
     * @param $created
     */
    public function testGetters($uuid, $userUuid, $token, $expires, $active, $created)
    {
        $passwordReset = new PasswordReset($uuid, $userUuid, $token, $expires, $active, $created);

        $this->assertEquals($uuid, $passwordReset->getUuid());
        $this->assertEquals($userUuid, $passwordReset->getUserUuid());
        $this->assertEquals($token, $passwordReset->getToken());
        $this->assertEquals($expires, $passwordReset->getExpires());
        $this->assertEquals($active, $passwordReset->getActive());
    }

    /**
     * @dataProvider passwordResetProvider
     * @param $uuid
     * @param $userUuid
     * @param $token
     * @param $expires
     * @param $active
     * @param $created
     */
    public function testJsonSerialize($uuid, $userUuid, $token, $expires, $active, $created)
    {
        $userActivation = new PasswordReset($uuid, $userUuid, $token, $expires, $active, $created);
        $expectedPayload = json_encode([
            'uuid' => $uuid,
            'userUuid' => $userUuid,
            'token' => $token,
            'expires' => $expires,
            'active' => $active,
            'created' => $created
        ]);

        $this->assertEquals($expectedPayload, json_encode($userActivation));
    }
}
