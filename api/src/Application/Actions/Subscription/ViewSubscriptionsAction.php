<?php
declare(strict_types=1);

namespace App\Application\Actions\Subscription;

use App\Application\Actions\Action;
use App\Queries\Subscription\ViewSubscriptionsQuery;
use Psr\Http\Message\ResponseInterface as Response;

class ViewSubscriptionsAction extends SubscriptionAction
{
    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $uuid = (string) $this->resolveArg('uuid');
        //validate headers userUuid against provided data userUuid
        //json schema validation
        $subscriptions = call_user_func(new ViewSubscriptionsQuery($this->logger, $this->subscriptionRepository, $this->userRepository), $uuid);
        return $this->respondWithData($subscriptions, Action::HTTP_OK);
    }
}