<?php
declare(strict_types=1);

namespace App\Application\Actions\Exception;

use App\Application\Actions\Action;
use Slim\Exception\HttpSpecializedException;

class HttpNotAcceptableException extends HttpSpecializedException
{
    protected $code = Action::HTTP_NOT_ACCEPTABLE;
    protected $message = 'Not acceptable';
    protected $title = '406 Not acceptable';
    protected $description = 'The server could not process the request as preconditions are not met';
}