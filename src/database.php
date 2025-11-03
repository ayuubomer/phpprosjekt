<?php
require __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

// ✅ Load environment variables from .env
if (!isset($_ENV['DB_DSN'])) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

/**
 * Returns a shared PDO database connection.
 * Works with MySQL or SQLite depending on .env configuration.
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    // Read credentials from environment
    $dsn  = $_ENV['DB_DSN']  ?? 'sqlite:../db/app.sqlite';
    $user = $_ENV['DB_USER'] ?? null;
    $pass = $_ENV['DB_PASS'] ?? null;

    // PDO options for safety and performance
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $opts);

        // Enable foreign keys if SQLite
        if (str_starts_with($dsn, 'sqlite:')) {
            $pdo->exec('PRAGMA foreign_keys = ON;');
        }
    } catch (PDOException $e) {
        // If connection fails, show a clear message
        die("❌ Database connection failed: " . $e->getMessage());
    }

    return $pdo;
}
