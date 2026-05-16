# Stratos Logbook API

A phpVMS 7 module that exposes the pilot's flight history for the [Stratos desktop client](https://skyvexsoftware.com) — list, detail, and aggregate stats. Builds on top of [stratos-core-api](https://github.com/skyvexsoftware/stratos-core-api).

## Install

You'll need a working phpVMS 7 install with **stratos-core-api** already enabled.

1. Download **`module.zip`** from the [latest release](https://github.com/skyvexsoftware/stratos-logbook-api/releases/latest).
2. In your phpVMS admin: **Admin → Modules → Add New Module**, upload the zip.
3. Back on the Modules list, **enable** `StratosLogbook`.
4. From a shell:
   ```bash
   php artisan optimize:clear
   ```

That's it — your phpVMS now exposes logbook endpoints at `/api/stratos/logbook`.

## What it adds

```
GET    /api/stratos/logbook/pireps           paginated PIREP list
GET    /api/stratos/logbook/pireps/{id}      single PIREP detail
GET    /api/stratos/logbook/stats            aggregate stats
```

All endpoints require a Bearer token (the pilot's `users.api_key`), authenticated through `stratos-core-api`'s `StratosAuth` middleware.

All data comes from phpVMS native tables — `pireps`, `acars` (route + log rows), and `users`. The module owns zero tables.

## Local development

```bash
git clone https://github.com/skyvexsoftware/stratos-logbook-api
cd stratos-logbook-api
composer install
./vendor/bin/pest
```

Symlink into a phpVMS install for live testing:

```bash
ln -s "$(pwd)" /path/to/phpvms/modules/StratosLogbook
cd /path/to/phpvms && php artisan module:enable StratosLogbook
```

## Releasing

Tag a release with `v*` (e.g. `v0.1.0`). The release workflow builds `module.zip` and attaches it to the GitHub release.

## License

MIT
