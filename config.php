<?php
/**
 * Configuration file for SpaceAI
 * IMPORTANT: Create a config.local.php file with your actual credentials
 * and add it to .gitignore
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'spaceai');
define('DB_USER', 'root');
define('DB_PASS', '');

// Google Gemini API Configuration
// Set your Gemini API key here or in config.local.php
// Get your API key from: https://makersuite.google.com/app/apikey
define('GEMINI_API_KEY', 'AIzaSyCmjWwArS5vBHGcRJ1-1C5xtwlHyYT6Ypk');

// Gemini Model Selection
// Run list-models.php to see available models for your API key
// Model names must include 'models/' prefix
// Good options: 'models/gemini-2.5-flash', 'models/gemini-flash-latest', 'models/gemini-pro-latest'
define('GEMINI_MODEL', 'models/gemini-2.5-flash');

// Security Settings
define('SESSION_LIFETIME', 3600); // 1 hour

// Load local config if it exists (for production)
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}
?>

