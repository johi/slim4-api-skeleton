<?php
declare(strict_types=1);

namespace Tests\Domain\User;

use App\Domain\User\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function userProvider()
    {
        return [
            ['00000000-0000-0000-0000-000000000000', 'bill.gates', 'bill@gates.com', 'abcdefg', '2019-10-07 12:00:00', '2019-10-07 10:00:00', null],
            ['00000000-0000-0000-0000-000000000001', 'steve.jobs', 'steve@jobs.com', 'abcdefg', '2019-10-07 12:00:00', '2019-10-07 10:00:00', null],
            ['00000000-0000-0000-0000-000000000002', 'mark.zuckerberg', 'mark@zuckerberg.com', 'abcdefg', '2019-10-07 12:00:00', '2019-10-07 10:00:00', null],
            ['00000000-0000-0000-0000-000000000003', 'evan.spiegel', 'evan@spiegel.com', 'abcdefg', '2019-10-07 12:00:00', '2019-10-07 10:00:00', null],
            ['00000000-0000-0000-0000-000000000000', 'jack.dorsey', 'jack@dorsey.com', 'abcdefg', '2019-10-07 12:00:00', '2019-10-07 10:00:00', null],
        ];
    }

    /**
     * @dataProvider userProvider
     * @param $uuid
     * @param $name
     * @param $email
     * @param $passwordHash
     * @param $verified
     * @param $created
     * @param $updated
     */
    public function testGetters($uuid, $name, $email, $passwordHash, $verified, $created, $updated)
    {
        $user = new User($uuid, $name, $email, $passwordHash, $verified, $created, $updated);

        $this->assertEquals($uuid, $user->getUuid());
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($passwordHash, $user->getPasswordHash());
        $this->assertEquals($verified, $user->getVerified());
    }

    /**
     * @dataProvider userProvider
     * @param $uuid
     * @param $name
     * @param $email
     * @param $passwordHash
     * @param $verified
     * @param $created
     * @param $updated
     */
    public function testJsonSerialize($uuid, $name, $email, $passwordHash, $verified, $created, $updated)
    {
        $user = new User($uuid, $name, $email, $passwordHash, $verified, $created, $updated);

        $expectedPayload = json_encode([
            'uuid' => $uuid,
            'name' => $name,
            'email' => $email,
            'verified' => $verified,
            'created' => $created,
            'updated' => $updated
        ]);

        $this->assertEquals($expectedPayload, json_encode($user));
    }
}
