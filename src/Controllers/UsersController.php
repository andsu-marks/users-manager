<?php
namespace Src\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Services\UsersService;

class UsersController {
    private UsersService $service;

    public function __construct() {
        $this->service = new UsersService;
    }

    public function getAll(Request $request, Response $response): Response {
        $users = $this->service->getAllUsers();
        $data = array_map(fn($user) => $user->toArray(), $users);

        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}