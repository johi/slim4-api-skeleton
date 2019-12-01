<?php

namespace App\Application\Actions\User;

use App\Commands\User\LogoutCommand;
use Psr\Http\Message\ResponseInterface as Response;

class LogoutAction extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $userUuid = $this->request->getHeader('useruuid')[0];
        $user = call_user_func(new LogoutCommand($this->logger, $this->userRepository), $userUuid);
        return $this->respondWithData(['success' => 'ok'], 200);
    }
}