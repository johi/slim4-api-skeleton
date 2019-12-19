<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\User;

use App\Infrastructure\Persistence\User\PdoUserRepository;
use App\Infrastructure\Token\SimpleTokenService;
use BaseUserSeeder;
use Tests\DatabaseTestCase;

class PdoUserRepositoryTest extends DatabaseTestCase
{
    const NON_EXISTING_UUID = '99999999-9999-9999-9999-999999999999';
    const NON_EXISTING_EMAIL = 'bill@gates.com';

    protected static $pdoUserRepository;

    protected static $seedSubPath = '/User';

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$pdoUserRepository = new PdoUserRepository(self::$databaseService, new SimpleTokenService());
    }

    /**
     * setUp migrates test db migrations for each test, it would be better doing it in the superclass DatabaseTestCase
     * setUpBeforeClass method, but since phinx does not support truncating tables with foreign key constraints,
     * it is done here
     */
    public function setUp()
    {
        self::$manager->migrate('test');
    }

    /**
     * tearDown rolls back migrations to origin for test db for each test,
     * it would be better doing it in the superclass DatabaseTestCase
     * tearDownAfterClass method, but since phinx does not support truncating tables with foreign key constraints,
     * it is done here
     */
    public function tearDown()
    {
        self::$manager->rollback('test', 0);
    }

    public function testFindUserOfUuid()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser();
        $this->assertEquals(
            $user,
            self::$pdoUserRepository->findUserOfUuid(
                $user->getUuid()
            )
        );
        $this->assertNull(self::$pdoUserRepository->findUserOfUuid(self::NON_EXISTING_UUID));
    }

    public function testFindUserOfEmail()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser();
        $this->assertEquals(
            $user,
            self::$pdoUserRepository->findUserOfEmail(
                $user->getEmail()
            ));
        $this->assertNull(self::$pdoUserRepository->findUserOfEmail(self::NON_EXISTING_EMAIL));
    }

    public function testCreateUser()
    {
        $name = 'Dr. Knowitall';
        $email = 'doctor@example.com';
        $password = 'weirdandclear';
        $this->assertInstanceOf('App\Domain\User\User', self::$pdoUserRepository->createUser($name, $email, $password));
        $this->assertEquals($email, self::$pdoUserRepository->findUserOfEmail($email)->getEmail());
    }

    public function testActivateUser()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser(['email_verified' => null]);
        $user = self::$pdoUserRepository->activateUser($user);
        $this->assertNotNull($user->getVerified());
    }

    public function testVerifyPassword()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser();
        $this->assertTrue(self::$pdoUserRepository->verifyPassword($user->getEmail(), BaseUserSeeder::DEFAULT_USER_PASSWORD));
        $this->assertFalse(self::$pdoUserRepository->verifyPassword($user->getEmail(), 'something'));
    }

    public function testUpdatePassword()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser();
        $newPassword = 'somethingelse';
        self::$pdoUserRepository->updatePassword($user, $newPassword);
        $this->assertTrue(self::$pdoUserRepository->verifyPassword($user->getEmail(), $newPassword));
        $this->assertFalse(self::$pdoUserRepository->verifyPassword($user->getEmail(), BaseUserSeeder::DEFAULT_USER_PASSWORD));
    }

    public function testFindUserActivationOfUuid()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser(['email_verified' => null]);
        $userActivation = BaseUserSeeder::addUserActivation();
        $this->assertInstanceOf('\App\Domain\User\UserActivation', self::$pdoUserRepository->findUserActivationOfUuid($userActivation->getUuid()));
        $this->assertNull(self::$pdoUserRepository->findUserActivationOfUuid(self::NON_EXISTING_UUID));
    }

    public function testFindUserActivationOfToken()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser(['email_verified' => null]);
        $userActivation = BaseUserSeeder::addUserActivation();
        $this->assertInstanceOf('\App\Domain\User\UserActivation', self::$pdoUserRepository->findUserActivationOfToken($userActivation->getToken()));
        $this->assertNull(self::$pdoUserRepository->findUserActivationOfToken('123'));
    }

    public function testUserActivationIsValid()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser(['email_verified' => null]);
        $userActivation = BaseUserSeeder::addUserActivation();
        $userActivation = self::$pdoUserRepository->findUserActivationOfToken($userActivation->getToken());
        $this->assertTrue(self::$pdoUserRepository->userActivationIsValid($userActivation));
        $otherUserActivationsUuid = '10000000-0000-0000-0000-000000000001';
        $otherUsersUuid = '00000000-0000-0000-0000-000000000001';
        $user = BaseUserSeeder::addUser([
            'uuid' => $otherUsersUuid,
            'email' => 'test2@example.com',
            'email_verified' => null
        ]);
        $token = $userActivation->getToken() . 'A';
        $userActivation = BaseUserSeeder::addUserActivation([
            'uuid' => $otherUserActivationsUuid,
            'user_uuid' => $otherUsersUuid,
            'token' => $token,
            'expires' => date('Y-m-d H:i:s', time() - 3600),
            'is_active' => false
        ]);
        $userActivation = self::$pdoUserRepository->findUserActivationOfToken($token);
        $this->assertFalse(self::$pdoUserRepository->userActivationIsValid($userActivation));
    }

    public function testCreateUserActivation()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser(['email_verified' => null]);
        $userActivation = self::$pdoUserRepository->createUserActivation($user);
        $this->assertInstanceOf('\App\Domain\User\UserActivation', $userActivation);
        $this->assertTrue(self::$pdoUserRepository->userActivationIsValid($userActivation));
        $newUserActivation = self::$pdoUserRepository->createUserActivation($user);
        $this->assertTrue(self::$pdoUserRepository->userActivationIsValid($newUserActivation));
        //old userActivation no longer valid
        $userActivation = self::$pdoUserRepository->findUserActivationOfToken($userActivation->getToken());
        $this->assertFalse(self::$pdoUserRepository->userActivationIsValid($userActivation));
    }

    /**
     * @expectedException \App\Domain\Exception\DomainRecordUpdateException
     */
    public function testCreateUserActivationOnAlreadyActivatedUserThrowsDomainRecordUpdateException()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser();
        $userActivation = self::$pdoUserRepository->createUserActivation($user);
    }

    public function testFindUserLoginOfToken()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser();
        $userLogin = BaseUserSeeder::addUserLogin();
        $this->assertEquals(
            $userLogin,
            self::$pdoUserRepository->findUserLoginOfToken($userLogin->getToken())
        );
        $this->assertNull(self::$pdoUserRepository->findUserLoginOfToken($userLogin->getToken() . 'A'));
    }

    public function testCreateUserLogin()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser();
        $userLogin = self::$pdoUserRepository->createUserLogin($user, SimpleTokenService::generateToken());
        $this->assertInstanceOf('\App\Domain\User\UserLogin', $userLogin);
        $this->assertTrue(self::$pdoUserRepository->userLoginIsValid($userLogin));
        $newUserLogin = self::$pdoUserRepository->createUserLogin($user, SimpleTokenService::generateToken());
        $this->assertTrue(self::$pdoUserRepository->userLoginIsValid($newUserLogin));
        //old userLogin no longer valid
        $userLogin = self::$pdoUserRepository->findUserLoginOfToken($userLogin->getToken());
        $this->assertFalse(self::$pdoUserRepository->userLoginIsValid($userLogin));
    }

    public function testLogin()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser();
        $token = self::$pdoUserRepository->login($user);
        $this->assertIsString($token);
        $this->assertTrue(self::$pdoUserRepository->verifyJwtToken($token, $user->getUuid()));
    }

