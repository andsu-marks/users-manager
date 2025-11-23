<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Src\Database;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

try {
    $pdo = Database::getConnection();

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