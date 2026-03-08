<?php
/**
 * Database connection – SQLite (default) or MySQL
 */
require_once __DIR__ . '/../../config.php';

$conn = null;
$db_available = false;

if (defined('USE_SQLITE') && USE_SQLITE) {
    $path = defined('SQLITE_PATH') ? SQLITE_PATH : (__DIR__ . '/../../data/spaceai.sqlite');
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    try {
        $conn = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $conn->exec('PRAGMA foreign_keys = ON');
        $db_available = true;
        // Create tables if not exist
        $conn->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS conversations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL DEFAULT 'New chat',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                conversation_id INTEGER,
                role TEXT NOT NULL,
                message TEXT NOT NULL,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
        try {
            $conn->exec("ALTER TABLE messages ADD COLUMN conversation_id INTEGER");
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'duplicate column') === false) {
                error_log("Migration conversation_id: " . $e->getMessage());
            }
        }
    } catch (PDOException $e) {
        error_log("SQLite connection failed: " . $e->getMessage());
        $conn = null;
        $db_available = false;
    }
} else {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        $db_available = true;
    } catch (PDOException $e) {
        error_log("MySQL connection failed: " . $e->getMessage());
        $conn = null;
        $db_available = false;
    }
}
?>
