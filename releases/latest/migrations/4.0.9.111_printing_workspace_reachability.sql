-- ============================================================================
-- MartPoint 4.0.9.111 — printing workspace reachability
--
-- No schema change. This file carries the release forward and records what
-- changed, because a version that ships controller behaviour with no migration
-- cannot be told apart from a version that shipped nothing.
--
-- TWO DEFECTS FIXED (both in code):
--
--   1. /dashboard had no branch for the printing industry.
--
--      Dashboard::index() routes `creator` to the Creator Workspace and
--      physiotherapy to the clinic dashboard, but printing fell through to the
--      retail dashboard. A store switched to printing therefore kept showing
--      retail sales, stock and profit — the store said "printing" and the
--      screen said otherwise. Printing now redirects to /printing the same way
--      Creator does; ?classic=1 still gives the retail dashboard.
--
--   2. The printing module gated on a flag that does not exist.
--
--      Printing::_check_feature() required `printing_workflow`, a key present
--      NOWHERE in the codebase — not in mp_get_feature_flags() (67 keys), not
--      in any business preset. mp_feature_enabled() falls through to its
--      default arm and looks the key up in the store's feature list, so it
--      returned false unconditionally: the entire printing workspace was
--      unreachable for every store, and would have reported "not activated"
--      even on a correctly configured print shop.
--
--      The gate is now `production_workflow`, which the printing preset has
--      declared all along. The old spelling is still accepted so an install
--      that somehow stored it keeps working.
--
-- WHY THIS NEEDS A MIGRATION AT ALL: stores whose industry is `printing` but
-- whose stored feature list predates the preset fix may lack
-- production_workflow. Without it the workspace stays locked, which would look
-- exactly like the bug this release fixes. The UPDATE below backfills the flag
-- for printing stores only — it never turns a capability on for another
-- industry.
-- ============================================================================

-- Backfill production_workflow for printing stores whose flag blob predates it.
--
-- JSON_SET/JSON_EXTRACT need MySQL 5.7+. Guarded on JSON_VALID so a malformed
-- or legacy non-JSON value is skipped rather than aborting the migration —
-- a failed migration stops the chain permanently, which is far worse than one
-- store needing its flags re-saved from Business Profile.
SET @has_col = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'db_store_industry_settings'
    AND COLUMN_NAME  = 'feature_flags_json'
);

SET @sql = IF(@has_col = 1,
  'UPDATE `db_store_industry_settings`
      SET `feature_flags_json` = JSON_SET(`feature_flags_json`, ''$.production_workflow'', ''1'')
    WHERE `industry_type` = ''printing''
      AND `feature_flags_json` IS NOT NULL
      AND JSON_VALID(`feature_flags_json`)
      AND JSON_EXTRACT(`feature_flags_json`, ''$.production_workflow'') IS NULL',
  'DO 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
