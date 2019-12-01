<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    const INVALID_ACTIVATION_TOKEN = '607b69bbf52b991bbeebfccdc080c397';
    const VALID_ACTIVATION_TOKEN = '607b69bbf52b991bbeebfccdc080c123';
    const INVALID_PASSWORD_RESET_TOKEN = '607b69bbf52b991bbeebfccdc080c397';
    const VALID_PASSWORD_RESET_TOKEN = '607b69bbf52b991bbeebfccdc080c123';
    const INVALID_USER_LOGIN_TOKEN = '607b69bbf52b991bbeebfccdc080c397';
    const VALID_USER_LOGIN_TOKEN = '607b69bbf52b991bbeebfccdc080c123';
    const PASSWORD_HASHED = '$2y$10$cE74vaMVaHpyYgsFG2KWMu4qFkruM8cnQdaKzDOUpYpiUaZmaRhJi';

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $usersTable = $this->table('users');
        $usersTable->insert([
            [
                'uuid' => '00000000-0000-0000-0000-000000000000',
                'name' => 'Johan Lerche Schulz',
                'email' => 'johan.schulz@gmail.com',
                'password' => self::PASSWORD_HASHED,
                'email_verified' => '2019-10-05 10:00:00.000000',
                'created_at' => '2019-10-05 08:00:00.000000',
                'updated_at' => '2019-10-05 10:00:00.000000'
            ],
            [
                'uuid' => '00000000-0000-0000-0000-000000000001',
                'name' => 'Jerry Lewis',
                'email' => 'jerry.lewis@example.com',
                'password' => self::PASSWORD_HASHED,
                'email_verified' => null,
                'created_at' => '2019-10-05 08:00:00.000000',
                'updated_at' => null
            ],
            [
                'uuid' => '00000000-0000-0000-0000-000000000002',
                'name' => 'Burt Reynolds',
                'email' => 'reynolds@example.com',
                'password' => self::PASSWORD_HASHED,
                'email_verified' => null,
                'created_at' => '2019-10-05 08:00:00.000000',
                'updated_at' => null
            ]
        ])->save();

        $userActivationsTable = $this->table('user_activations');
        $userActivationsTable->insert([
            [
                'uuid' => '10000000-0000-0000-0000-000000000001',
                'user_uuid' => '00000000-0000-0000-0000-000000000001',
                'token' => self::INVALID_ACTIVATION_TOKEN,
                'expires' => '2019-10-05 09:00:00.000000',
                'created_at' => '2019-10-05 08:00:00.000000'
            ],
            [
                'uuid' => '10000000-0000-0000-0000-000000000002',
                'user_uuid' => '00000000-0000-0000-0000-000000000002',
                'token' => self::VALID_ACTIVATION_TOKEN,
                'expires' => '2029-10-05 09:00:00.000000',
                'created_at' => '2019-10-05 08:00:00.000000'
            ]
        ])->save();

        $passwordResetsTable = $this->table('password_resets');
        $passwordResetsTable->insert([
            [
                'uuid' => '20000000-0000-0000-0000-000000000001',
                'user_uuid' => '00000000-0000-0000-0000-000000000001',
                'token' => self::INVALID_PASSWORD_RESET_TOKEN,
                'expires' => '2019-10-05 09:00:00.000000',
                'created_at' => '2019-10-05 08:00:00.000000'
            ],
            [
                'uuid' => '20000000-0000-0000-0000-000000000002',
                'user_uuid' => '00000000-0000-0000-0000-000000000002',
                'token' => self::VALID_PASSWORD_RESET_TOKEN,
                'expires' => '2029-10-05 09:00:00.000000',
                'created_at' => '2019-10-05 08:00:00.000000'
            ]
        ])->save();

        $userLoginsTable = $this->table('user_logins');
        $userLoginsTable->insert([
            [
                'uuid' => '20000000-0000-0000-0000-000000000001',
                'user_uuid' => '00000000-0000-0000-0000-000000000001',
                'token' => self::INVALID_USER_LOGIN_TOKEN,
                'expires' => '2019-10-05 09:00:00.000000',
                'created_at' => '2019-10-05 08:00:00.000000'
            ],
            [
                'uuid' => '20000000-0000-0000-0000-000000000002',
                'user_uuid' => '00000000-0000-0000-0000-000000000002',
                'token' => self::VALID_USER_LOGIN_TOKEN,
                'expires' => '2029-10-05 09:00:00.000000',
                'created_at' => '2019-10-05 08:00:00.000000'
            ]
        ])->save();
    }
}
