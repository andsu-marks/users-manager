<?php 
namespace Src\Services;

use Firebase\JWT\JWT;
use Src\Repositories\UsersRepository;

/**
 * Service responsible for authentication logic.
 * 
 * Handles user login, password verification, and JWT generation.
 */
class AuthService {
    /**
     * Repository for accessing user data.
     * 
     * @var UsersRepository
     */
    private UsersRepository $repository;

    /**
     * Secret key used to sign JWT tokens.
     * 
     * @var string
     */
    private string $secret;

    /**
     * AuthService constructor.
     * 
     * Initialize the user repository and loads the JWT secret jey from environment
     */
    public function __construct() {
        $this->repository = new UsersRepository;
        $this->secret = $_ENV['SECRET_KEY'];
    }

    /**
     * Authenticates a user and returns a JWT token with user data.
     * 
     * Validates the provided email and password. If valid, generates a JWT token containing the user ID, email,
     * issued at (iat) and expiration (exp) timestamps. Throws exceptions when authentication fails.
     * 
     * @param string $email User email.
     * @param string $password User password.
     * 
     * @return array {
     *      token: string,
     *      user: User
     * } Array containing the JWT token and the authenticated User object.
     * 
     * @throws \Exception If the user is not found or the password is incorrect.
     */
    public function Login(string $email, string $password): array {
        $user = $this->repository->getByEmail($email);
        if (!$user) throw new \Exception('User not found :/', 404);
        if (!password_verify($password, $user->getPassword())) throw new \Exception('Incorrect password!', 403);
        $token = JWT::encode([
                'sub' => $user->getId(),
                'email' => $user->getEmail(),
                'iat' => time(),
                'exp' => time() + (60 * 60)
            ],
            $this->secret,
            'HS256'
        );

        return [
            'token' => $token,
            'user' => $user
        ];
    }
}