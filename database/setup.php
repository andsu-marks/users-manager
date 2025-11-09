<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

try {
    $pdo = new PDO(
        'pgsql:host=' . $_ENV['DB_HOST'] . ';port=' . $_ENV['DB_PORT'] . ';dbname=' . $_ENV['DB_NAME'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sqlFiles = glob(__DIR__ . '/*.sql');

    foreach($sqlFiles as $file) {
        echo 'Executing: ' . basename($file) . PHP_EOL;
        $sql = file_get_contents($file);
        $pdo->exec($sql);
    }

    echo '✅ Database iniciated successful! \n';
} catch (PDOException $error) {
    die('❌ Error: ' . $error->getMessage() . '\n');
}