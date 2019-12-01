<?php
declare(strict_types=1);

namespace App\Application\Actions\Exception;

use Slim\Exception\HttpSpecializedException;

class HttpConflictException extends HttpSpecializedException
{
    protected $code = 409;
    protected $message = 'Conflict';
    protected $title = '409 Conflict';
    protected $description = 'The server encountered a conflict for this request';
}