<?php
declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Queries\User\ConfirmPasswordResetQuery;
use Psr\Http\Message\ResponseInterface as Response;

class ConfirmPasswordResetAction extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $token = (string) $this->resolveArg('token');
        $uuid = call_user_func(new ConfirmPasswordResetQuery($this->logger, $this->userRepository), $token);
        return $this->respondWithData(['success' => 'ok'], 200);
    }
}