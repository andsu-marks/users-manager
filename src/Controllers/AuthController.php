<?php
namespace Src\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Services\AuthService;
use Src\Http\ApiResponse;

/**
 * Controller responsible for handling authentication-related operations.
 */
class AuthController {
    /**
     * Authentication service responsible for login operations.
     * 
     * @var AuthService
     */
    private AuthService $service;

    /**
     * AuthController constructor.
     * 
     * Initializes the authentication service.
     */
    public function __construct() {
        $this->service = new AuthService();
    }

    /**
     * Handles the login request.
     * 
     * Validates the incoming request data and attempts to authenticate the user using the AuthService. Returns a
     * token on success.
     * 
     * @param Request $request Incoming HTTP request.
     * @param Response $response HTTP response object.
     * 
     * @return Response JSON response containing the token or an error message.
     * 
     * @throws \Exception When authentication fails.
     */
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