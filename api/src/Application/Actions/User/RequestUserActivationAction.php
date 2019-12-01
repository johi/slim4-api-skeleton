<?php
declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Commands\User\RequestUserActivationCommand;
use Psr\Http\Message\ResponseInterface as Response;

class RequestUserActivationAction extends UserActionWithEmail
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $data = $this->getPayload();
        $user = call_user_func(new RequestUserActivationCommand($this->logger, $this->userRepository, $this->emailService), $data);
        return $this->respondWithData(['success' => 'ok', 'message' => 'email sent'], 200);
    }
}