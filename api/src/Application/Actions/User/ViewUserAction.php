<?php
declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Queries\User\ViewUserQuery;
use Psr\Http\Message\ResponseInterface as Response;

class ViewUserAction extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        //@todo ensure user is allowed to view user of uuid
        $uuid = (string) $this->resolveArg('uuid');
        $user = call_user_func(new ViewUserQuery($this->logger, $this->userRepository), $uuid);
        return $this->respondWithData($user);
    }
}