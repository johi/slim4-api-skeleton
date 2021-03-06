<?php
declare(strict_types=1);

namespace App\Commands\User;

use App\Application\Configuration\AppConfiguration;
use App\Commands\Command;
use App\Domain\Exception\DomainRecordNotFoundException;
use App\Domain\Exception\DomainRecordUpdateException;
use App\Domain\User\User;
use App\Infrastructure\Email\EmailService;
use App\Infrastructure\Email\SimpleEmailMessage;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

class RequestUserActivationCommand extends Command
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

    public function run($data): User
    {
        //@todo validate
        $user = $this->userRepository->findUserOfEmail($data['email']);
        if (is_null($user)) {
            throw new DomainRecordNotFoundException(sprintf('User of email: %s not found for RequestUserActivationCommand', $data['email']));
        }
        if (!is_null($user->getVerified())) {
            throw new DomainRecordUpdateException(sprintf('The user of uuid: %s has already been activated previously for RequestUserActivationCommand', $user->getUuid()));
        }
        //@todo if already activated do not create a new one!, add test
        $userActivation = $this->userRepository->createUserActivation($user);
        $configuration = AppConfiguration::getAll();
        $this->emailService->send(
            new SimpleEmailMessage('confirm.html', [
                'name' => $user->getName(),
                'url' => AppConfiguration::getBaseUrl() . $configuration['paths']['activation'],
                'token' => $userActivation->getToken()
            ],
                'Please confirm your email',
                [$user->getEmail() => $user->getName()]
            )
        );
        $uuid = $user->getUuid();
        $this->logger->info("UserActivation for uuid `${uuid}` was requested.");
        return $user;
    }
}