# Deadlockhook — Implementation Reference

## What This Project Does

A Laravel application that acts as a Discord bot for the game Deadlock. Users run `/deadlockhook 1234567890:Ace 7615471872:Bromar` in a Discord channel. The app tracks those players, polls the Deadlock API hourly, and posts a post-match screenshot + public scoreboard link to the channel whenever a new match is detected.

---

## Key Design Decisions

### Discord: HTTP Interactions, not WebSocket
No persistent bot process. Discord POSTs to `POST /discord/interactions` when a slash command is used. Responses must be returned within 3 seconds. All heavy work (API calls, screenshots) is dispatched as queued jobs.

### Slash command registration
Slash commands are **not** automatic when a bot joins a server. Register globally once (`php artisan discord:register-commands`) — propagates within 1 hour and works in every server forever. For dev, use `--guild=ID` for instant registration.

### nice_name is per-channel, not per-player
The same Steam ID can have different display names in different Discord servers. `nice_name` lives on the **pivot table** (`discord_subscription_tracked_player`), not on `TrackedPlayer`. The `TrackedPlayer` model only stores the global `steam_id` and `last_match_id`.

### Privacy by design
When rendering a match screenshot, only players tracked within **that specific Discord channel** are shown by name. All other players (including those tracked in other channels) are shown as "Player 1"–"Player 12" by match position. The privacy is enforced in two places: `$trackedSteamIds` passed to the job only contains that channel's players, and `loadNicenames()` further filters by `channel_id` on the pivot join.

### Multi-match handling
The Deadlock API updates once per hour per player. Multiple short matches can complete within one polling window. `PlayerApiService::getMatchesSince()` returns **all** matches with `match_id > last_match_id` (match IDs are sequential), not just the latest one. Each new match gets its own `ProcessNewMatch` job.

### Party detection
If multiple tracked players in the same channel played the same match, only **one** screenshot is sent. The `CheckNewMatches` command groups players by `match_id` per subscription before dispatching — one job per unique match per channel.

### First match behaviour
When a player is first added via `/deadlockhook`, `last_match_id` is null. The first cron run fetches their latest match, shows it as a screenshot, and sets `last_match_id`. There is no silent seeding — the first match is always shown.

### Deadlock API rate limits
The free tier allows ~3 requests/hour. With 3 tracked players the scheduler should run `hourly()`, not `everyTwoMinutes()`. An API key (`DEADLOCK_API_KEY`) unlocks higher limits and should be sent as `X-API-Key` header.

---

## Architecture

```
Discord user types /deadlockhook
        │
        ▼
POST /discord/interactions
        │
VerifyDiscordSignature middleware (Ed25519)
        │
DiscordInteractionController
  ├── Type 1 (PING) → {"type":1}
  └── Type 2 (command)
        ├── Parse steamId:niceName pairs
        ├── firstOrCreate DiscordSubscription (channel_id, guild_id)
        ├── firstOrCreate TrackedPlayer per steam_id
        ├── syncWithoutDetaching pivot with nice_name
        └── {"type":4, "data": {"content": "Now tracking X players"}}

Scheduler (hourly)
        │
CheckNewMatches command
  ├── For each TrackedPlayer:
  │     ├── last_match_id null → getLatestMatchId() → treat as new match
  │     └── last_match_id set → getMatchesSince() → all newer matches
  ├── Update last_match_id = max(new match IDs)
  └── Per DiscordSubscription: group players by match_id → dispatch ProcessNewMatch

ProcessNewMatch job (queued)
  ├── Fetch match metadata (MatchApiService)
  ├── Load nice_names from pivot (filtered by channel_id)
  ├── Build player display list (tracked → nice_name, others → "Player N")
  ├── Render match-summary.blade.php → Browsershot PNG
  ├── Send PNG + scoreboard URL to Discord (DiscordBotService)
  └── Delete temp file
```

---

## Database Schema

### `discord_subscriptions`
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| guild_id | string | Discord server snowflake |
| channel_id | string | unique — one subscription per channel |
| timestamps | | |

### `tracked_players`
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| steam_id | string | unique — global record, one per player |
| last_match_id | unsignedBigInteger | nullable — null means never polled |
| timestamps | | |

### `discord_subscription_tracked_player` (pivot)
| Column | Type | Notes |
|--------|------|-------|
| id | bigIncrements | |
| discord_subscription_id | foreignId | cascadeOnDelete |
| tracked_player_id | foreignId | cascadeOnDelete |
| nice_name | string | nullable — per-channel display name |
| timestamps | | |
| unique | [discord_subscription_id, tracked_player_id] | |

---

## Environment Variables

```
DISCORD_BOT_TOKEN=
DISCORD_APP_ID=
DISCORD_PUBLIC_KEY=
DEADLOCK_API_KEY=
```

---

## File Map

