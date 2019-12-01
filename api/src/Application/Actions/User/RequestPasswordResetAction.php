<?php

namespace App\Application\Actions\User;

use App\Commands\User\RequestPasswordResetCommand;
use Psr\Http\Message\ResponseInterface as Response;

class RequestPasswordResetAction extends UserActionWithEmail
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $data = $this->getPayload();
        //@todo remember to apply schema validation
        $user = call_user_func(new RequestPasswordResetCommand($this->logger, $this->userRepository, $this->emailService), $data);
        return $this->respondWithData(['success' => 'ok', 'message' => 'email sent'], 200);
    }
}