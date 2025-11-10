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
}