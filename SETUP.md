# SpaceAI Setup Guide

## Prerequisites

1. **PHP 7.4+** with the following extensions:
   - PDO
   - PDO_MySQL
   - cURL
   - JSON

2. **MySQL/MariaDB** database

3. **Web Server** (Apache/Nginx) or PHP built-in server

4. **Google Gemini API Key** (Get one free at https://makersuite.google.com/app/apikey)

## Installation Steps

### 1. Database Setup

Create the database and tables:

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

Or import the `spaceai.sql` file if available.

### 2. Configuration

Edit `config.php` and set your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'spaceai');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### 3. API Key Setup

Get your free Gemini API key from: https://makersuite.google.com/app/apikey

**Option A: Edit config.php directly**
```php
define('GEMINI_API_KEY', 'your-actual-api-key-here');
// Optional: Choose model
define('GEMINI_MODEL', 'gemini-1.5-flash'); // or 'gemini-1.5-pro'
```

**Option B: Create config.local.php (Recommended for production)**
```php
<?php
define('GEMINI_API_KEY', 'your-actual-api-key-here');
define('GEMINI_MODEL', 'gemini-1.5-flash');
```

The `config.local.php` file is already in `.gitignore` and won't be committed to version control.

### 4. Run the Application

**Using PHP Built-in Server:**
```bash
php -S localhost:8000
```

Then open http://localhost:8000 in your browser.

**Using Apache/Nginx:**
- Point your web server document root to this directory
- Ensure mod_rewrite is enabled (if needed)
- Access via your configured domain/port

## Testing

1. Register a new account at `/register.html`
2. Login at `/login.html`
3. Start chatting at `/index.html`

## API Endpoints

- `POST /register.php` - Register new user
- `POST /login.php` - Login user
- `POST /spaceai.php` - Send message to AI

All endpoints expect JSON and return JSON responses.

## Troubleshooting

### Database Connection Error
- Check database credentials in `config.php`
- Ensure MySQL service is running
- Verify database exists

### API Key Not Working
- Verify Gemini API key is correct
- Check that you've enabled the Gemini API in Google Cloud Console
- Review error logs for detailed messages
- Make sure you're using the correct API endpoint format

### Session Issues
- Ensure PHP sessions are enabled
- Check file permissions on session directory
- Verify cookies are enabled in browser

## Security Notes

- Never commit `config.local.php` to version control
- Use strong passwords for database
- Keep API keys secure
- Consider using environment variables in production
- Enable HTTPS in production

