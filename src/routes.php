<?php
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Controllers\UsersController;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('The App is Running on Slim! 🚀');
        return $response;
    });

    $app->get('/users', [UsersController::class, 'getAll']);
    $app->post('/users', [UsersController::class, 'create']);
    $app->get('/users/{id}', [UsersController::class, 'getById']);
    $app->put('/users/{id}', [UsersController::class, 'update']);
    $app->delete('/users/{id}', [UsersController::class, 'delete']);
};