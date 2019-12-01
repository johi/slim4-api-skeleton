<?php
declare(strict_types=1);

namespace Tests;

use App\Infrastructure\Persistence\User\UserRepository;
use DI\ContainerBuilder;
use DI\Container;
use Exception;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;

class TestCase extends PHPUnit_TestCase
{
    const JWT_TOKEN_UNDER_TEST = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1NzEwMDY1NTEsImp0aSI6IjAxZDE1ZDA0OWZkMjNkYWI2M2NjYWYyZWU1MTcyZDczIiwiaXNzIjoiYXBpLnNtYXJ0Z29hbHMudGVjaCIsImV4cCI6MTg4NjM2NjU1MSwiZGF0YSI6eyJ1dWlkIjoiMDAwMDAwMDAtMDAwMC0wMDAwLTAwMDAtMDAwMDAwMDAwMDAwIiwiZW1haWwiOiJqb2hhbi5zY2h1bHpAZ21haWwuY29tIn19.NOXsM3ac3_-bZBbwkYdBaHiiBaV4j8C2CSLQEmc6CK6d647NBGEJAY8wO2e9oC0qj5qiBxBKkmKVVN9SSaVOyQ';
    const UUID_UNDER_TEST = '00000000-0000-0000-0000-000000000002';

    /**
     * @return App
     * @throws Exception
     */
    protected function getAppInstance(): App
    {
        $container = $this->getContainer();
        // Instantiate the api
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        //an attempt to mock up the AuthorizationMiddleware call to verifyJwtToken early on
        $userRepositoryProphecy = $this->prophesize(UserRepository::class);
        $userRepositoryProphecy
            ->verifyJwtToken(self::JWT_TOKEN_UNDER_TEST, self::UUID_UNDER_TEST)
            ->willReturn(true);
        $container->set(UserRepository::class, $userRepositoryProphecy->reveal());
        // Register middleware
        $middleware = require __DIR__ . '/../app/middleware.php';
        $middleware($app);

        // Register routes
        $routes = require __DIR__ . '/../app/routes.php';
        $routes($app);

        return $app;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $headers
     * @param array|null $payload
     * @param array $serverParams
     * @param array $cookies
     * @return Request
     */
    protected function createRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $payload = null,
        array $serverParams = [],
        array $cookies = []
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);
        if (!is_null($payload)) {
            $stream = (new StreamFactory())->createStream(json_encode($payload));
        }

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        $request = new SlimRequest($method, $uri, $h, $serverParams, $cookies, $stream);
        $request->withParsedBody($payload);
        return $request;
    }

    /**
     * @return Container
     * @throws Exception
     */
    protected function getContainer(): Container
    {
        // Instantiate PHP-DI ContainerBuilder
        $containerBuilder = new ContainerBuilder();

        // Container intentionally not compiled for tests.

        // Set up settings
        $settings = require __DIR__ . '/../app/settings.php';
        $settings($containerBuilder);

        // Set up dependencies
        $dependencies = require __DIR__ . '/../app/dependencies.php';
        $dependencies($containerBuilder);

        // Set up repositories
        $repositories = require __DIR__ . '/../app/repositories.php';
        $repositories($containerBuilder);

        // Build PHP-DI Container instance
        $container = $containerBuilder->build();
        return $container;
    }
}
