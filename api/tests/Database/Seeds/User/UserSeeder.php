<?php
declare(strict_types=1);

// namespace Tests\Database\Seeds\User;

use App\Domain\User\PasswordReset;
use App\Domain\User\User;
use App\Domain\User\UserActivation;
use App\Domain\User\UserLogin;
use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    const DEFAULT_USER_UUID = '00000000-0000-0000-0000-000000000000';
    const DEFAULT_USER_NAME = 'John Doe';
    const DEFAULT_USER_EMAIL = 'john.doe@example.com';
    const DEFAULT_USER_PASSWORD_HASHED = '$2y$10$cE74vaMVaHpyYgsFG2KWMu4qFkruM8cnQdaKzDOUpYpiUaZmaRhJi'; // password_hash of 'gumm1G3d'
    const DEFAULT_USER_PASSWORD = 'gumm1G3d';
    const DEFAULT_EMAIL_VERIFIED = '2019-10-05 10:00:00+00';
    const DEFAULT_CREATED_AT = '2019-10-05 08:00:00+00';
    const DEFAULT_UPDATED_AT = '2019-10-05 08:00:00+00';
    const DEFAULT_USER_ACTIVATION_UUID = '10000000-0000-0000-0000-000000000000';
    const DEFAULT_USER_LOGIN_UUID = '20000000-0000-0000-0000-000000000000';
    const DEFAULT_TOKEN = '607b69bbf52b991bbeebfccdc080c397';
    const DEFAULT_TOKEN_EXPIRES = '2029-10-05 08:00:00+00';
    const DEFAULT_IS_ACTIVE = true;

    private static $usersTable;
    private static $userActivationsTable;
    private static $userLoginsTable;
    private static $passwordResetsTable;

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
        self::$usersTable = $this->table('users');
        self::$userActivationsTable = $this->table('user_activations');
        self::$userLoginsTable = $this->table('user_logins');
        self::$passwordResetsTable = $this->table('password_resets');
    }

    public static function addUser(array $overrides = []): User
    {
        $userArray = [
            'uuid' => $overrides['uuid'] ?? self::DEFAULT_USER_UUID,
            'name' => $overrides['name'] ?? self::DEFAULT_USER_NAME,
            'email' => $overrides['email'] ??  self::DEFAULT_USER_EMAIL,
            'password' => $overrides['password'] ?? self::DEFAULT_USER_PASSWORD_HASHED,
            'email_verified' => array_key_exists('email_verified', $overrides) ? $overrides['email_verified'] : self::DEFAULT_EMAIL_VERIFIED,
            'created_at' => $overrides['created_at'] ?? self::DEFAULT_CREATED_AT,
            'updated_at' => $overrides['updated_at'] ?? self::DEFAULT_UPDATED_AT
        ];
        self::$usersTable->insert([
            $userArray
        ])->save();
        return new User(
            $userArray['uuid'],
            $userArray['name'],
            $userArray['email'],
            $userArray['password'],
            $userArray['email_verified'],
            $userArray['created_at'],
            $userArray['updated_at']
        );
    }

    public static function addUserActivation(array $overrides = []): UserActivation
    {
        $userActivationArray = [
            'uuid' => $overrides['uuid'] ?? self::DEFAULT_USER_ACTIVATION_UUID,
            'user_uuid' => $overrides['user_uuid'] ?? self::DEFAULT_USER_UUID,
            'token' => $overrides['token'] ?? self::DEFAULT_TOKEN,
            'expires' => $overrides['expires'] ?? self::DEFAULT_TOKEN_EXPIRES,
            'created_at' => $overrides['created_at'] ?? self::DEFAULT_CREATED_AT,
            'is_active' => $overrides['is_active'] ?? self::DEFAULT_IS_ACTIVE
        ];
        self::$userActivationsTable->insert([
            $userActivationArray
        ])->save();
        return new UserActivation(
            $userActivationArray['uuid'],
            $userActivationArray['user_uuid'],
            $userActivationArray['token'],
            $userActivationArray['expires'],
            $userActivationArray['is_active'],
            $userActivationArray['created_at']
        );
    }

    public static function addUserLogin(array $overrides = []): UserLogin
    {
        $userLoginArray = [
            'uuid' => $overrides['uuid'] ?? self::DEFAULT_USER_LOGIN_UUID,
            'user_uuid' => $overrides['user_uuid'] ?? self::DEFAULT_USER_UUID,
            'token' => $overrides['token'] ?? self::DEFAULT_TOKEN,
            'expires' => $overrides['expires'] ?? self::DEFAULT_TOKEN_EXPIRES,
            'is_active' => $overrides['is_active'] ?? self::DEFAULT_IS_ACTIVE,
            'created_at' => $overrides['created_at'] ?? self::DEFAULT_CREATED_AT
        ];
        self::$userLoginsTable->insert([
            $userLoginArray
        ])->save();
        return new UserLogin(
            $userLoginArray['uuid'],
            $userLoginArray['user_uuid'],
            $userLoginArray['token'],
            $userLoginArray['expires'],
            $userLoginArray['is_active'],
            $userLoginArray['created_at']
        );
    }

    public static function addPasswordReset(array $overrides = []): PasswordReset
    {
        $passwordResetArray = [
            'uuid' => $overrides['uuid'] ?? self::DEFAULT_USER_LOGIN_UUID,
            'user_uuid' => $overrides['user_uuid'] ?? self::DEFAULT_USER_UUID,
            'token' => $overrides['token'] ?? self::DEFAULT_TOKEN,
            'expires' => $overrides['expires'] ?? self::DEFAULT_TOKEN_EXPIRES,
            'created_at' => $overrides['created_at'] ?? self::DEFAULT_CREATED_AT,
            'is_active' => $overrides['is_active'] ?? self::DEFAULT_IS_ACTIVE
        ];
        self::$passwordResetsTable->insert([
            $passwordResetArray
        ])->save();
        return new PasswordReset(
            $passwordResetArray['uuid'],
            $passwordResetArray['user_uuid'],
            $passwordResetArray['token'],
            $passwordResetArray['expires'],
            $passwordResetArray['is_active'],
            $passwordResetArray['created_at']
        );
    }
}
