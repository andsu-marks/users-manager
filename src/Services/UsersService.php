<?php
namespace Src\Services;

use Src\Repositories\UsersRepository;
use Src\Models\User;
use Exception;

class UsersService {
    private Usersrepository $repository;

    public function __construct() {
        $this->repository = new UsersRepository;
    }

    public function getAllUsers(int $page, int $perPage): array {
        if ($page < 1) $page = 1;
        if ($perPage < 1) $perPage = 10;

        $totalRecords = $this->repository->countAll();
        $totalPages = ceil($totalRecords / $perPage);

        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

        $users = $this->repository->getAll($page, $perPage);
        return [
            'current_page' =>$page,
            'per_page' =>$perPage,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'data' => $users
        ];
    }

    public function createUser(string $name, string $email, string $password): User {
        $existingUser = $this->repository->getByEmail($email);
        if ($existingUser) throw new Exception('E-mail already registered!'); 

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $user = new User(null, $name, $email, $hashedPassword);
        return $this->repository->create($user);
    }

    public function getUserById(int $id): ?User {
        $user = $this->repository->getById($id);
        if (!$user) {
            throw new Exception('User not found :/');
        }
        return $user;
    }

    public function updateUser(int $id, string $name, string $email): User {
        $user = $this->repository->getById($id);
        if (!$user) throw new Exception('User not found :(');

        if ($email !== '') {
            $existingUser = $this->repository->getByEmail($email);
            if ($existingUser && $existingUser->getId() !== $id) throw new Exception('E-mail already in use!');
            $user->setEmail($email);
        }

        if ($name !== '') $user->setName($name);

        return $this->repository->update($user);
    }

    public function deleteUser(int $id): void {
        $user = $this->repository->getById($id);
        if (!$user) throw new Exception('User not found :3');
        $this->repository->delete($id);
    }

    public function getUserByEmail(string $email): ?User {
        $user = $this->repository->getByEmail($email);
        if (!$user) throw new Exception('User not found :/');
        return $user;
    }
}