//    /**
//     * @expectedException \App\Domain\Exception\DomainRecordRequestException
//     */
//    public function testLoginThrowsDomainRecordRequestException()
//    {
//        self::$manager->seed('test', 'BaseUserSeeder');
//        $user = BaseUserSeeder::addUser();
//        $token = self::$pdoUserRepository->login($user, 'somethingelse');
//    }

    public function testFindPasswordResetOfToken()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser();
        $passwordReset = BaseUserSeeder::addPasswordReset();
        $this->assertInstanceOf('\App\Domain\User\PasswordReset', self::$pdoUserRepository->findPasswordResetOfToken($passwordReset->getToken()));
        $this->assertEquals(
            $passwordReset,
            self::$pdoUserRepository->findPasswordResetOfToken($passwordReset->getToken())
        );
        $this->assertNull(self::$pdoUserRepository->findPasswordResetOfToken($passwordReset->getToken() . 'A'));
    }

    public function testCreatePasswordReset()
    {
        self::$manager->seed('test', 'BaseUserSeeder');
        $user = BaseUserSeeder::addUser();
        $passwordReset = self::$pdoUserRepository->createPasswordReset($user);
        $this->assertInstanceOf('\App\Domain\User\PasswordReset', $passwordReset);
        $this->assertTrue(self::$pdoUserRepository->passwordResetIsValid($passwordReset));
        $newPasswordReset = self::$pdoUserRepository->createPasswordReset($user);
        $this->assertTrue(self::$pdoUserRepository->passwordResetIsValid($newPasswordReset));
        //old userLogin no longer valid
        $passwordReset = self::$pdoUserRepository->findPasswordResetOfToken($passwordReset->getToken());
        $this->assertFalse(self::$pdoUserRepository->passwordResetIsValid($passwordReset));
    }
}
