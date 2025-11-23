<?php
namespace Src\Repositories;

use PDO;
use Src\Database;
use Src\Models\User;

/**
 * Repository class responsible for CRUD operations on users.
 * 
 * Handles all database interactions for User model, including fetching, creating, deleting and counting records.
 */
class UsersRepository {
    /**
     * PDO database connection instance.
     * 
     * @var PDO
     */
    private PDO $connection;

    /**
     * UsersRepository constructor.
     * 
     * Initialize the database connection via the Database class
     */
    public function __construct() {
        $this->connection = Database::getConnection();
    }

    /**
     * Retrieves a paginated list of users.
     * 
     * @param int $page Page number (1-based)
     * @param int $perPage Number of users per page
     * 
     * @return User[] Array of User objects
     */
    public function getAll(int $page, int $perPage): array {
        $limit = $perPage;
        $offset = ($page - 1) * $perPage;

        $sql = 'SELECT * FROM users ORDER BY id DESC LIMIT :limit::INT OFFSET :offset::INT';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':limit' => $limit, ':offset' => $offset]);
        $users = [];

        while($row = $stmt->fetch()) {
            $users[] = new User(
                $row['id'],
                $row['name'],
                $row['email'],
                $row['password'],
                $row['created_at'],
                $row['updated_at']
            );
        }

        return $users;
    }

    /**
     * Creates a new user record in the database.
     * 
     * @param User $user User object with name, email and password.
     * 
     * @return User the newly created User object with ID and timestamps.
     */
    public function create(User $user): User {
        $sql = 'INSERT INTO users (name, email, password)
            VALUES (:name, :email)
            RETURNING id, created_at, updated_at
        ';
        $stmt = $this->connection->prepare($sql);

        $stmt->execute([
            ':name' => $user->getName(),
            ':email' => $user->getEmail(),
            ':password' => $user->getPassword()
        ]);

        $row = $stmt->fetch();
        return new User(
            $row['id'],
            $row['name'],
            $row['email'],
            $row['created_at'],
            $row['updated_at']
        );
    }

    /**
     * Retrieves a user by e-mail.
     * 
     * @param string $email User's email
     * 
     * @return User|null User object if found, null otherwise
     */
    public function getByEmail(string $email): ?USer {
        $sql = 'SELECT * FROM users WHERE email = :email LIMIT 1';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        if (!$row) return null;
        return new User(
            $row['id'],
            $row['name'],
            $row['email'],
            $row['password'],
            $row['created_at'],
            $row['updated_at']
        );
    }

    /**
     * Retrieves a user by ID.
     * 
     * @param int $id User ID.
     * 
     * @return User|null User object if found, null otherwise.
     */
    public function getById(int $id): ?User {
        $sql = 'SELECT * FROM users WHERE id = :id LIMIT 1';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row) return null;
        return new User(
            $row['id'],
            $row['name'],
            $row['email'],
            $row['password'],
            $row['created_at'],
            $row['updated_at']
        );
    }

    /**
     * Updates an existing user.
     * 
     * @param User $user object with updated fields.
     * 
     * @return User Updated User object.
     */
    public function update(User $user): User {
        $fields = [];
        $params = [':id' => $user->getId()];

        if ($user->getName() !== '') {
            $fields[] = "name = :name";
            $params[':name'] = $user->getName();
        }

        if ($user->getEmail() !== '') {
            $fields[] = "email = :email";
            $params[':email'] = $user->getEmail();
        }

        $fields[] = "updated_at = NOW()";

        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id
            RETURNING id, name, email, password, created_at, updated_at
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return new User(
            $row['id'],
            $row['name'],
            $row['email'],
            $row['password'],
            $row['created_at'],
            $row['updated_at']
        );
    }

    /**
     * Deletes a user by ID
     * 
     * @param int $id User ID
     */
    public function delete(int $id): void {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    /**
     * Counts the total number of users.
     * 
     * @return int Total user count.
     */
    public function countAll(): int {
        $sql = "SELECT COUNT(*) FROM users";
        $stmt = $this->connection->query($sql);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Updates the password of a user.
     */
    public function updatePassword(User $user): void {
        $sql = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':password' => $user->getPassword(), ':id' => $user->getId()]);
    }
}