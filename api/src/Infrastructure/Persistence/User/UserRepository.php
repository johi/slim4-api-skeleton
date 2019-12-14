<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\PasswordReset;
use App\Domain\User\User;
use App\Domain\User\UserActivation;
use App\Domain\User\UserLogin;

interface UserRepository
{

    /**
     * @param string $uuid
     * @return User
     */
    public function findUserOfUuid(string $uuid): ?User;

    /**
     * @param string $email
     * @return User
     */
    public function findUserOfEmail(string $email): ?User;

    /**
     * @param string $name
     * @param string $email
     * @param string $password
     * @return User
     */
    public function createUser(string $name, string $email, string $password): User;

    /**
     * @param User $user
     * @return User
     */
    public function activateUser(User $user): User;

    /**
     * @param User $user
     * @param string $password
     * @return User
     */
    public function updatePassword(User $user, string $password): User;

    /**
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $email, string $password): bool;

    /**
     * @param User $user
     * @return UserActivation
     */
    public function createUserActivation(User $user): UserActivation;

    /**
     * @param string $uuid
     * @return UserActivation
     */
    public function findUserActivationOfUuid(string $uuid): ?UserActivation;

    /**
     * @param string $token
     * @return UserActivation
     */
    public function findUserActivationOfToken(string $token): ?UserActivation;

    /**
     * @param User $user
     */
    public function invalidateUserActivations(User $user): void;

    /**
     * @param UserActivation $userActivation
     * @return bool
     */
    public function userActivationIsValid(UserActivation $userActivation): bool;

    /**
     * @param User $user
     * @return PasswordReset
     */
    public function createPasswordReset(User $user): PasswordReset;

    /**
     * @param string $token
     * @return PasswordReset
     */
    public function findPasswordResetOfToken(string $token): ?PasswordReset;

    /**
     * @param User $user
     */
    public function invalidatePasswordResets(User $user): void;

    /**
     * @param PasswordReset $passwordReset
     * @return bool
     */
    public function passwordResetIsValid(PasswordReset $passwordReset): bool;

    /**
     * @param User $user
     * @return string JWT token
     */
    public function login(User $user): string;

    /**
     * @param User $user
     * @param string $token
     * @return UserLogin
     */
    public function createUserLogin(User $user, string $token): UserLogin;

    /**
     * @param string $token
     * @return UserLogin
     */
    public function findUserLoginOfToken(string $token): ?UserLogin;

    /**
     * @param User $user
     */
    public function invalidateUserLogins(User $user): void;

    /**
     * @param UserLogin $userLogin
     * @return bool
     */
    public function userLoginIsValid(UserLogin $userLogin): bool;

    /**
     * @param string $token
     * @param string $userUuid
     * @return bool
     */
    public function verifyJwtToken(string $token, string $userUuid): bool;

}
