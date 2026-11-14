-- Waiter sections — which tables belong to which waiter (v4.0.9.235).
--
-- 4.0.9.231 put a waiter on every round, but the name had to be typed onto the
-- tab each time a guest sat down. On a real floor the seating is not random:
-- the garden belongs to Chidi, the bar rail to Ada. A lounge that hands out
-- sections at the start of the night should not have to re-say who is holding
-- table 12 on every single order, and a bill raised without anyone looking at
-- the section list should still attribute the sale to the right person.
--
-- So the section lives on the table, and a tab opened on that table inherits
-- it. The chain stays the one that already ships: table section -> tab waiter
-- -> round -> tip. Nothing new is written onto the invoice here, and no second
-- order, payment or stock table is introduced.
--
-- The column is nullable and additive. Every table that existed before this
-- migration reads back as "no section", which is exactly how an unassigned
-- floor behaves today, so Restaurant, Retail and clinic installs keep working
-- with no data change and no behaviour change until the switch is on.
--
-- No foreign key to db_users on purpose: staff come and go, and a deleted
-- waiter must not delete or orphan the table definition they used to hold.

SET @db := DATABASE();

-- ── The waiter who owns the table ───────────────────────────────────────────
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_tables' AND COLUMN_NAME='waiter_user_id')=0,
  'ALTER TABLE db_tables ADD COLUMN `waiter_user_id` INT NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- The floor board and the section summary both ask "what is this waiter
-- sitting behind", filtered by the store they work in.
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_tables' AND INDEX_NAME='idx_tables_waiter')=0,
  'ALTER TABLE db_tables ADD INDEX `idx_tables_waiter` (`store_id`,`waiter_user_id`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
