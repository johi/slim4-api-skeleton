<?php
declare(strict_types=1);

namespace App\Domain\Subscription;

use JsonSerializable;

class SubscriptionTopic implements JsonSerializable
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
    private $description;

    /**
     * @var string
     */
    private $created;

    /**
     * @var string|null
     */
    private $updated;

    public function __construct(string $uuid, string $name, string $description, string $created, ?string $updated)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->description = $description;
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
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'created' => $this->created,
            'updated' => $this->updated
        ];
    }
}
