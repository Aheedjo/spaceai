#!/bin/bash

# SpaceAI Server Startup Script

echo "🚀 Starting SpaceAI Server..."

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed!"
    echo ""
    echo "Please install PHP first:"
    echo ""
    echo "Using Homebrew (recommended for macOS):"
    echo "  brew install php"
    echo ""
    echo "Or download from: https://www.php.net/downloads.php"
    echo ""
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "✅ PHP $PHP_VERSION found"

# Check required extensions
echo "Checking PHP extensions..."
php -m | grep -q pdo_mysql && echo "✅ PDO MySQL extension found" || echo "⚠️  PDO MySQL extension not found"
php -m | grep -q curl && echo "✅ cURL extension found" || echo "⚠️  cURL extension not found"

# Start PHP server (public/ is document root)
echo ""
echo "🌐 Starting server on http://localhost:3000"
echo "Press Ctrl+C to stop"
echo ""

php -S localhost:3000 -t public

