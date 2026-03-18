# Deadhock — Full Feature Implementation Plan

## Context

The base Laravel project already has Discord interaction wiring, signature verification, and basic subscription storage. What remains is completing the full bot loop: player registration via the `/deadhock` slash command, a cron-driven Deadlock match poller, an ephemeral match display page, and Browsershot-powered Discord posting.

Column naming decision: use existing convention (`subscription_id`, `player_id`) consistently across all new tables — no rename needed.

---

## What Already Exists

- `subscriptions` table + `Subscription` model + factory
- `players` table + `Player` model + factory
- `subscription_players` table (columns: `subscription_id`, `player_id`, `nice_name`)
- `matches` table + `Matches` model + factory
- `DiscordInteractionController` → currently only upserts subscription, returns `['test' => true]`
- `DiscordBotService` — command registration only
- `DeadlockApiService`, `PlayerApiService`, `MatchApiService` — stubs (return null/void)
- Middleware, base request, ping verification action

---

## Phase 1 — Database

### New Migrations

**`create_match_participants_table`**
```
id, match_id (FK matches.id cascade), player_id (FK players.id cascade),
unique(match_id, player_id), timestamps
```

**`create_match_posts_table`**
```
id, match_id (FK matches.id cascade), player_id (FK players.id),
subscription_id (FK subscriptions.id cascade),
discord_message_id string(32) nullable, posted_at useCurrent(),
unique(match_id, subscription_id), timestamps
```

### New & Updated Models (with relationships + fillable)

| Model | Type | Notes |
|---|---|---|
| `SubscriptionPlayer` | New — extends `Pivot` | pivot for subscription_players with `nice_name` |
| `MatchParticipant` | New | FK to matches + players |
| `MatchPost` | New | FK to matches + subscriptions + players |
| `Subscription` | Update | add `players()` BelongsToMany via SubscriptionPlayer, `matchPosts()` HasMany |
| `Player` | Update | add `subscriptions()` BelongsToMany, `matchParticipants()` HasMany, `matchPosts()` HasMany |
| `Matches` | Update | add `participants()` HasMany MatchParticipant, `posts()` HasMany MatchPost |

All `BelongsToMany` relationships use `->using(SubscriptionPlayer::class)->withPivot('nice_name')->withTimestamps()`.

### New Factories

- `SubscriptionPlayerFactory` — ties an existing subscription + player with a fake nice_name
- `MatchParticipantFactory`
- `MatchPostFactory`

---

## Phase 2 — Discord Slash Command Player Registration

### Discord Payload Shape

```json
{
  "type": 2,
  "guild_id": "...",
  "channel_id": "...",
  "data": { "options": [{ "name": "players", "value": "76561198xxx:Name 76561198yyy:Other" }] }
}
```

Players arrive as a space-separated string of `steam_id:nice_name` pairs at `data.options.0.value`.

### New DTO

**`app/DTO/PlayerDTO.php`** — `public string $steam_id`, `public string $nice_name`

### New Actions

**`app/Actions/Player/ParsePlayerAction.php`**
- `handle(string $raw): array<PlayerDTO>` — splits by space, then `:`, trims and skips empty/malformed entries, returns DTOs

**`app/Actions/Player/StorePlayerAction.php`**
- `handle(PlayerDTO $pair): Player` — `Player::query()->createOrFirst(['steam_id' => ...])`

**`app/Actions/PlayerSubscription/StorePlayerSubscriptionAction.php`**
- `handle(Subscription $sub, Player $player, PlayerDTO $playerDTO): PlayerSubscription` — `updateOrCreate(['subscription_id', 'player_id'], ['nice_name'])`

### Updates

**`StoreSubscriptionRequest`** — add `data.options.0.value` as required string when not a ping

**`DiscordInteractionController::invoke()`** — after subscription upsert, inject and call:
1. `ParsePlayerAction` on `data.options.0.value`
2. For each pair: `StorePlayerAction` → `StorePlayerSubscriptionAction`
3. Return type 4 response: `{"type": 4, "data": {"content": "Now tracking N player(s) in this channel."}}`

---

## Phase 3 — Deadlock API Services (Fix Return Types)

**`DeadlockApiService::get()`** — change return type from `null` to `?array`, return decoded JSON body

**`PlayerApiService::getMatchHistory()`** — return `?array` of match history

**`MatchApiService::getMatchMetadata()`** — return `?array` of match metadata

---

## Phase 4 — Match Display Page

**Route** (add to `routes/web.php`):
```
GET /matches/{matchId}  →  MatchController::show   name: matches.show
```

**`app/Http/Controllers/MatchController.php`**
- `show(string $matchId): View|Response`
- Reads `Cache::get("match.{$matchId}")` — returns 404 if null
- Passes data to view

**`resources/views/matches/show.blade.php`**
- Renders match result HTML that Browsershot will screenshot
- Minimal styled page: hero name, duration, result, participant list

