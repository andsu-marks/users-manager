<?php
namespace Src\Services;

use Src\Repositories\UsersRepository;
use Src\Models\User;

/**
 * Service responsible for user-related business logic.
 * 
 * Handles operations such as creating, reading, updating, deleting users, and managing passwords. Delegates database
 * operations to UsersRepository.
 */
class UsersService {
    /**
     * Repository instance for user data operations.
     * 
     * @var UsersRepository
     */
    private Usersrepository $repository;

    /**
     * UsersService constructor.
     * 
     * Initialize the UsersRepository instance.
     */
    public function __construct() {
        $this->repository = new UsersRepository;
    }

    /**
     * Retrieves a paginated list of users along with pagination info.
     * 
     * @param int $page Current page number (1-based)
     * @param int $perPage Number of user per page
     * 
     * @return array {
     *      current_page: int,
     *      per_page: int,
     *      total_records: int,
     *      total_pages: int,
     *      data: User[]
     * }
     */
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
            'users' => $users
        ];
    }

    /**
     * Creates a new user.
     * 
     * @param string $name User name
     * @param string $email User email
     * @param string @password User password (plain text)
     * 
     * @return User Newly created User object
     * 
     * @throws \Exception If the e-mail is already registered
     */
    public function createUser(string $name, string $email, string $password): User {
        $existingUser = $this->repository->getByEmail($email);
        if ($existingUser) throw new \Exception('E-mail already registered!', 409); 

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $user = new User(null, $name, $email, $hashedPassword);
        return $this->repository->create($user);
    }

    /**
     * Retrieves a user by ID.
     * 
     * @param int $id User ID
     * 
     * @return User Usre object.
     * 
     * @throws \Exception If the user is not found
     */
    public function getUserById(int $id): ?User {
        $user = $this->repository->getById($id);
        if (!$user) throw new \Exception('User not found :/', 404);
        return $user;
    }

    /**
     * Updates user information.
     * 
     * @param int $id User ID
     * @param string $name New name (optional)
     * @param string $email New email (optional)
     * 
     * @return User Updated User object
     * 
     * @throws \Exception If the user is not found or email is in use
     */
    public function updateUser(int $id, string $name, string $email): User {
        $user = $this->repository->getById($id);
        if (!$user) throw new \Exception('User not found :(', 404);

        if ($email !== '') {
            $existingUser = $this->repository->getByEmail($email);
            if ($existingUser && $existingUser->getId() !== $id) throw new \Exception('E-mail already in use!', 409);
            $user->setEmail($email);
        }

        if ($name !== '') $user->setName($name);

        return $this->repository->update($user);
    }

    /**
     * Deletes a user by ID.
     * 
     * @param int $id User ID
     * 
     * @throws \Exception If the user is not found
     */
    public function deleteUser(int $id): void {
        $user = $this->repository->getById($id);
        if (!$user) throw new \Exception('User not found :3', 404);
        $this->repository->delete($id);
    }

    /**
     * Retrieves a user by email.
     * 
     * @param string $email User email.
     * 
     * @return User User object
     * 
     * @throws \Exception If the user is not found
     */
    public function getUserByEmail(string $email): ?User {
        $user = $this->repository->getByEmail($email);
        if (!$user) throw new \Exception('User not found :/', 404);
        return $user;
    }

    /**
     * Updates the password for a user.
     * 
     * @param int $id User ID
     * @param string $oldPassword Current password (plain text)
     * @param string $newPassword New password (plain text)
     * 
     * @throws \Exception If the user is not found or old password is incorrect
     */
    public function updatePassword(int $id, string $oldPassword, string $newPassword): void {
        $user = $this->repository->getById($id);

        if (!$user) throw new \Exception('User not found :/', 404);
        if (!password_verify($oldPassword, $user->getPassword())) {
            throw new \Exception('Old password is incorrect!', 403);
        }
    
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->setPassword($hashedPassword);
        $this->repository->updatePassword($user);
    }
}