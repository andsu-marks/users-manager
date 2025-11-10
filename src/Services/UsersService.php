<?php
namespace Src\Services;

use Src\Repositories\UsersRepository;
use Src\Models\User;

class UsersService {
    private Usersrepository $repository;

    public function __construct() {
        $this->repository = new UsersRepository;
    }

    public function getAllUsers(): array {
        return $this->repository->getAll();
    }

    public function createUser(string $name, string $email, string $password): User {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $user = new User(null, $name, $email, $hashedPassword);
        return $this->repository->create($user);
    }
}