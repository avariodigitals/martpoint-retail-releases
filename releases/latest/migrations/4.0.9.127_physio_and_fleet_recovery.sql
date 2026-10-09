-- MartPoint 4.0.9.127 — Physiotherapy provisioning and fleet recovery.
-- Code release marker; no destructive schema changes.
-- Fresh Physio installs seed full-control clinical grants and staff presets.
-- Existing Physio stores sync once per store/session/release, preserving
-- recorded permission revocations. Required industry flags are restored.
-- Fleet retries retain one command and a bounded resume count; hard errors
-- stop. Missing canonical schema is fetched and hash-verified before migration.
DO 0;
