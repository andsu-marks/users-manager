<?php
namespace Src\Services;

use Src\Repositories\UsersRepository;

class UsersService {
    private Usersrepository $repository;

    public function __construct() {
        $this->repository = new UsersRepository;
    }

    public function getAllUsers(): array {
        return $this->repository->getAll();
    }
}