<?php
declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class User implements JsonSerializable
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string|null
     */
    private $verified;

    /**
     * @var string
     */
    private $created;

    /**
     * @var string|null
     */
    private $updated;

    /**
     * @var string
     */
    private $passwordHash;

    /**
     * @param string $uuid
     * @param string $name
     * @param string $email
     * @param string $passwordHash
     * @param string|null $verified
     * @param string $created
     * @param string|null $updated
     */
    public function __construct(string $uuid, string $name, string $email, string $passwordHash, ?string $verified, string $created, ?string $updated)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->verified = $verified;
        $this->created = $created;
        $this->updated = $updated;
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
    public function getName(): string
    {
        return strtolower($this->name);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @return string
     */
    public function getVerified(): ?string
    {
        return $this->verified;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'verified' => $this->verified,
            'created' => $this->created,
            'updated' => $this->updated
        ];
    }
}

