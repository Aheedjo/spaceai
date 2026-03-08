<?php
/**
 * Configuration file for SpaceAI
 * IMPORTANT: Create a config.local.php file with your actual credentials
 * and add it to .gitignore
 */

// Database: use SQLite (no MySQL needed) or set USE_SQLITE to false for MySQL
// SQLite file is stored in project root / data/ – when hosting, ensure this folder is writable by the web server
define('USE_SQLITE', true);
define('SQLITE_PATH', __DIR__ . '/data/spaceai.sqlite');

// MySQL (only if USE_SQLITE is false)
define('DB_HOST', 'localhost');
define('DB_NAME', 'spaceai');
define('DB_USER', 'root');
define('DB_PASS', '');

// Google Gemini API Configuration
// Set your Gemini API key here or in config.local.php
// Get your API key from: https://makersuite.google.com/app/apikey
define('GEMINI_API_KEY', 'AIzaSyDgBQxTu7mXDOYYgYg8K5WNC9yJS8DOXAc');

// Gemini Model Selection
// Run list-models.php to see available models for your API key
// Model names must include 'models/' prefix
// Good options: 'models/gemini-2.5-flash', 'models/gemini-flash-latest', 'models/gemini-pro-latest'
define('GEMINI_MODEL', 'models/gemini-2.5-flash');

// System instruction: tunes the model's persona (space expert)
define('GEMINI_SYSTEM_INSTRUCTION', 'You are SpaceAI, a friendly and knowledgeable space expert. You answer questions about astronomy, space exploration, cosmology, rockets, planets, stars, black holes, and the universe with accuracy and enthusiasm. Use clear, engaging language. When asked about other topics, you can help but may briefly connect the topic to space when it makes sense. Sign off as SpaceAI when it feels natural.');

// Security Settings
define('SESSION_LIFETIME', 3600); // 1 hour

// Session cookie: httpOnly, SameSite, secure when on HTTPS
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path' => '/',
    'domain' => '',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Load local config if it exists (for production)
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}
?>

