<?php
declare(strict_types=1);

namespace App\Application\Actions\Exception;

use App\Application\Actions\Action;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpSpecializedException;
use Throwable;

class HttpBadRequestException extends HttpSpecializedException
{

    protected $code = Action::HTTP_BAD_REQUEST;
    protected $message = 'Bad request.';
    protected $title = '400 Bad Request';
    protected $description = 'The server cannot or will not process the request due to an apparent client error.';
    protected $data = null;

    /**
     * @param ServerRequestInterface $request
     * @param string|null            $message
     * @param Throwable|null         $previous
     */
    public function __construct(ServerRequestInterface $request, ?string $message = null, $data = null, ?Throwable $previous = null)
    {
        if ($message !== null) {
            $this->message = $message;
        }

        if ($data !== null) {
            $this->description = 'Input validation failed';
            $this->data = $data;
        }
        parent::__construct($request, $this->message, $previous);
    }

    public function getData()
    {
        return $this->data;
    }
}