<?php
namespace Src\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Services\UsersService;
use Src\Http\ApiResponse;

class UsersController {
    private UsersService $service;

    public function __construct() {
        $this->service = new UsersService;
    }

    public function getAll(Request $request, Response $response): Response {
        $query = $request->getQueryParams();
        $page = isset($query['page']) ? (int)$query['page'] : 1;
        $perPage = isset($query['per_page']) ? (int)$query['per_page'] : 10;

        try {
            $users = $this->service->getAllUsers($page, $perPage);
            $links = [];

            if ($users['current_page'] > 1) {
                $links['prev'] = '/users?page=' . ($users['current_page'] - 1) . '&per_page=' . $perPage;
            }

            if ($users['current_page'] < $users['total_pages']) {
                $links['next'] = '/users?page=' . ($users['current_page'] + 1) . '&per_page=' . $perPage;
            }

            $users['links'] = $links;
            return ApiResponse::success($response, $users, 200);
        } catch (\Exception $error) {
            return ApiResponse::error($response, $error->getMessage(), 400);
        }
    }

    public function create(Request $request, Response $response): Response {
        $body = $request->getParsedBody();
        $name = $body['name'] ?? '';
        $email = $body['email'] ?? '';
        $password = $body['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            return ApiResponse::error($response, 'Name, e-mail and password are required!', 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ApiResponse::error($response, 'Invalid e-mail format!', 400);
        }

        try {
            $user = $this->service->createUser($name, $email, $password);
            return ApiResponse::success($response, $user->jsonSerialize(), 201);
        } catch (\Exception $error) {
            return ApiResponse::error($response, $error->getMessage(), 409);
        }
    }

    public function getById(Request $request, Response $response, array $args): Response {
        $id = (int)($args['id'] ?? 0);
        if ($id <= 0) return ApiResponse::error($response, 'Invalid ID!', 400);

        try {
            $user = $this->service->getUserById($id);
            return ApiResponse::success($response, $user->jsonSerialize(), 200);
        } catch (\Exception $error) {
            return ApiResponse::error($response, $error->getMessage(), $error->getCode() ?: 400);
        }
    }

    public function update(Request $request, Response $response, array $args): Response {
        $id = (int)($args['id'] ?? 0);
        $body = $request->getParsedBody();
        $name = $body['name'] ?? '';
        $email = $body['email'] ?? '';

        if ($id <= 0 || ($name === '' && $email === '')) return ApiResponse::error($response, 'Invalid data!', 400);

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ApiResponse::error($response, 'Invalid e-mail format!', 400);
        }

        try {
            $updatedUser = $this->service->updateUser($id, $name, $email);
            return ApiResponse::success($response, $updatedUser->jsonSerialize(), 200);
        } catch (\Exception $error) {
            return ApiResponse::error($response, $error->getMessage(), $error->getCode() ?: 400);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response {
        $id = (int)($args['id'] ?? 0);
        if ($id <= 0) return ApiResponse::error($response, 'Invalid ID!', 400);

        try {
            $this->service->deleteUser($id);
            return $response->withStatus(204);
        } catch (\Exception $error) {
            return ApiResponse::error($response, $error->getMessage(), 400);
        }
    }

    public function getByEmail(Request $request, Response $response): Response {
        $email = $request->getQueryParams()['email'] ?? '';
        if ($email === '') return ApiResponse::error($response, 'Invalid e-mail!', 400);

        try {
            $user = $this->service->getUserByEmail($email);
            return ApiResponse::success($response, $user->jsonSerialize(), 200);
        } catch (\Exception $error) {
            return ApiResponse::error($response, $error->getMessage(), $error->getCode() ?: 400);
        }
    }

    public function updatePassword(Request $request, Response $response, array $args): Response {
        $id = (int)($args['id'] ?? 0);
        $body = $request->getParsedBody();

        $oldPassword = $body['old_password'] ?? '';
        $newPassword = $body['new_password'] ?? '';

        if ($id <= 0 || empty($oldPassword) || empty($newPassword)) {
            return ApiResponse::error($response, 'Invalid data!', 400);
        }

        try {
            $this->service->updatePassword($id, $oldPassword, $newPassword);
            return $response->withStatus(204);
        } catch (\Exception $error) {
            return ApiResponse::error($response, $error->getMessage(), $error->getCode() ?: 400);
        }
    }
}