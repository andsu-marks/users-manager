<?php 
namespace Src\Services;

use Src\Repositories\UsersRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService {
    private UsersRepository $repository;
    private string $secret;

    public function __construct() {
        $this->repository = new UsersRepository;
        $this->secret = $_ENV['SECRET_KEY'];
    }

    public function Login(string $email, string $password): array {
        $user = $this->repository->getByEmail($email);
        if (!$user) throw new \Exception('User not found :/');
        if (!password_verify($password, $user->getPassword())) throw new \Exception('Incorrect password!');
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
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail()
            ]
        ];
    }
}