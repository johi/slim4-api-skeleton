<?php
declare(strict_types=1);

use App\Infrastructure\Database\PdoDatabaseService;
use App\Infrastructure\Persistence\User\PdoUserRepository;
use App\Infrastructure\Persistence\User\UserRepository;
use App\Infrastructure\Token\TokenService;
use DI\ContainerBuilder;
use App\Infrastructure\Persistence\Subscription\PdoSubscriptionRepository;
use App\Infrastructure\Persistence\Subscription\SubscriptionRepository;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        UserRepository::class => function(ContainerInterface $c) {
            return new PdoUserRepository($c->get(PdoDatabaseService::class), $c->get(TokenService::class));
        },
        SubscriptionRepository::class => function(ContainerInterface $c) {
            return new PdoSubscriptionRepository($c->get(PdoDatabaseService::class));
        }
    ]);
};
