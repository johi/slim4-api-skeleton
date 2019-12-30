<?php
declare(strict_types=1);

namespace App\Application\Actions\Subscription;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use App\Queries\Subscription\ListSubscriptionTopicsQuery;

class ListSubscriptionTopicsAction extends SubscriptionAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        $this->logger->debug(
            'WTF'
        );
        $subscriptionTopics = call_user_func(new ListSubscriptionTopicsQuery($this->logger, $this->userRepository, $this->subscriptionRepository), []);
        return $this->respondWithData($subscriptionTopics, Action::HTTP_OK);
    }
}