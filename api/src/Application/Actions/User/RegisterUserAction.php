<?php
declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Application\Actions\Exception\HttpBadRequestException;
use App\Application\Validation\AppValidator;
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
        $this->logger->debug(__dir__);
        $errors = AppValidator::validate($data, 'users/register.json');
        //password and password_confirmation identical
        if (!empty($errors)) {
            throw new HttpBadRequestException($this->request, 'Validation Errors', $errors);
        }

        $user = call_user_func(
            new RegisterUserCommand($this->logger, $this->userRepository, $this->emailService),
            $data
        );
        return $this->respondWithData($user, 201);
    }
}