# Database Setup Guide

## Current Status

The app is currently running in **demo mode** without a database. This means:
- ✅ You can chat with Gemini AI
- ✅ Login/Registration works (session-based)
- ❌ Chat history is not saved
- ❌ User accounts are not persisted

## Installing MySQL (Recommended)

### macOS using Homebrew:

```bash
# Install MySQL
brew install mysql

# Start MySQL service
brew services start mysql

# Secure installation (set root password)
mysql_secure_installation
```

### Create Database and Tables

Once MySQL is running, connect to it:

```bash
mysql -u root -p
```

Then run:

```sql
CREATE DATABASE spaceai;
USE spaceai;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('user','ai') NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Update config.php

Update your database credentials in `config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'spaceai');
define('DB_USER', 'root');
define('DB_PASS', 'your_mysql_password');
```

## Alternative: Using SQLite (Simpler, No Server Needed)

If you prefer a simpler setup without running a MySQL server, you can use SQLite. However, this requires code changes to use SQLite instead of MySQL.

## Testing Database Connection

You can test if your database is working by checking the PHP error logs or by trying to register a new user - if the database is working, the user will be saved permanently.

## Troubleshooting

### "No such file or directory" error
- MySQL server is not running
- Start it with: `brew services start mysql`

### "Access denied" error
- Check username and password in `config.php`
- Verify MySQL user has permissions

### Database doesn't exist
- Run the CREATE DATABASE command above
- Make sure the database name matches in `config.php`

