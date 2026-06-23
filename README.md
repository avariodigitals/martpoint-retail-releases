# MartPoint Retail Releases

**NOTE:** This repository serves as the MartPoint Retail auto-update distribution channel. The `release-manifest.json` acts as the source of truth: the updater compares its local version against the manifest's `version` field, downloads files listed under `files` after verifying their SHA-256 hash and size, applies SQL migrations tracked in `db_schema_migrations` to prevent re-runs, and skips all paths declared in `protected_paths`. Always update the manifest, recalculate hashes, and keep migrations idempotent before pushing a new release.

---

## Repository Structure

```
releases/
├── release-manifest.json          # Global manifest (version, files, hashes, migrations)
└── latest/                        # Current release payload
    ├── migrations/                # SQL migration scripts
    │   └── 3.0_to_4.0.0.sql
    ├── application/               # PHP app patches (controllers, config, core, libraries)
    ├── theme/                     # Frontend assets (CSS, JS, views)
    └── vendor/                    # Composer dependencies (Twilio SDK, TCPDF, etc.)
```

---

## How the Auto-Update System Works

1. **Client Check** — The installed MartPoint Retail instance polls `release-manifest.json` from this repository.
2. **Version Comparison** — If `version` in the manifest is newer than the local version, the update proceeds.
3. **Integrity Verification** — Each file entry contains a SHA-256 `hash` and `size`. The updater downloads files and verifies them before overwriting.
4. **Protected Paths** — Files listed in `protected_paths` are **never** overwritten automatically:
   - `application/config/database.php`
   - `application/config/config.php`
   - `application/config/constants.php`
   - `uploads/`
   - `backups/`
5. **Database Migrations** — SQL scripts listed in the `migrations` array are executed in order. A `db_schema_migrations` table tracks applied scripts to prevent re-runs.
6. **Signature Verification** — When `signature` is provided, the updater verifies the manifest signature before processing.

---

## Creating a New Release

1. Bump the `version` and `previous_version` fields in `release-manifest.json`.
2. Add or update files under `releases/latest/`.
3. Recalculate SHA-256 hashes and file sizes for all changed files.
4. Add migration SQL scripts to `releases/latest/migrations/` and list them in `migrations`.
5. Update the `changelog`.
6. Commit and push.

---

## Migration Conventions

- Name migrations sequentially: `3.0_to_4.0.0.sql`, `4.0.0_to_4.0.1.sql`, etc.
- All migrations must be **idempotent** (safe to run multiple times).
- The migration script should insert itself into `db_schema_migrations` at the end:
  ```sql
  INSERT IGNORE INTO `db_schema_migrations` (`version`, `filename`)
  VALUES ('4.0.0', '3.0_to_4.0.0.sql');
  ```

---

## Security Notes

- Never commit sensitive data (API keys, database credentials) to this repository.
- Use the `signature` field to cryptographically sign manifests.
- The updater validates every file hash before installation.

---

## License

Proprietary - Avario Digitals. All rights reserved.
