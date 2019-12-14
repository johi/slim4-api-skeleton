<?php
declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Actions\Exception\HttpConflictException;
use App\Application\Actions\Exception\HttpNotAcceptableException;
use App\Domain\Exception\DomainException;
use App\Domain\Exception\DomainRecordDuplicateException;
use App\Domain\Exception\DomainRecordForbiddenException;
use App\Domain\Exception\DomainRecordInvalidException;
use App\Domain\Exception\DomainRecordNotAuthorizedException;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\Exception\DomainRecordRequestException;
use App\Domain\Exception\DomainRecordUpdateException;
use App\Domain\Exception\DomainServiceException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;

abstract class Action
{
    //the following are added for convenience in testing that response codes are actually correctly returned
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_CONFLICT = 409;
    const HTTP_INTERNAL = 500;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $args;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws HttpBadRequestException
     * @throws HttpConflictException
     * @throws HttpForbiddenException
     * @throws HttpInternalServerErrorException
     * @throws HttpMethodNotAllowedException
     * @throws HttpNotAcceptableException
     * @throws HttpNotFoundException
     * @throws HttpUnauthorizedException
     */
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action();
        } catch (DomainException $e) {
            if ($e instanceof DomainRecordRequestException) {
                // 400, used for e.g. validation errors
                throw new HttpBadRequestException($this->request, $e->getMessage());
            }
            if ($e instanceof DomainRecordNotAuthorizedException) {
                // 401, used for general access restrictions, missing/invalid api- or Bearer token, content from a context the user is not eligible for
                throw new HttpUnauthorizedException($this->request, $e->getMessage());
            }
            if ($e instanceof  DomainRecordForbiddenException) {
                // 403, user has insufficient rights, becomes relevant when several users share the same data with differing access rights
                throw new HttpForbiddenException($this->request, $e->getMessage());
            }
            if ($e instanceof DomainRecordNotFoundException) {
                // 404, the resource was not found
                throw new HttpNotFoundException($this->request, $e->getMessage());
            }
            if ($e instanceof DomainRecordInvalidException) {
                // 405, e.g. a token parameter is invalid
                throw new HttpMethodNotAllowedException($this->request, $e->getMessage());
            }
            if ($e instanceof  DomainRecordUpdateException) {
                // 406, server data are in a state where request is not applicable
                throw new HttpNotAcceptableException($this->request, $e->getMessage());
            }
            if ($e instanceof DomainRecordDuplicateException) {
                // 409, e.g. trying to create a resource that already exists
                throw new HttpConflictException($this->request, $e->getMessage());
            }
            if ($e instanceof DomainServiceException) {
                // 500, sql/database/service error or the like
                throw new HttpInternalServerErrorException($this->request, $e->getMessage());
            }
        }
    }

    /**
     * @return Response
     * @throws DomainRecordNotFoundException
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @return array
     * @throws HttpBadRequestException
     */
    protected function getPayload(): array
    {
        //I presume this is for post requests, that name is a bit misleading
        $input = json_decode($this->request->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpBadRequestException($this->request, 'Malformed JSON input.');
        }

        return $input;
    }

    /**
     * @param  string $name
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }
        return $this->args[$name];
    }

    /**
     * @param array|object|null $data
     * @param int $statusCode
     * @return Response
     */
    protected function respondWithData($data = null, $statusCode = self::HTTP_OK): Response
    {
        $payload = new ActionPayload($statusCode, $data);
        return $this->respond($payload);
    }

    /**
     * @param ActionPayload $payload
     * @return Response
     */
    protected function respond(ActionPayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);
        return $this->response->withStatus($payload->getStatusCode())
            ->withHeader('Content-Type', 'application/json');
    }
}
