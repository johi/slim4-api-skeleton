<?php
declare(strict_types=1);

namespace App\Domain\Subscription;

use JsonSerializable;

class Subscription implements JsonSerializable
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
    private $subscriptionTopicUuid;

    /**
     * @var bool
     */
    private $isConfirmed;

    /**
     * @var bool
     */
    private $isActive;

    /**
     * @var string
     */
    private $created;

    /**
     * @var string|null
     */
    private $updated;

    public function __construct(string $uuid, string $userUuid, string $subscriptionTopicUuid, bool $isConfirmed, bool $isActive, string $created, ?string $updated)
    {
        $this->uuid = $uuid;
        $this->userUuid = $userUuid;
        $this->subscriptionTopicUuid = $subscriptionTopicUuid;
        $this->isConfirmed = $isConfirmed;
        $this->isActive = $isActive;
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
    public function getUserUuid(): string
    {
        return $this->userUuid;
    }

    /**
     * @return string
     */
    public function getSubscriptionTopicUuid(): string
    {
        return $this->subscriptionTopicUuid;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->isConfirmed;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'uuid' => $this->uuid,
            'userUuid' => $this->userUuid,
            'subscriptionTopicUuid' => $this->subscriptionTopicUuid,
            'isConfirmed' => $this->isConfirmed,
            'isActive' => $this->isActive,
            'created' => $this->created,
            'updated' => $this->updated
        ];
    }
}
