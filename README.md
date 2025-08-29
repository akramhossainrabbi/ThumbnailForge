# ThumbnailForge

ThumbnailForge is a Laravel application that processes image thumbnail jobs using Redis queues and provides realtime status updates via Laravel Reverb.

This README contains a concise installation and quick-start guide for local development on a Linux (Ubuntu/Debian) machine.

## Requirements

- PHP 8.4+ (or the version required by `composer.json`)
- Composer
- Node.js & npm
- MySQL (or change `DB_CONNECTION` to `sqlite` if you prefer)
- Redis (used for sessions and queues)

Redis is required

This application relies on Redis for session storage and queue processing. Make sure Redis is running and configured in your `.env` before starting queue workers or Reverb.

Example `.env` Redis settings:

```
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

Start Redis (Linux / WSL):

```bash
sudo systemctl start redis-server
sudo systemctl enable redis-server
redis-cli ping   # should return PONG
```

If you can't run Redis natively (Windows or locked-down environment), run it in Docker:

```bash
docker run -d --name redis -p 6379:6379 redis:7
```

When using Docker on Windows, set `REDIS_HOST=127.0.0.1` (or the Docker host IP) in `.env`.

Optional
- A Node.js thumbnail processing service (the repo expects `http://nodejs-service:3000/process` for background processing — see notes)

## Quick install (local)

Follow these steps from a terminal.

1) System packages (Ubuntu/Debian example)

```bash
sudo apt update
sudo apt install -y php php-cli php-mbstring php-xml php-mysql php-curl php-zip unzip
sudo apt install -y mysql-server redis-server nodejs npm
```

2) Clone the repo

```bash
cd ~/Public
git clone https://github.com/akramhossainrabbi/ThumbnailForge.git ThumbnailForge
cd ThumbnailForge
```

3) Environment and keys

```bash
cp .env.example .env
php artisan key:generate
# (optional) generate Reverb keys for REVERB_APP_KEY and REVERB_APP_SECRET
php -r "echo base64_encode(random_bytes(32)) . PHP_EOL;"
```

Open `.env` and set DB_* values, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, and any other settings you need.

4) Install dependencies and build assets

```bash
composer install --no-interaction --prefer-dist
npm install
npm run build    # production build; use `npm run dev` while developing
```

5) Database

Create the database (example for MySQL):

```sql
CREATE DATABASE laravel;
```

Then run migrations and seeders:

```bash
php artisan migrate
php artisan db:seed
```

6) Storage and filesystem

```bash
php artisan storage:link
```

7) Services: Redis, Reverb, Queue

- Ensure Redis is running:

```bash
redis-cli ping   # should return PONG
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

- Start Reverb (dev realtime server provided in project):

```bash
php artisan reverb:start
```

- Start a queue worker in another terminal to process thumbnail jobs:

```bash
php artisan queue:work --queue=high,medium,low
```

8) Serve the app

```bash
php artisan serve --host=0.0.0.0 --port=8000
# Open http://localhost:8000
```

## Testing

```bash
php artisan test
```

## Notes & troubleshooting

- Button styling and frontend assets are bundled with Vite. If you change CSS imports or Polaris versions, run `npm run build` and hard-refresh the browser to pick up changes.
- If the submit form's textarea is required, use browser validation and also ensure server-side validation (update controllers or request classes) exists for `image_urls`.
- If thumbnails are not being processed, confirm the queue worker and the optional Node.js processing service are running.
- If Reverb or realtime updates fail, ensure `php artisan reverb:start` is running and check browser console for WebSocket/connection errors.
- Permission issues: ensure `storage/` and `bootstrap/cache/` are writable by the web server.

## Additional notes

- The project includes `@shopify/polaris` in `package.json`. The app previously referenced Polaris from a CDN; the recommended setup is to import Polaris via the local frontend build (the repo already includes an import in `resources/css/app.css`). Keep Polaris versions aligned between `package.json` and any CDN references.
- The repo's seeds create test users (password `password`) — check `database/seeders` or the output after seeding to see their emails.

## Quick command summary

```bash
# install deps
composer install
npm install
npm run build

# migrate + seed
php artisan migrate --seed

# run services
php artisan reverb:start
php artisan queue:work --queue=high,medium,low
php artisan serve
```

## Windows users

Recommended: use WSL2 (Windows Subsystem for Linux) and run the Linux instructions inside an Ubuntu/Debian distro — that gives the smoothest experience and matches the commands above.

If you prefer to run natively on Windows, here are two options:

- Option A — WSL2 (recommended)

	1. Enable WSL and install an Ubuntu distribution from the Microsoft Store.
	2. Open the Ubuntu shell and follow the Linux steps in this README (they will work unchanged).

- Option B — native Windows (PowerShell + Chocolatey)

	Install Chocolatey (if you don't have it), then install common packages:

	```powershell
	choco install -y php composer nodejs-lts mysql
	# For composer you may also use the Windows installer from getcomposer.org
	```

	- For Redis it's easiest to run Redis in Docker on Windows or use WSL; native Windows Redis packages are unofficial and not recommended for production.

	Example Docker Redis command (PowerShell):

	```powershell
	docker run -d --name redis -p 6379:6379 redis:7
	```

	After installing the required tools, open PowerShell in the project folder and run the same project commands (composer, npm, php artisan). Some commands that expect systemd (e.g., starting Redis via systemctl) won't work on native Windows — use Docker or WSL there.

Notes for Windows users
- Reverb and other realtime components may rely on Unix-like tooling; WSL avoids many compatibility issues.
- If you use native MySQL and PHP on Windows, ensure your PATH points to the installed binaries and that PHP has required extensions (mbstring, xml, curl, zip, etc.).
- When using Docker for services (Redis, Node processing service), expose the ports and update `.env` hostnames accordingly (e.g., set REDIS_HOST=127.0.0.1 if using Docker on Windows).

