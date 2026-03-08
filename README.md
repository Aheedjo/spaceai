# SpaceAI

A modern AI chat application with user authentication and support for multiple AI providers (OpenAI GPT and Google Gemini).

## Features

- 🔐 User authentication (registration and login)
- 💬 Real-time AI chat interface
- 🤖 Powered by Google Gemini AI
- 💾 Chat history stored in database
- 🎨 Modern, responsive UI

## Quick Start

### Prerequisites
- PHP 7.4+ with PDO, cURL, and SQLite extensions
- Google Gemini API key (Get one free at https://makersuite.google.com/app/apikey)

### Setup

1. **Database (SQLite – default)**  
   No setup needed. The app uses a SQLite file at **`data/spaceai.sqlite`** (created automatically on first run).  
   **When hosting:** ensure the `data/` directory is **writable** by the web server, e.g.:
   ```bash
   chmod 755 data
   # or, if the web server runs as a different user:
   chown www-data:www-data data   # Linux, adjust user/group to your server
   ```

2. **Configuration**
   - Edit `config.php` with your database credentials
   - Add your Gemini API key in `config.php` or create `config.local.php`
   ```php
   define('GEMINI_API_KEY', 'your-api-key-here');
   ```

3. **Install PHP** (if not already installed)

   **macOS (using Homebrew):**
   ```bash
   brew install php
   ```

   **Linux:**
   ```bash
   sudo apt-get install php php-mysql php-curl  # Ubuntu/Debian
   sudo yum install php php-mysql php-curl       # CentOS/RHEL
   ```

4. **Run the Application**

   **Option 1: Using the startup script (recommended):**
   ```bash
   ./start-server.sh
   ```

   **Option 2: Using npm:**
   ```bash
   npm start
   ```

   **Option 3: Direct PHP command:**
   ```bash
   php -S localhost:3000
   ```

   Then open http://localhost:3000 in your browser.

   **Using Apache/Nginx:**
   - Point document root to this directory
   - Access via your configured domain

## Where is the database stored?

- **SQLite (default):** `data/spaceai.sqlite` in the project root.  
  Users and chat messages are stored here. When you deploy, keep `data/` writable by PHP (see Setup above).
- **MySQL:** Optional; set `USE_SQLITE` to `false` in `config.php` and set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.

## Project Structure

```
spaceai/
├── config.php              # Config (DB, Gemini API key) – keep at root
├── config.local.php        # Optional local overrides (gitignored)
├── data/                   # SQLite DB stored here (must be writable when hosting)
│   └── spaceai.sqlite      # Created at first run; gitignored
├── public/                 # Document root
│   ├── index.html          # Main chat
│   ├── login.html
│   ├── register.html
│   ├── css/
│   │   ├── auth.css
│   │   ├── style.css
│   │   └── style2.css
│   ├── js/
│   │   └── api.js
│   ├── images/
│   │   └── space.avif
│   └── api/
│       ├── login.php
│       ├── register.php
│       ├── spaceai.php
│       ├── db.php
│       └── list-models.php
├── start-server.sh
├── package.json
└── README.md
```

## API Integration

The app uses Google Gemini API. To set it up:

1. Get your free Gemini API key from: https://makersuite.google.com/app/apikey

2. Add your API key to `config.php`:
   ```php
   define('GEMINI_API_KEY', 'your-api-key-here');
   ```

3. Optionally choose a model (default is `gemini-1.5-flash`):
   ```php
   define('GEMINI_MODEL', 'gemini-1.5-flash'); // or 'gemini-1.5-pro', 'gemini-pro'
   ```

That's it! The app is ready to use.

## Documentation

See `SETUP.md` for detailed setup instructions and troubleshooting.

## Development

For frontend development (static files only):
```bash
npm start
```
Note: This won't work for PHP endpoints. Use PHP server for full functionality.

