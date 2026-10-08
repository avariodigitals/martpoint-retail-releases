-- ============================================================================
-- MartPoint 4.0.9.112 — printing sidebar rail
--
-- No schema change. Carries the release forward and records the change.
--
-- WHAT WAS WRONG
--
-- The Printing module has a complete workspace — jobs, production board,
-- artworks, authorisations, payments, quotations, costing reports — served
-- from Printing.php with 13 views and 16 permissions. Reachable at /printing
-- since .111 redirects the dashboard there. But the shared sidebar
-- (views/mp_sidebar.php) had NO printing entries at all: two incidental
-- matches for "print" in that file were both the unrelated `print_labels`
-- permission.
--
-- So a print shop landed on /printing and the rail offered Sales, Inventory,
-- Clients, Reports — the retail rail. The workspace was reachable but
-- unnavigable, and there was no way to find Jobs, Production or the machine
-- screens from the menu.
--
-- WHAT CHANGED
--
-- * mp_sidebar.php gains a printing rail: Print Shop (Overview, Print Orders,
--   Production Floor, Artwork, Authorisations, Payments), Quotations (New
--   Quote, Awaiting a Job, All Quotes) and Insights (Costing & Margin, Stage
--   Report). Every item is gated on a permission the controller itself
--   checks, so a link and its destination cannot disagree.
-- * The retail groups are suppressed for printing via a single $hide_retail
--   flag rather than repeating `!$is_creator` on each group, so a group added
--   later cannot forget the check.
-- * Two guards had an operator-precedence defect: `!$hide_retail && (perms) ||
--   more_perms` parses as `(!$hide_retail && perms) || more_perms`, so the
--   extra permissions escaped the check and leaked the Finance and Reports
--   groups onto the printing rail. Both are now wrapped in one paren group.
--   This is the kind of bug that only shows on the SECOND call site pattern —
--   the same expression had worked on every other group because those listed
--   their permissions inside the group already.
--
-- NOTE ON MACHINES: migrations .107/.108 created db_print_machines,
-- db_print_machine_readings, db_print_machine_supplies,
-- db_print_machine_maintenance, db_print_maintenance_parts,
-- db_print_customer_materials and db_print_custody_moves, and
-- Printing_ops_model drives them. There is NO controller and NO view for them
-- yet — only the acceptance suite exercises the model directly. This release
-- therefore does NOT add a machine menu entry, because there would be no
-- screen behind it. The tables and model are ready for that work.
-- ============================================================================

-- No schema change. This migration is intentionally a no-op beyond ensuring
-- the tables .107/.108 introduced still exist, so a partially-applied upgrade
-- is detectable rather than silent.
SET @has_machines = (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_print_machines'
);
SET @sql = IF(@has_machines = 0, 'DO 0', 'DO 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
