<?php
declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Commands\User\ConfirmUserActivationCommand;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ConfirmUserActivationAction extends UserAction
{
    public function __construct(
        LoggerInterface $logger,
        UserRepository $userRepository
    )
    {
        parent::__construct($logger, $userRepository);
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $token = (string) $this->resolveArg('token');
        $user = call_user_func(new ConfirmUserActivationCommand(
            $this->logger,
            $this->userRepository
        ), $token);
        return $this->respondWithData($user, 200);
    }
}