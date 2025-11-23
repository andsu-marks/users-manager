<?php
namespace Src;

use PDO;
use PDOException;

/**
 * Database connection manager.
 * 
 * Provides a singleton PDO connection instance for the application. Supports PostgreSQL database connection using
 * environment variables.
 */
class Database {
    /**
     * Singleton PDO connection instance.
     * 
     * @var PDO|null
     */
    private static ?PDO $connection = null;

    /**
     * Returns a PDO connection instance.
     * 
     * If a connection has already been estabilished, it returns the existing one. Otherwise, it creates a new
     * connection using environment variables.
     * 
     * @return PDO PDO connection instance.
     */
    public static function getConnection(): PDO {
        if (self::$connection === null) {
            $dsn = 'pgsql:host=' . $_ENV['DB_HOST'] . ';port=' . $_ENV['DB_PORT'] . ';dbname=' . $_ENV['DB_NAME'];

            try {
                self::$connection = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $error) {
                die('Error connecting on the database: ' . $error->getMessage());
            }
        }

        return self::$connection;
    }
}