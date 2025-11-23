<?php
namespace Src\Controllers;

use Src\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Http\ApiResponse;

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
            return ApiResponse::error($response, 'E-mail and password are required!', 400);
        }

        try {
            $token = $this->service->login($email, $password);
            return ApiResponse::success($response, $token, 200);
        } catch (\Exception $error) {
            return ApiResponse::error($response, $error->getMessage(), $error->getCode() ?: 401);
        }
    }
}