<?php

namespace App\Application\Configuration;
require __DIR__ . '/../../../app/configuration.php';

class AppConfiguration
{
    public static function getAll()
    {
        return getConfiguration();
    }

    public static function getKey(string $key)
    {
        $configuration = getConfiguration();
        if (!isset($configuration[$key])) {
            throw new AppConfigurationException('Configuration key: ' . $key . ' not found');
        }
        return $configuration[$key];
    }
}