---

## Phase 5 — Cron Polling

### Dependency

```
composer require spatie/browsershot
```

### Console Command

**`app/Console/Commands/PollDeadlockMatchesCommand.php`**
- Signature: `deadlock:poll`
- `handle(PollMatchesAction $action): void` — delegates entirely

**Scheduled** in `routes/console.php`:
```php
Schedule::command('deadlock:poll')->hourly();
```

### Actions (all in `app/Actions/Deadlock/`)

**`PollMatchesAction`**
- Fetches all players that have ≥1 subscription via `Player::query()->whereHas('subscriptions')`
- Calls `CheckPlayerMatchesAction` for each, collects pending posts, sorts by `match_started_at` ASC before posting

**`CheckPlayerMatchesAction`**
- `handle(Player $player): void`
- Calls `PlayerApiService::getMatchHistory()` — last 10 matches
- For each match ID: call `FetchAndCacheMatchAction`, then `PostMatchToSubscriptionsAction`
- Updates `player->last_checked_at`

**`FetchAndCacheMatchAction`**
- `handle(string $matchId): ?Matches`
- Checks `Matches::query()->where('match_id', $matchId)->first()` — returns existing if found
- Otherwise: calls `MatchApiService::getMatchMetadata()`, writes to `Cache::put("match.{$matchId}", $data, now()->addHours(24))`
- Inserts `Matches` row, inserts `MatchParticipant` rows for all players in response

**`PostMatchToSubscriptionsAction`**
- `handle(Matches $match): void`
- Loads participants with subscriptions: `$match->participants()->with('player.subscriptions')`
- For each unique subscription, checks `MatchPost::query()->where(['match_id' => $match->id, 'subscription_id' => $sub->id])->exists()`
- If not posted: calls `ScreenshotMatchAction`, posts to Discord, inserts `MatchPost` row (catches unique constraint violation to safely handle race conditions)

**`ScreenshotMatchAction`**
- `handle(Matches $match): string` — returns temp file path
- Builds URL to `/matches/{$match->match_id}` using `url()` helper
- Uses `Browsershot::url($url)->save($tempPath)`, returns path

### Discord Posting

Update **`DiscordBotService`** with:
```php
public function postMatchToChannel(string $channelId, string $imagePath): ?string
```
- POSTs multipart to Discord's `channels/{channelId}/messages` with image attachment
- Returns `discord_message_id` from response

---

## Critical Files

| File | Action |
|---|---|
| `database/migrations/` | 2 new migrations |
| `app/Models/Subscription.php` | add relationships |
| `app/Models/Player.php` | add relationships |
| `app/Models/Matches.php` | add relationships |
| `app/Models/SubscriptionPlayer.php` | new Pivot model |
| `app/Models/MatchParticipant.php` | new model |
| `app/Models/MatchPost.php` | new model |
| `app/DTO/PlayerDTO.php` | new |
| `app/Actions/Player/ParsePlayerAction.php` | new |
| `app/Actions/Player/StorePlayerAction.php` | new |
| `app/Actions/PlayerSubscription/StorePlayerSubscriptionAction.php` | new |
| `app/Http/Requests/StoreSubscriptionRequest.php` | add players validation |
| `app/Http/Controllers/DiscordInteractionController.php` | add player registration |
| `app/Http/Controllers/MatchController.php` | new |
| `app/Services/Deadlock/DeadlockApiService.php` | fix return type |
| `app/Services/Deadlock/PlayerApiService.php` | fix return type |
| `app/Services/Deadlock/MatchApiService.php` | fix return type |
| `app/Services/Discord/DiscordBotService.php` | add postMatchToChannel() |
| `app/Console/Commands/PollDeadlockMatchesCommand.php` | new |
| `app/Actions/Deadlock/PollMatchesAction.php` | new |
| `app/Actions/Deadlock/CheckPlayerMatchesAction.php` | new |
| `app/Actions/Deadlock/FetchAndCacheMatchAction.php` | new |
| `app/Actions/Deadlock/PostMatchToSubscriptionsAction.php` | new |
| `app/Actions/Deadlock/ScreenshotMatchAction.php` | new |
| `resources/views/matches/show.blade.php` | new |
| `routes/web.php` | add matches route |
| `routes/console.php` | schedule deadlock:poll |

---

## Verification

1. Run `php artisan migrate` — 2 new tables created cleanly
2. Register command: `php artisan discord:register-commands --guild={guildId}` → post `/deadhock players:76561198xxx:TestPlayer` in Discord → confirm subscription + player + subscription_player rows in DB, Discord replies with "Registered 1 player: TestPlayer"
3. Visit `/matches/{matchId}` with no cache → 404; seed cache manually via `php artisan tinker` → page renders
4. Run `php artisan deadlock:poll` manually → confirm match rows inserted, posts sent, match_posts rows written
5. Run `php artisan test --compact` → all tests pass
