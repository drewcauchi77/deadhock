# Deadhock

A Discord bot that tracks [Deadlock](https://store.steampowered.com/app/1422450/Deadlock/) matches and posts scoreboard screenshots to your Discord server.

> **Disclaimer:** Deadhock is an independent, community-built project and is not affiliated with, endorsed by, or associated with Valve Corporation or Deadlock in any way.

## Setup

```bash
# 1) Install PHP deps
composer install

# 2) Copy env and set your DB credentials
cp .env.example .env

# 3) Generate app key
php artisan key:generate

# 4) Create the SQLite file
touch database/database.sqlite
# On Windows PowerShell: New-Item database/database.sqlite

# 5) Run database migrations + seeders
php artisan migrate:fresh --seed

# 6) Install JS deps
npm install

# 7) Start Vite in watch mode (one terminal)
npm run dev

# 8) Start the PHP server (second terminal)
php -S 0.0.0.0:8000 -t public
```

Open the app at [http://localhost](http://localhost) (or `http://localhost:8000` if using port 8000).

## Exposing for Discord Webhooks

If you need Discord to reach your local server (e.g. for interaction endpoints):

```bash
ngrok http 8000 --domain=your-domain.ngrok-free.app
```

In my case:

```bash
ngrok http 8000 --domain=hostly-frenzied-dalene.ngrok-free.dev
```

## Links

- [Add to Discord](https://discord.com/oauth2/authorize?client_id=1483407552074219530&scope=bot+applications.commands&permissions=34816)
- [Report an issue](https://github.com/drewcauchi77/deadhock/issues)
