<?php
declare(strict_types=1);

namespace App\Application\Actions\Subscription;

use App\Application\Actions\Action;
use App\Commands\Subscription\SaveSubscriptionsCommand;
use Psr\Http\Message\ResponseInterface as Response;

class SaveSubscriptionsAction extends SubscriptionAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $data = $this->getPayload();
        //validate headers userUuid against provided data userUuid
        //json schema validation
        $subscriptions = call_user_func(new SaveSubscriptionsCommand($this->logger, $this->subscriptionRepository, $this->userRepository), $data);
        return $this->respondWithData($subscriptions, Action::HTTP_OK);
    }
}