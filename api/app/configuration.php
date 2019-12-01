<?php

use Monolog\Logger;

function getConfiguration() {
    return [
        'displayErrorDetails' => true, // Should be set to false in production
        'logger' => [
            'name' => 'slim-api',
            'path' => isset($_ENV['DOCKER']) ? 'php://stdout' : __DIR__ . '/../logs/api.log',
            'level' => Logger::DEBUG,
        ],
        'database' => [
            'development' => [
                'driver' => $_ENV['DB_DRIVER'],
                'host' => $_ENV['DB_HOST'],
                'port' => $_ENV['DB_PORT'],
                'database' => $_ENV['DB_DATABASE'],
                'username' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD']
            ],
            'test' => [
                'driver' => $_ENV['DB_DRIVER'],
                'host' => $_ENV['DB_HOST'],
                'port' => $_ENV['DB_PORT'],
                'database' => $_ENV['DB_DATABASE'] . '_test',
                'username' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD']
            ]
        ],
        'email' => [
            'host' => $_ENV['EMAIL_HOST'],
            'port' => $_ENV['EMAIL_PORT'],
            'senders' => [
                'default' => [
                    'account@smartgoals.tech' => 'Account Service'
                ]
            ]
        ],
        'security' => [
            'jwt_secret' => $_ENV['JWT_SECRET'],
            'jwt_alg' => $_ENV['JWT_ALG'],
            'jwt_expiration' => (int)$_ENV['JWT_EXPIRATION'],
            'server_name' => $_ENV['SERVER_NAME'],
            'password_request_token_expiration' => (int)$_ENV['PASSWORD_TOKEN_EXPIRATION'],
            'activation_token_expiration' => (int)$_ENV['ACTIVATION_TOKEN_EXPIRATION'],
            'api_token' => $_ENV['API_TOKEN']
        ]
    ];
}