| File | Purpose |
|------|---------|
| `config/discord.php` | `bot_token`, `app_id`, `public_key`, `api_base` |
| `app/Models/DiscordSubscription.php` | `belongsToMany(TrackedPlayer)->withPivot('nice_name')->withTimestamps()` |
| `app/Models/TrackedPlayer.php` | `belongsToMany(DiscordSubscription)->withPivot('nice_name')->withTimestamps()` |
| `app/Services/DeadlockApiService.php` | Guzzle wrapper — `get(string $path): ?array` — sends `X-API-Key` header |
| `app/Services/PlayerApiService.php` | `getMatchHistory()`, `getLatestMatchId()`, `getMatchesSince()` |
| `app/Services/MatchApiService.php` | `getMatchMetadata(int $matchId): ?array` |
| `app/Services/DiscordBotService.php` | `registerGlobalCommand()`, `registerGuildCommand()`, `sendMatchResult()` |
| `app/Http/Middleware/VerifyDiscordSignature.php` | Ed25519 sig check via `sodium_crypto_sign_verify_detached` |
| `app/Http/Controllers/DiscordInteractionController.php` | Handles PING + slash command |
| `app/Jobs/ProcessNewMatch.php` | Renders screenshot, sends to Discord |
| `app/Console/Commands/CheckNewMatches.php` | `deadlock:check-matches` — hourly poll + dispatch |
| `app/Console/Commands/RegisterDiscordCommands.php` | `discord:register-commands [--guild=ID]` |
| `app/Livewire/Pages/MatchScoreboard.php` | Public scoreboard — all players anonymous, cached 24h |
| `resources/views/match-summary.blade.php` | Browsershot screenshot — Tailwind CDN, 1200px, tracked players highlighted |
| `resources/views/livewire/pages/match-scoreboard.blade.php` | Public scoreboard view |
| `routes/web.php` | `POST /discord/interactions`, `GET /matches/{matchId}` |
| `routes/console.php` | `Schedule::command(CheckNewMatches::class)->hourly()` |
| `bootstrap/app.php` | CSRF exclusion for `discord/interactions` |

---

## Implementation Order

1. **Migrations** — `discord_subscriptions`, `tracked_players`, pivot (with `nice_name` + timestamps on pivot, NOT on `tracked_players`)
2. **Models** — both `belongsToMany` with `->withPivot('nice_name')->withTimestamps()`
3. **Factories + Seeder** — `nice_name` is attached on pivot only, not in `TrackedPlayerFactory`
4. **`DeadlockApiService`** — Guzzle wrapper, `X-API-Key` header, returns `array|null`
5. **`PlayerApiService`** — `getMatchHistory` (sorted desc by `start_time`), `getLatestMatchId`, `getMatchesSince` (filter by `match_id > $lastMatchId`)
6. **`MatchApiService`** — single method wrapping `matches/{id}/metadata`
7. **`DiscordBotService`** — `registerGlobalCommand`, `registerGuildCommand` (shared `postCommand` private method), `sendMatchResult` (multipart)
8. **`VerifyDiscordSignature`** middleware — `abort_if` / `abort_unless` pattern, route-level only
9. **`DiscordInteractionController`** — parse `steamId:niceName` tokens, `firstOrCreate` subscription, `syncWithoutDetaching` pivot
10. **Routes** — `POST /discord/interactions` with middleware, `GET /matches/{matchId}` Livewire route
11. **`bootstrap/app.php`** — `$middleware->validateCsrfTokens(except: ['discord/interactions'])`
12. **`ProcessNewMatch` job** — `loadNicenames()` filters pivot by `channel_id`, `buildPlayerList()` maps account_id to display name
13. **`match-summary.blade.php`** — standalone HTML (no Vite), Tailwind CDN `@4`, fixed 1200px, two-team layout
14. **`MatchScoreboard` Livewire component** — `mount(int $matchId)`, `Cache::remember("match.{$matchId}", 24h, ...)`
15. **`match-scoreboard.blade.php`** — uses app layout, all players shown as "Player N" by position
16. **`CheckNewMatches` command** — no seed/new distinction; `last_match_id null` treated same as new match; `max()` for update; group by match_id per subscription
17. **`RegisterDiscordCommands` command** — global by default, `--guild` for dev
18. **Scheduler** — `hourly()` for free API tier, `everyTwoMinutes()` once API key obtained
19. **Install** — `composer require spatie/browsershot` + `npm install puppeteer`
20. **Pint + tests**

---

## Gotchas

- `DeadlockApiService` must NOT be `final` — Mockery needs to subclass it for tests. Exclude it from pint's `final_class` rule via `notPath` in `pint.json`.
- `nice_name` on the pivot requires `->withPivot('nice_name')->withTimestamps()` on **both** sides of the relationship, and the seeder must pass it as `[$player->id => ['nice_name' => ...]]` in `attach()`.
- Discord's PING verification happens when you save the Interactions Endpoint URL in the Developer Portal — Laravel must be running and reachable at that moment.
- Slash commands must be registered via API before they appear in Discord. Adding a bot to a server does not register commands automatically.
- Browsershot needs `->setNodeModulePath(base_path('node_modules'))` and `npm install puppeteer` to find Chromium.
- The `match-summary.blade.php` screenshot view must be fully self-contained (no Vite, no external assets that require auth) — use Tailwind CDN `@4`.

---

## Running Locally

Three terminals required:

```bash
php artisan serve
php artisan queue:work
php artisan schedule:work
```

Plus ngrok for Discord to reach your local endpoint:

```bash
ngrok http 8000 --hostname=your-hostname.ngrok-free.app
```

One-time setup:

```bash
php artisan discord:register-commands --guild=YOUR_GUILD_ID  # dev
php artisan discord:register-commands                         # production (1h propagation)
```
