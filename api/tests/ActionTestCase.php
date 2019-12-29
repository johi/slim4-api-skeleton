<?php

namespace Tests;

use App\Application\Handlers\HttpErrorHandler;
use Slim\Middleware\ErrorMiddleware;

abstract class ActionTestCase extends TestCase
{

    protected $app;
    protected $container;

    public function setUp()
    {
        $this->app = $this->getAppInstance();
        $this->container = $container = $this->app->getContainer();
        $callableResolver = $this->app->getCallableResolver();
        $responseFactory = $this->app->getResponseFactory();
        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = new ErrorMiddleware($callableResolver, $responseFactory, true, false ,false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);
        $this->app->add($errorMiddleware);
    }

    public function makeRequest(string $method, string $path, array $payload = [], int &$httpCode = null): string
    {
        $request = $this->createRequest($method, $path, [
            'HTTP_ACCEPT' => 'application/json',
            'Authorization' => 'Bearer ' . self::JWT_TOKEN_UNDER_TEST,
            'useruuid' => self::UUID_UNDER_TEST
        ], $payload);
        $response = $this->app->handle($request);
        if (!is_null($httpCode)) {
            $httpCode = $response->getStatusCode();
        }
        return (string) $response->getBody();
    }
}