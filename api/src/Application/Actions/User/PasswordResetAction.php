<?php
declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Commands\User\PasswordResetCommand;
use Psr\Http\Message\ResponseInterface as Response;

class PasswordResetAction extends UserActionWithEmail
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $data = $this->getPayload();
        //@todo it is assumed that input passes validation criteria
        $user = call_user_func(new PasswordResetCommand($this->logger, $this->userRepository), $data);
        return $this->respondWithData($user, 200);
    }
}