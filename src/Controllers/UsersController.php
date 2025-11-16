<?php
namespace Src\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Services\UsersService;
use Exception;

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
            $response->getBody()->write(json_encode($users, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $error) {
            $response->getBody()->write(json_encode(['error' => $error->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function create(Request $request, Response $response): Response {
        $body = $request->getParsedBody();
        $name = $body['name'] ?? '';
        $email = $body['email'] ?? '';
        $password = $body['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            $response->getBody()->write(json_encode(['error' => 'Name, email and password ar required!']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $user = $this->service->createUser($name, $email, $password);
            $response->getBody()->write(json_encode(['success' => true, 'user' => $user], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (Exception $error) {
            $response->getBody()->write(json_encode(
                ['success' => false, 'message' => $error->getMessage()],
                JSON_UNESCAPED_UNICODE
            ));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }
    }

    public function getById(Request $request, Response $response, array $args): Response {
        $id = (int)($args['id'] ?? 0);

        if ($id <= 0) {
            $response->getBody()->write(json_encode(['error' => 'Invalid ID!']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $user = $this->service->getUserById($id);
            $response->getBody()->write(json_encode($user->jsonSerialize(), JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $error) {
            $response->getBody()->write(json_encode(['error' => $error->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function update(Request $request, Response $response, array $args): Response {
        $id = (int)($args['id'] ?? 0);
        $body = $request->getParsedBody();
        $name = $body['name'] ?? '';
        $email = $body['email'] ?? '';

        if ($id <= 0 || (empty($name) && empty($email))) {
            $response->getbody()->write(json_encode(['error' => 'Invalid data!']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $updatedUser = $this->service->updateUser($id, $name, $email);
            $response->getBody()->write(json_encode(['success' => true, 'user' => $updatedUser], JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $error) {
            $response->getBody()->write(json_encode(['error' => $error->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response {
        $id = (int)($args['id'] ?? 0);
        if ($id <= 0) {
            $response->getBody()->write(json_encode(['error' => 'Invalid ID!']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $this->service->deleteUser($id);
            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $error) {
            $response->getBody()->write(json_encode(['error' => $error->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function getByEmail(Request $request, Response $response, array $args): Response {
        $email = $request->getQueryParams()['email'] ?? '';
        if ($email === '') {
            $response->getBody()->write(json_encode(['error' => 'Invalid e-mail!']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $user = $this->service->getUserByEmail($email);
            $response->getBody()->write(json_encode($user->jsonSerialize(), JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $error) {
            $response->getBody()->write(json_encode(['error' => $error->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function updatePassword(Request $request, Response $response, array $args): Response {
        $id = (int)($args['id'] ?? 0);
        $body = $request->getParsedBody();

        $oldPassword = $body['old_password'] ?? '';
        $newPassword = $body['new_password'] ?? '';

        if ($id <= 0 || empty($oldPassword) || empty($newPassword)) {
            $response->getBody()->write(json_encode(['error' => 'Invalid data!']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $this->service->updatePassword($id, $oldPassword, $newPassword);
            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $error) {
            $response->getBody()->write(json_encode(['error' => $error->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}