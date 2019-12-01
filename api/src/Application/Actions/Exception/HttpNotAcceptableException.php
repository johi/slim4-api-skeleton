<?php
declare(strict_types=1);

namespace App\Application\Actions\Exception;

use Slim\Exception\HttpSpecializedException;

class HttpNotAcceptableException extends HttpSpecializedException
{
    protected $code = 406;
    protected $message = 'Not acceptable';
    protected $title = '406 Not acceptable';
    protected $description = 'The server could not process the request as preconditions are not met';
}