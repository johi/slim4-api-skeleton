<?php
declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Commands\User\RegisterUserCommand;
use Psr\Http\Message\ResponseInterface as Response;

class RegisterUserAction extends UserActionWithEmail
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $data = $this->getPayload();
        //@todo validate input: should check for name (min, max),
        // email (is valid email [DNS?]),
        // password (regex, min, max)
        //password and password_confirmation identical
        $user = call_user_func(
            new RegisterUserCommand($this->logger, $this->userRepository, $this->emailService),
            $data
        );
        return $this->respondWithData($user, 201);
    }
}