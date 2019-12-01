<?php
declare(strict_types=1);

use App\Infrastructure\Database\DatabaseConnectionException;
use App\Infrastructure\Database\PdoDatabaseService;
use App\Infrastructure\Email\EmailService;
use App\Infrastructure\Email\SwiftMailerEmailService;
use App\Infrastructure\Token\SimpleTokenService;
use App\Infrastructure\Token\TokenService;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpInternalServerErrorException;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        PdoDatabaseService::class => function (ContainerInterface $c) {
            try {
                return new PdoDatabaseService();
            } catch (DatabaseConnectionException $e) {
                throw new HttpInternalServerErrorException($e->getMessage());
            }
        },
        EmailService::class => function (ContainerInterface $c) {
            $emailService = new SwiftMailerEmailService();
            return $emailService;
        },
        TokenService::class => function (ContainerInterface $c) {
            $tokenService = new SimpleTokenService();
            return $tokenService;
        }
    ]);
};
