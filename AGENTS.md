## Working here

- Read `GLOSSARY.md` first. Idea, Post, Stats, Series and Review mean specific things here, and the code uses the same names (`Stats` is one platform's numbers for one post).
- Times are stored in UTC and shown in `config('planner.timezone')`, Kathmandu by default. Convert anything typed in a local time with `Carbon::instance(...)->utc()` before saving.
- Photos are shrunk to about 1200 px in the browser (`resources/js/app.js`) before upload, so the server needs no image library.
- Reminders go out through ntfy from two scheduled commands, `reminders:stats` and `reminders:review`. Both do nothing until `NTFY_TOPIC` is set.
- Tests: `php artisan test`. Without local PHP, run it in the `composer:2` Docker image with the folder mounted at `/app`.

## Agent skills

### Issue tracker

Issues live in this repo's GitHub Issues, managed with the `gh` CLI. See `docs/agents/issue-tracker.md`.

### Triage labels

The five default labels: `needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`. See `docs/agents/triage-labels.md`.

### Domain docs

Single-context: one `GLOSSARY.md` and `docs/adr/` at the repo root. See `docs/agents/domain.md`.
