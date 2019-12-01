<?php

namespace App\Application\Actions\User;

use App\Commands\User\LoginCommand;
use Psr\Http\Message\ResponseInterface as Response;

class LoginAction extends UserActionWithEmail
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $data = $this->getPayload();
        //@todo validate input: should check for email and password
        $jwt = call_user_func(new LoginCommand($this->logger, $this->userRepository), $data);
        return $this->respondWithData(['jwt' => $jwt], 200);
    }
}