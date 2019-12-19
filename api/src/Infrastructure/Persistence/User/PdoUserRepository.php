<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Application\Configuration\AppConfiguration;
use App\Domain\Exception\DomainRecordNotAuthorizedException;
use App\Domain\Exception\DomainRecordUpdateException;
use App\Domain\Exception\DomainServiceException;
use App\Domain\User\PasswordReset;
use App\Domain\User\User;
use App\Domain\User\UserActivation;
use App\Domain\User\UserLogin;
use App\Infrastructure\Database\PdoDatabaseService;
use App\Infrastructure\Token\TokenService;
use PDO;
use PDOException;

class PdoUserRepository implements UserRepository
{
    private $pdoDatabaseConnection;
    private $pdoDatabaseService;
    private $tokenService;
    private $activationTokenExpiration;
    private $passwordRequestTokenExpiration;
    private $loginTokenExpiration;

    public function __construct(PdoDatabaseService $service, TokenService $tokenService)
    {
        $security = AppConfiguration::getKey('security');
        $this->activationTokenExpiration = (int)$security['activation_token_expiration'];
        $this->passwordRequestTokenExpiration = (int)$security['password_request_token_expiration'];
        $this->loginTokenExpiration = (int)$security['jwt_expiration'];
        $this->pdoDatabaseService = $service;
        $this->pdoDatabaseConnection = $service->getConnection();
        $this->tokenService = $tokenService;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserOfUuid(string $uuid): ?User
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from users where uuid = :uuid");
            $statement->execute([
                ':uuid' => $uuid
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findUserOfUuid with uuid: %s', $uuid));
        }
        if (isset($result['uuid'])) {
            return $this->getUserFromRow($result);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserOfEmail(string $email): ?User
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from users where email = :email");
            $statement->execute([
                ':email' => $email
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findUserOfEmail with email :%s', $email));
        }
        if (isset($result['uuid'])) {
            return $this->getUserFromRow($result);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function createUser(string $name, string $email, string $password): User
    {
        try {
            $uuid = $this->pdoDatabaseService->fetchUuid();
            $query = "insert into users (uuid, name, email, password) values (:uuid, :name, :email, :password)";
            $statement = $this->pdoDatabaseConnection->prepare($query);
            $statement->execute([
                ':uuid' => $uuid,
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_BCRYPT)
            ]);
            return $this->findUserOfUuid($uuid);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for createUser name: %s email %s', $name, $email));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function activateUser(User $user): User
    {
        $query = "update users set email_verified=NOW(), updated_at=NOW() where uuid=:uuid";
        $uuid = $user->getUuid();
        try {
            $statement  = $this->pdoDatabaseConnection->prepare($query);
            $statement->execute([
                ':uuid' => $uuid
            ]);
        } catch (PDOException $exception) {
            throw new DomainServiceException(sprintf('SQL query failed for activateUser with uuid: %s', $uuid));
        }
        return $this->findUserOfUuid($uuid);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePassword(User $user, string $password): User
    {
        $query = "update users set password=:password, updated_at=NOW() where uuid=:uuid";
        $uuid = $user->getUuid();
        try {
            $statement  = $this->pdoDatabaseConnection->prepare($query);
            $statement->execute([
                ':password' => password_hash($password, PASSWORD_BCRYPT),
                ':uuid' => $uuid
            ]);
        } catch (PDOException $exception) {
            throw new DomainServiceException(sprintf('SQL query failed for updatePassword for user with uuid: %s', $uuid));
        }
        return $this->findUserOfUuid($uuid);
    }

    /**
     * {@inheritdoc}
     */
    public function verifyPassword(string $email, string $password): bool
    {
        $user = $this->findUserOfEmail($email);
        return password_verify($password, $user->getPasswordHash());
    }

    /**
     * {@inheritdoc}
     */
    public function findUserActivationOfUuid(string $uuid): ?UserActivation
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from user_activations where uuid = :uuid");
            $statement->execute([
                ':uuid' => $uuid
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findUserActivationOfUuid with uuid: %s', $uuid));
        }
        if (isset($result['uuid'])) {
            return $this->getUserActivationFromRow($result);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findUserActivationOfToken(string $token): ?UserActivation
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from user_activations where token = :token");
            $statement->execute([
                ':token' => $token
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findUserActivationOfUuid for token: %s', $token));
        }
        if (isset($result['uuid'])) {
            return $this->getUserActivationFromRow($result);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateUserActivations(User $user): void
    {
        $uuid = $user->getUuid();
        try {
            $query = "update user_activations set is_active = 'f' where user_uuid = :uuid";
            $statement = $this->pdoDatabaseConnection->prepare($query);
            $statement->execute([
                ':uuid' => $uuid
            ]);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for invalidateUserActivations for user of uuid: %s', $uuid));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createUserActivation(User $user): UserActivation
    {
        $token = $this->tokenService->generateToken();
        $userUuid = $user->getUuid();
        $this->invalidateUserActivations($user);
        try {
            $uuid = $this->pdoDatabaseService->fetchUuid();
            $expires = $this->pdoDatabaseService->fetchTimestamp($this->activationTokenExpiration);
            $query = "insert into user_activations (uuid, user_uuid, token, expires) values (:uuid, :user_uuid, :token, :expires)";
            $statement = $this->pdoDatabaseConnection->prepare($query);
            $statement->execute([
                ':uuid' => $uuid,
                ':user_uuid' => $userUuid,
                ':token' => $token,
                ':expires' => $expires
            ]);
        } catch (PDOException $exception) {
            throw new DomainServiceException(sprintf('SQL query failed for createUserActivation for user with uuid: %s', $userUuid));
        }
        return $this->findUserActivationOfToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function userActivationIsValid(UserActivation $userActivation): bool
    {
        $currentTimestamp = $this->pdoDatabaseService->fetchTimestamp();
        if (
            !$userActivation->getActive() ||
            ($userActivation->getExpires() < $currentTimestamp)
        ) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function findPasswordResetOfToken(string $token): ?PasswordReset
    {
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from password_resets where token = :token");
            $statement->execute([
                ':token' => $token
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findPasswordResetOfToken for token: %s', $token));
        }
        if (isset($result['uuid'])) {
            return $this->getPasswordResetFromRow($result);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidatePasswordResets(User $user): void
    {
        $uuid = $user->getUuid();
        try {
            $query = "update password_resets set is_active = 'f' where user_uuid = :uuid";
            $statement = $this->pdoDatabaseConnection->prepare($query);
            $statement->execute([
                ':uuid' => $uuid
            ]);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for invalidatePasswordResets for users uuid: %s', $uuid));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function passwordResetIsValid(PasswordReset $passwordReset): bool
    {
        $currentTimestamp = $this->pdoDatabaseService->fetchTimestamp();
        if (!$passwordReset->getActive() || ($passwordReset->getExpires() < $currentTimestamp)) {
            return false;
        }
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function createPasswordReset(User $user): PasswordReset
    {
        $userUuid = $user->getUuid();
        $token = $this->tokenService->generateToken();
        $this->invalidatePasswordResets($user);
        $uuid = $this->pdoDatabaseService->fetchUuid();
        $expires = $this->pdoDatabaseService->fetchTimestamp($this->passwordRequestTokenExpiration);
        $query = "insert into password_resets (uuid, user_uuid, token, expires) values (:uuid, :user_uuid, :token, :expires)";
        try {
            $statement = $this->pdoDatabaseConnection->prepare($query);
            $statement->execute([
                ':uuid' => $uuid,
                ':user_uuid' => $userUuid,
                ':token' => $token,
                ':expires' => $expires
            ]);
        } catch (PDOException $exception) {
            throw new DomainServiceException(sprintf('SQL query failed for createPasswordReset for user uuid: %s', $userUuid));
        }
        return $this->findPasswordResetOfToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function login(User $user): string
    {
        $uuid = $user->getUuid();
        $email = $user->getEmail();
        $timestamp = $this->pdoDatabaseService->fetchTimestamp();
        $identifier = $this->tokenService->generateToken();
        $this->createUserLogin($user, $identifier);
        return $this->tokenService->encodeJwt($timestamp, $identifier, [
           'uuid' => $uuid,
           'email' => $email
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserLoginOfToken(string $token): ?UserLogin
    {
        $result = null;
        try {
            $statement = $this->pdoDatabaseConnection->prepare("select * from user_logins where token = :token");
            $statement->execute([
                ':token' => $token
            ]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for findUserLoginOfToken with token: %s', $token));
        }
        if (isset($result['uuid'])) {
            return $this->getUserLoginFromRow($result);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function createUserLogin(User $user, string $token): UserLogin
    {
        $this->invalidateUserLogins($user);
        $uuid = $this->pdoDatabaseService->fetchUuid();
        $userUuid = $user->getUuid();
        $expires = $this->pdoDatabaseService->fetchTimestamp($this->loginTokenExpiration);
        $query = "insert into user_logins (uuid, user_uuid, token, expires) values (:uuid, :user_uuid, :token, :expires)";
        try {
            $statement = $this->pdoDatabaseConnection->prepare($query);
            $statement->execute([
                ':uuid' => $uuid,
                ':user_uuid' => $userUuid,
                ':token' => $token,
                ':expires' => $expires
            ]);
        } catch (PDOException $exception) {
            throw new DomainServiceException(sprintf('SQL query failed for createUserLogin for users uuid: %s and token: %s', $userUuid, $token));
        }
        return $this->findUserLoginOfToken($token);
    }

    /**
     * {@inheritdoc}
     */
    public function verifyJwtToken(string $token, string $userUuid): bool
    {
        $decodedToken = $this->tokenService->decodeJwt($token);
        $security =  AppConfiguration::getKey('security');
        if ($decodedToken['iss'] !== $security['server_name']) {
            //@todo do the reporting as promised :-P
            throw new DomainRecordNotAuthorizedException(sprintf('Illicit issuer domain: %s, this incidence will be reported!', $decodedToken['iss']));
        }
        if ($decodedToken['data']['uuid'] !== $userUuid) {
            throw new DomainRecordNotAuthorizedException(sprintf('Token: %s does not belong to user of uuid: %s', $token, $userUuid));
        }
        $userLogin = $this->findUserLoginOfToken($decodedToken['jti']);
        return $this->userLoginIsValid($userLogin);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateUserLogins(User $user): void
    {
        $query = "update user_logins set is_active = 'f' where user_uuid = :uuid";
        $uuid = $user->getUuid();
        try {
            $statement = $this->pdoDatabaseConnection->prepare($query);
            $statement->execute([
                ':uuid' => $user->getUuid()
            ]);
        } catch (PDOException $e) {
            throw new DomainServiceException(sprintf('SQL query failed for invalidateUserLogins for users with uuid: %s', $uuid));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function userLoginIsValid(UserLogin $userLogin): bool
    {
        $currentTimestamp = $this->pdoDatabaseService->fetchTimestamp();
        if (!$userLogin->getActive() || ($userLogin->getExpires() < $currentTimestamp)) {
            return false;
        }
        return true;
    }

    /**
     * @param array $result
     * @return User
     */
    private function getUserFromRow(array $result): User
    {
        return new User(
            $result['uuid'],
            $result['name'],
            $result['email'],
            $result['password'],
            $result['email_verified'],
            $result['created_at'],
            $result['updated_at']
        );
    }

    /**
     * @param array $result
     * @return UserActivation
     */
    private function getUserActivationFromRow(array $result): UserActivation
    {
        return new UserActivation(
            $result['uuid'],
            $result['user_uuid'],
            $result['token'],
            $result['expires'],
            $result['is_active'],
            $result['created_at']
        );
    }

    /**
     * @param array $result
     * @return PasswordReset
     */
    private function getPasswordResetFromRow(array $result): PasswordReset
    {
        return new PasswordReset(
            $result['uuid'],
            $result['user_uuid'],
            $result['token'],
            $result['expires'],
            $result['is_active'],
            $result['created_at']
        );
    }

    /**
     * @param array $result
     * @return UserLogin
     */
    private function getUserLoginFromRow(array $result): UserLogin
    {
        return new UserLogin(
            $result['uuid'],
            $result['user_uuid'],
            $result['token'],
            $result['expires'],
            $result['is_active'],
            $result['created_at']
        );
    }
}
