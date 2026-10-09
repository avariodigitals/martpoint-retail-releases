-- ============================================================================
-- MartPoint 4.0.9.126 — updater schema reconciliation + ledger dedupe
--
-- TWO PARTS. The real fix is CODE; this file exists to advance the release
-- version in lockstep and to clean up the ledger inflation that confused the
-- fleet view.
--
-- PART 1 — CODE (application/libraries/Updater.php)
--
--   The migration runner now RECONCILES the schema before running migrations.
--   It replays the shipped canonical `CREATE TABLE IF NOT EXISTS` definitions
--   from setup/install/includes/db_schema_catchup.sql, so a table that drifted
--   out of an install (the ledger records it, but the table is missing) is
--   recreated with the release's FULL column set BEFORE a later migration's
--   guarded ALTER can fatal with:
--
--     Migration failed [4.0.9.107_printing_machines.sql]:
--     Table '...db_print_stage_logs' doesn't exist
--
--   That fatal was the killer: it is deterministic (the guard counts columns
--   of a table that no longer exists and then ALTERs it), so the runner retried
--   5 times, gave up, and the install was wedged at that migration FOREVER —
--   it could never reach the later .123/.125 repair migrations. Recreating the
--   missing table first turns every guarded ALTER into a `DO 0` no-op.
--
--   The reconciliation is idempotent, non-destructive (zero DROP TABLE), and
--   chunked with checkpointing so a large schema never exceeds one request.
--
-- PART 2 — THIS FILE
--
--   Dedupe db_schema_migrations. The ledger's UNIQUE key is (version, filename),
--   so a migration re-recorded under a later release left duplicate rows for
--   the same file — the source of the confusing "187 of 123 applied" count.
--   Keep one row per filename (lowest id). The filename SET is unchanged, so
--   skip/pending logic (which matches by filename) is unaffected.
--
-- Idempotent. Safe to re-run.
-- ============================================================================

DELETE m1
  FROM `db_schema_migrations` m1
 INNER JOIN `db_schema_migrations` m2
    ON m1.filename = m2.filename
   AND m1.id > m2.id;
