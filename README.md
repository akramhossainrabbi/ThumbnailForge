# ThumbnailForge

A simple Laravel application for processing thumbnail jobs.

## Requirements

- PHP 8.1+ (or the version required by the project's composer.json)
- Composer
- Node.js & npm (for frontend assets)
- MySQL (or update `DB_CONNECTION` to use `sqlite`) 
- Redis (used by sessions and queue in `.env`)

## Quick start

1. Clone the repository:

```bash
git clone <repo-url> thumbnailforge
cd thumbnailforge
```

2. Install PHP dependencies:

```bash
composer install --no-interaction --prefer-dist
```

3. Copy and edit environment variables:

```bash
cp .env.example .env
# Edit .env and set DB_*, REVERB_*, and other values as needed
```

4. Generate the application key:

```bash
php artisan key:generate
```

5. Create the database and run migrations + seeders:

```bash
# ensure your DB_* settings in .env are correct
php artisan migrate --seed
```

6. Set up storage and filesystem:

```bash
php artisan storage:link
```

7. Install frontend dependencies and build assets (optional for local dev):

```bash
npm install
# development build
npm run dev
```

8. Start the application (simple way):

```bash
php artisan serve --host=0.0.0.0 --port=8000
# then open http://localhost:8000
```

9. Run queue workers (if using queued jobs):

```bash
# start a worker for configured queues
php artisan queue:work --queue=high,medium,low
```

10. (Optional) Start the Reverb dev server used by the project (if applicable):

```bash
# this project includes a reverb server command
php artisan reverb:start
```

## Running tests

```bash
php artisan test
```

## Notes

- If you don't have Redis available, update `.env` to use a different `SESSION_DRIVER` and set `QUEUE_CONNECTION` appropriately.
- The repo includes an `.env` with example keys for `REVERB_*`. Replace these with secure values in production.
- If you prefer Docker, you can adapt these steps to your container setup.

## Troubleshooting

- Permission issues when writing to `storage/` or `bootstrap/cache/`: ensure web server user has write access.
- Database connection errors: double-check `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.

