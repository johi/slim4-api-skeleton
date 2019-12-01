<?php
declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class UserActivation implements JsonSerializable
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $userUuid;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $expires;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var string
     */
    private $created;


    /**
     * @param string $uuid
     * @param string $userUuid
     * @param string $token
     * @param string $expires
     * @param bool $active
     * @param string $created
     */
    public function __construct(string $uuid, string $userUuid, string $token, string $expires, bool $active, string $created)
    {
        $this->uuid = $uuid;
        $this->userUuid = $userUuid;
        $this->token = $token;
        $this->expires = $expires;
        $this->active = $active;
        $this->created = $created;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getUserUuid(): string
    {
        return $this->userUuid;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getExpires(): string
    {
        return $this->expires;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'uuid' => $this->uuid,
            'userUuid' => $this->userUuid,
            'token' => $this->token,
            'expires' => $this->expires,
            'active' => $this->active,
            'created' => $this->created,
        ];
    }
}
