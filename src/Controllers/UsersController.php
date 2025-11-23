<?php
namespace Src\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Services\UsersService;
use Src\Http\ApiResponse;

/**
 * Controller responsible for handling User-related operations.
 */
class UsersController {
    /**
     * User service responsible for User operations.
     * 
     * @var UsersService
     */
    private UsersService $service;

    /**
     * UsersController constructor.
     * 
     * Initializes the Users service.
     */
    public function __construct() {
        $this->service = new UsersService;
    }

    /**
     * Retrieves a paginated list of users.
     * 
     * Extracts pagination parameters from the query string, fetches users from the service layer, and adds
     * navitagion links (prev/next) when applicable.
     * 
     * @param Request $request incoming HTTP request.
     * @param Response $response HTTP response object.
     * 
     * @return Response JSON response containing paginated users and navigation links.
     * 
     * @throws \Exception When an unexpected error occurs while fetching data.
     */
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

    /**
     * Creates a new user.
     * 
     * Validates incoming payload fields (name, email, password), checks e-mail format, and delegates user creation
     * to the service layer. Returns the newly created user data on success.
     * 
     * @param Request $request incoming HTTP request.
     * @param Response $response HTTP response object.
     * 
     * @return Response JSON response on the created user or an error message.
     * 
     * @throws \Exception When user creation fails (e.g., duplicate e-mail).
     */
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

    /**
     * Retrieves a user by its ID.
     * 
     * Validates the provided ID from route parameters and attempts to fetch the corresponding user from the
     * service layer. Returns the user data when found.
     * 
     * @param Request $request incoming HTTP request.
     * @param Response $response HTTP response object.
     * @param array $args Route parameters (must contain 'id').
     * 
     * @return Response JSON response containing the user or an error message.
     * 
     * @throws \Exception When the user cannot be found or another error occurs.
     */
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

    /**
     * Updates an existing user.
     * 
     * Validates the user ID from route parameters and checks if at least one field (name or email) was provided.
     * Also validates e-mail format when present. Delegates the update operation to the service layer and returns
     * the updated user data.
     * 
     * @param Request $request incoming HTTP request.
     * @param Response $response HTTP response object.
     * @param array $args Route parameters (must contain 'id').
     * 
     * @return Response JSON response with the updated user or an error message.
     * 
     * @throws \Exception When the update fails or invalid data is provided.
     */
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

    /**
     * Deletes a user by irs ID.
     * 
     * Validates the provided ID from rote parameters and delegates the delete operation to the service layer.
     * Returns an empty 204 response on success.
     * 
     * @param Request $request incoming HTTP request.
     * @param Response $response HTTP response object.
     * @param array $args Route parameters (must contain 'id').
     * 
     * @return Response Empty response with status 204 or an error message.
     * 
     * @throws \Exception When the user cannot be deleted or another error occurs.
     */
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

    /**
     * Retrieves a user by e-mail address.
     * 
     * Extracts the e-mail from query parameters, validates it, and delegates the lookup to the service layer.
     * Returns the user data when found.
     * 
     * @param Request $request incoming HTTP request.
     * @param Response $response HTTP response object.
     * 
     * @return Response JSON response containing the user or an error message.
     * 
     * @throws \Exception When the user cannot be found or another error occurs.
     */
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

    /**
     * Updates the password of a user.
     * 
     * validates the user ID and requires fields ('old_password' and 'new_password') from the request body.
     * Delegates the password update to the service layer. Returns an empty 204 response on success.
     * 
     * @param Request $request incoming HTTP request.
     * @param Response $response HTTP response object.
     * @param array $args Route parameters (must contain 'id').
     * 
     * @return Response Empty response with status 204 or an error message.
     * 
     * @throws \Exception When the password cannot be updated or validation
     */
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