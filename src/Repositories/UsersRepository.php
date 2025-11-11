<?php
namespace Src\Repositories;

use Src\Database;
use Src\Models\User;
use PDO;

class UsersRepository {
    private PDO $connection;

    public function __construct() {
        $this->connection = Database::getConnection();
    }

    public function getAll(): array {
        $sql = 'SELECT * FROM users ORDER BY id DESC';
        $stmt = $this->connection->query($sql);
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
}