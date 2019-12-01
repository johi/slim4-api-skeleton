<?php
declare(strict_types=1);

namespace Tests\Domain\User;

use App\Domain\User\UserActivation;
use App\Infrastructure\Token\SimpleTokenService;
use Tests\TestCase;

class UserActivationTest extends TestCase
{
    public function userActivationProvider()
    {
        return [
            ['00000000-0000-0000-0000-100000000000', '00000000-0000-0000-0000-000000000000', SimpleTokenService::generateToken(), '2029-10-07 12:00:00', true, '2019-10-07 10:00:00'],
            ['00000000-0000-0000-0000-100000000001', '00000000-0000-0000-0000-000000000001', SimpleTokenService::generateToken(), '2029-10-07 12:00:00', true, '2019-10-07 10:00:00'],
        ];
    }

    /**
     * @dataProvider userActivationProvider
     * @param $uuid
     * @param $userUuid
     * @param $token
     * @param $expires
     * @param $active
     * @param $created
     */
    public function testGetters($uuid, $userUuid, $token, $expires, $active, $created)
    {
        $userActivation = new UserActivation($uuid, $userUuid, $token, $expires, $active, $created);

        $this->assertEquals($uuid, $userActivation->getUuid());
        $this->assertEquals($userUuid, $userActivation->getUserUuid());
        $this->assertEquals($token, $userActivation->getToken());
        $this->assertEquals($expires, $userActivation->getExpires());
        $this->assertEquals($active, $userActivation->getActive());
    }

    /**
     * @dataProvider userActivationProvider
     * @param $uuid
     * @param $userUuid
     * @param $token
     * @param $expires
     * @param $active
     * @param $created
     */
    public function testJsonSerialize($uuid, $userUuid, $token, $expires, $active, $created)
    {
        $userActivation = new UserActivation($uuid, $userUuid, $token, $expires, $active, $created);
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
