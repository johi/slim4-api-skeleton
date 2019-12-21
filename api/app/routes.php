<?php
declare(strict_types=1);

use App\Application\Actions\User\ConfirmPasswordResetAction;
use App\Application\Actions\User\ConfirmUserActivationAction;
use App\Application\Actions\User\LoginAction;
use App\Application\Actions\User\LogoutAction;
use App\Application\Actions\User\PasswordResetAction;
use App\Application\Actions\User\RegisterUserAction;
use App\Application\Actions\User\RequestPasswordResetAction;
use App\Application\Actions\User\RequestUserActivationAction;
use App\Application\Actions\User\ViewUserAction;
use App\Application\Middleware\AuthorizationMiddleware;
use App\Infrastructure\Persistence\User\UserRepository;
use Application\Actions\Subscription\CreateSubscriptionsAction;
use Application\Actions\Subscription\ListSubscriptionsTopics;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group) use ($container) {
        $group->post('/register', RegisterUserAction::class);
        $group->get('/activate/{token}', ConfirmUserActivationAction::class);
        $group->post('/login', LoginAction::class);
        $group->post('/password', RequestPasswordResetAction::class);
        $group->get('/password/{token}', ConfirmPasswordResetAction::class);
        $group->put('/password', PasswordResetAction::class);
        $group->post('/activation', RequestUserActivationAction::class);
    });

    $app->group('/users', function (Group $group) use ($container) {
        // $group->get('', ListUserAction::class); //this is not a current requirement
        $group->get('/{uuid:[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}}', ViewUserAction::class);
        $group->get('/logout', LogoutAction::class);
    })->add(new AuthorizationMiddleware($container->get(LoggerInterface::class), $container->get(UserRepository::class)));

    $app->group('/subscriptions', function (Group $group) use ($container) {
       $group->get('/topics', ListSubscriptionsTopics::class);
       $group->post('/', CreateSubscriptionsAction::class); //->create user if neccesary and corresponding subscriptions, send email confirming user and subscriptions, -> update
       $group->get('/confirm/{token}', ConfirmCreateSubscriptions::class);
       // $group->delete('/', RemoveSubscriptionsAction::class);
       // $group->get('/remove/{token}', ConfirmRemoveSubscriptionsAction::class);
    });

    $app->group('/subscriptions', function (Group $group) use ($container) {
        // $group->put('/', UpdateSubscriptionsAction::class);
        // $group->get('/', ViewSubscriptionsAction::class);
    })->add(new AuthorizationMiddleware($container->get(LoggerInterface::class), $container->get(UserRepository::class)));
};
