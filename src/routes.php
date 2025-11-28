<?php
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Controllers\UsersController;
use Src\Controllers\AuthController;
use Src\Middlewares\AuthMiddleware;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('The App is Running on Slim! 🚀');
        return $response;
    });
    
    $app->group('/users', function ($group) {
        $group->get('', [UsersController::class, 'getAll']);
        $group->post('', [UsersController::class, 'create']);
        $group->get('/by-email', [UsersController::class, 'getByEmail']);
        $group->get('/{id}', [UsersController::class, 'getById']);
        $group->put('/{id}', [UsersController::class, 'update']);
        $group->delete('/{id}', [UsersController::class, 'delete']);
        $group->map(['PATCH'], '/{id}', [UsersController::class, 'updatePassword']);
    })->add(new AuthMiddleware($app->getResponseFactory()));

    $app->post('/login', [AuthController::class, 'login']);
};