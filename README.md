# Content planner

[![Tests](https://github.com/Schaulagain/content-planner/actions/workflows/tests.yml/badge.svg)](https://github.com/Schaulagain/content-planner/actions/workflows/tests.yml)

A small web app I use to plan and learn from my nature videos on TikTok ([@hocuspocus760](https://www.tiktok.com/@hocuspocus760)), and the same clips on Instagram Reels and YouTube Shorts.

I film around Nepal on my phone. Ideas used to live in my head and the numbers lived in TikTok Studio, so it was hard to tell what was working. This puts the whole loop in one place: jot down an idea at the spot, move it along as it gets filmed and edited, write down the numbers two days after posting, and keep one line a week about what I learned.

It's built to be added to the iPhone home screen and used one-handed.

## What it does

- **Board.** Every idea sits in a stage: idea, filmed, edited, posted, reviewed. Tap the arrows to move it along.
- **Quick add.** A photo of the spot, the place (filled in from your location through OpenStreetMap), a caption idea and a series. Photos are shrunk on the phone before upload.
- **Series.** Posts in a series get numbered in order, like "Places that make you slow down #3".
- **Posting.** Mark an idea as posted with the caption you actually used and the platforms it went to.
- **Stats.** Views, likes, comments, shares, saves and average watch time for each platform. Once every platform has its numbers, the idea moves to reviewed.
- **Week.** This week's posts against the target (three by default), and a box for the one thing you learned. Earlier learnings stay listed underneath.
- **Reminders.** A push notification through [ntfy](https://ntfy.sh) when a post's stats are due, and a Sunday evening nudge if the week hasn't been reviewed.

The words it uses are listed in [GLOSSARY.md](GLOSSARY.md).

## Running it

You need PHP 8.4 or newer, Composer and Node.

```sh
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan user:create you@example.com
php artisan serve
```

For reminders, set `NTFY_TOPIC` in `.env`, subscribe to that topic in the ntfy app, and run the scheduler (`php artisan schedule:work`, or a cron entry for `schedule:run`).

Other settings in `.env`:

| Setting | Default | |
| --- | --- | --- |
| `PLANNER_TIMEZONE` | `Asia/Kathmandu` | Times on the page and the Sunday nudge |
| `PLANNER_POSTS_PER_WEEK` | `3` | The weekly target |
| `PLANNER_STATS_AFTER_HOURS` | `48` | When stats are due |

```sh
php artisan test
```

## What's next

- Hosting it at `plan.sumanchaulagain.info.np`.
- Pulling TikTok's views, likes, comments and shares automatically through a sandbox app on TikTok's Display API. Watch time and traffic sources only show in TikTok Studio, so those stay typed in by hand.
- Claude suggesting caption lines from a description of the clip.

## How this was built

The code and docs here were written mostly by **Claude** (Anthropic's AI model, in Claude Code). The idea came out of a long back-and-forth about growing my TikTok. I made the design calls, I test it, and the agent did most of the writing.

## Where this information comes from

- **Checked against TikTok's developer docs (November 2026):** the Display API's video fields (views, likes, comments and shares, with no watch time or traffic source), and sandbox apps working for your own accounts without app review. See [the video object](https://developers.tiktok.com/doc/tiktok-api-v2-video-object) and [sandbox](https://developers.tiktok.com/doc/add-a-sandbox).
- **General knowledge:** how OpenStreetMap's Nominatim turns a location into a place name.

## License

MIT
