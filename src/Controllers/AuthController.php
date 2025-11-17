<?php
namespace Src\Controllers;

use Src\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController {
    private AuthService $service;

    public function __construct() {
        $this->service = new AuthService();
    }

    public function login(Request $request, Response $response): Response {
        $body = $request->getParsedBody();
        $email = $body['email'] ?? '';
        $password = $body['password'] ?? '';

        if (empty($email) || empty($password)) {
            $response->getBody()->write(json_encode(['error' => 'Email and password are required!']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $token = $this->service->login($email, $password);
            $response->getBody()->write(json_encode(['token' => $token], JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $error) {
            $response->getBody()->write(json_encode(['error' => $error->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401); 
        }
    }
}