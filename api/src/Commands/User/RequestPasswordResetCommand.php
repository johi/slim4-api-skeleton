<?php
declare(strict_types=1);

namespace App\Commands\User;

use App\Application\Configuration\AppConfiguration;
use App\Commands\Command;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\Exception\DomainRecordUpdateException;
use App\Infrastructure\Email\EmailService;
use App\Infrastructure\Email\SimpleEmailMessage;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

class RequestPasswordResetCommand extends Command
{
    private $logger;
    private $userRepository;
    private $emailService;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository, EmailService $emailService)
    {
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }

    public function run($data)
    {
        $user = $this->userRepository->findUserOfEmail($data['email']);
        if (is_null($user)) {
            throw new DomainRecordNotFoundException(sprintf('User of email: %s not found for RequestPasswordResetCommand', $data['email']));
        }
        if (is_null($user->getVerified())) {
            throw new DomainRecordUpdateException(sprintf('The user of uuid: %s has not been activated yet for RequestPasswordResetCommand', $user->getUuid()));
        }
        $passwordReset = $this->userRepository->createPasswordReset($user);
        $configuration = AppConfiguration::getAll();
        $this->emailService->send(
            new SimpleEmailMessage('forgotPassword.html', [
                'name' => $user->getName(),
                'url' => AppConfiguration::getBaseUrl() . $configuration['paths']['password'],
                'token' => $passwordReset->getToken()
            ],
                'Your password reset link',
                [$user->getEmail() => $user->getName()]
            )
        );
        $uuid = $passwordReset->getUserUuid();
        $this->logger->info("Forgot password link for uuid `${uuid}` was requested.");
        return $user;
    }
}