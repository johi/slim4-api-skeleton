<?php
declare(strict_types=1);

namespace App\Application\Configuration;
require __DIR__ . '/../../../app/configuration.php';

class AppConfiguration
{
    public static function getAll(): array
    {
        return getConfiguration();
    }

    public static function getKey(string $key): array
    {
        $configuration = getConfiguration();
        if (!isset($configuration[$key])) {
            throw new AppConfigurationException('Configuration key: ' . $key . ' not found');
        }
        return $configuration[$key];
    }

    public static function getBaseUrl(): string
    {
        $configuration = self::getKey('http');
        $port = ($configuration['port'] == '80') ? '' : ':' . $configuration['port'];
        return $configuration['protocol'] . '://' . $configuration['host'] . $port;
    }
}