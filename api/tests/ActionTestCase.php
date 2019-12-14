<?php

namespace Tests;

use App\Application\Handlers\HttpErrorHandler;
use App\Domain\User\PasswordReset;
use App\Domain\User\User;
use App\Domain\User\UserActivation;
use App\Infrastructure\Token\TokenService;
use Slim\Middleware\ErrorMiddleware;

abstract class ActionTestCase extends TestCase
{
    const USER_NAME = 'bill.gates';
    const USER_EMAIL = 'bill@example.com';
    const USER_PASSWORD = 'abcdefg';
    const CREATED_TIMESTAMP = '2019-10-05 08:00:00';
    const UPDATED_TIMESTAMP = '2019-10-05 10:00:00';

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

    public function getToken()
    {
        return $this->container->get(TokenService::class)->generateToken();
    }

    public function getUser()
    {
        return new User(
            self::UUID_UNDER_TEST,
            self::USER_NAME,
            self::USER_EMAIL,
            self::USER_PASSWORD,
            null,
            self::CREATED_TIMESTAMP,
            null
        );
    }

    public function getUserActivation()
    {
        return new UserActivation(
            self::UUID_UNDER_TEST,
            self::UUID_UNDER_TEST,
            $this->getToken(),
            self::CREATED_TIMESTAMP,
            true,
            self::UPDATED_TIMESTAMP
        );
    }

    public function getPasswordReset()
    {
        return new PasswordReset(
            self::UUID_UNDER_TEST,
            self::UUID_UNDER_TEST,
            $this->getToken(),
            self::CREATED_TIMESTAMP,
            true,
            self::UPDATED_TIMESTAMP
        );
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