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
        if ($existingUser) throw new Exception('E-mail already registered!', 409); 

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $user = new User(null, $name, $email, $hashedPassword);
        return $this->repository->create($user);
    }

    public function getUserById(int $id): ?User {
        $user = $this->repository->getById($id);
        if (!$user) throw new Exception('User not found :/', 404);
        return $user;
    }

    public function updateUser(int $id, string $name, string $email): User {
        $user = $this->repository->getById($id);
        if (!$user) throw new Exception('User not found :(', 404);

        if ($email !== '') {
            $existingUser = $this->repository->getByEmail($email);
            if ($existingUser && $existingUser->getId() !== $id) throw new Exception('E-mail already in use!', 409);
            $user->setEmail($email);
        }

        if ($name !== '') $user->setName($name);

        return $this->repository->update($user);
    }

    public function deleteUser(int $id): void {
        $user = $this->repository->getById($id);
        if (!$user) throw new Exception('User not found :3', 404);
        $this->repository->delete($id);
    }

    public function getUserByEmail(string $email): ?User {
        $user = $this->repository->getByEmail($email);
        if (!$user) throw new Exception('User not found :/', 404);
        return $user;
    }

    public function updatePassword(int $id, string $oldPassword, string $newPassword): void {
        $user = $this->repository->getById($id);

        if (!$user) throw new Exception('User not found :/', 404);
        if (!password_verify($oldPassword, $user->getPassword())) {
            throw new Exception('Old password is incorrect!', 403);
        }
    
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->setPassword($hashedPassword);
        $this->repository->updatePassword($user);
    }
}