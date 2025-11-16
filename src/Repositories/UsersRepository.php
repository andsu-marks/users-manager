<?php
namespace Src\Repositories;

use Src\Database;
use Src\Models\User;
use PDO;
use Exception;

class UsersRepository {
    private PDO $connection;

    public function __construct() {
        $this->connection = Database::getConnection();
    }

    public function getAll(int $page, int $perPage): array {
        $limit = $perPage;
        $offset = ($page - 1) * $perPage;

        $sql = 'SELECT * FROM users ORDER BY id DESC LIMIT :limit OFFSET :offset';
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

    public function create(User $user): User {
        $sql = 'INSERT INTO users (name, email, password)
            VALUES (:name, :email, :password)
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
            $user->getName(),
            $user->getEmail(),
            $user->getPassword(),
            $row['created_at'],
            $row['updated_at']
        );
    }

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

    public function delete(int $id): void {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) throw new Exception('Failed to delete user!');
    }
}