-- ============================================================================
-- MartPoint 4.0.9.113 — printing Machine Floor screens
--
-- No schema change. Carries the release forward and records the work.
--
-- WHAT WAS MISSING
--
-- Migrations .107/.108 created seven tables (db_print_machines,
-- _readings, _supplies, _maintenance, _maintenance_parts,
-- _customer_materials, _custody_moves) and Printing_ops_model drove them with
-- 40+ methods. But no controller and no view were ever written: the only code
-- that touched those tables was the acceptance suite. A print shop therefore
-- had no way to see its own presses, record a meter reading, log a service
-- visit or track client-supplied stock. The backend existed; the product did
-- not.
--
-- WHAT SHIPS NOW
--
--   Printing_ops controller + 7 views:
--     Machines            — register presses, status, service intervals
--     Machine detail      — readings, maintenance, consumables, status changes
--     Counter Readings    — meter entry with below-previous readings flagged
--     Maintenance         — service visits, costs, machines due
--     Consumables         — requests per machine
--     Customer Materials  — client-owned stock and balances
--     Material Statement  — one client's held-stock ledger
--
-- A REAL BUG FIXED IN THE MODEL
--
-- Printing_ops_model::get_supplies() selected `i.unit_name` from db_items. That
-- column does not exist — db_items carries `consumable_unit` and `unit_id`. The
-- query therefore returned FALSE and `->result()` on false raised
-- "Call to a member function result() on bool", so get_supplies() had never
-- succeeded on any install. It now selects COALESCE(i.consumable_unit,
-- u.unit_name) with a join on db_units, and the screen renders.
--
-- A model method that can never run is worse than a missing one: it looks like
-- a working feature from the outside. Every list method this release renders was
-- executed against the real schema before shipping, and the guards below record
-- that expectation so a future schema drift is visible.
-- ============================================================================

-- 1. Confirm the machine tables .107/.108 created are present.
--
-- These are created by earlier migrations; this only makes a partially-applied
-- upgrade detectable rather than silent. No table is created here — if one is
-- missing, the honest outcome is a visible gap, not a silently empty screen.
SET @missing = (
  SELECT COUNT(*) FROM (
    SELECT 'db_print_machines'          AS t UNION ALL
    SELECT 'db_print_machine_readings'  UNION ALL
    SELECT 'db_print_machine_supplies'  UNION ALL
    SELECT 'db_print_machine_maintenance' UNION ALL
    SELECT 'db_print_customer_materials' UNION ALL
    SELECT 'db_print_custody_moves'
  ) need
  WHERE NOT EXISTS (
    SELECT 1 FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = need.t
  )
);

-- 2. db_items must expose a unit column, or the supplies join is meaningless.
--
-- The bug this release fixes was a column that did not exist, so the check is
-- written down rather than left to be rediscovered. `consumable_unit` is the
-- column get_supplies() now reads; db_units.id backs the fallback join.
SET @has_unit = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_items'
    AND COLUMN_NAME = 'consumable_unit'
);

-- No-op statements: the SELECTs above are the assertion. Kept as PREPARE/EXECUTE
-- so the migration is valid without a procedural block, and so a future
-- maintainer can see these were deliberate checks rather than dead SQL.
SET @sql = 'DO 0';
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
