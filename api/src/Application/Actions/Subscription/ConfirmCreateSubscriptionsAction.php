<?php
declare(strict_types=1);

namespace Application\Actions\Subscription;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;

class ConfirmCreateSubscriptionsAction extends SubscriptionAction
{

    /**
     * @inheritDoc
     */
    protected function action(): Response
    {
        // TODO: Implement action() method.
        return $this->respondWithData(['success' => 'ok'], Action::HTTP_OK);
    }
}