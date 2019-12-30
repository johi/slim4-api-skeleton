<?php

namespace App\Application\Middleware;

use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;


class AuthorizationMiddleware
{

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository) {
        $this->logger = $logger;
        $this->userRepository = $userRepository;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        if (!isset($request->getHeader('authorization')[0])) {
            throw new HttpBadRequestException($request, 'Missing authorization header');
        }
        $jwt = sscanf($request->getHeader('authorization')[0], 'Bearer %s');
        if (!isset($request->getHeader('useruuid')[0])) {
            throw new HttpBadRequestException($request, 'Missing useruuid header');
        }
        $userUuid = $request->getHeader('useruuid')[0];
        if (!$this->userRepository->verifyJwtToken($jwt[0], $userUuid)) {
            throw new HttpUnauthorizedException($request, 'The provided token is invalid');
        }
        return $response;
    }
}