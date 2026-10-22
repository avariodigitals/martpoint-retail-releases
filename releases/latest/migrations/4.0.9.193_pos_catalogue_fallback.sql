-- 4.0.9.193 — make the desktop POS catalogue resilient on legacy fleets.
--
-- The runtime retries with core db_items columns if an optional schema/join
-- makes the rich catalogue query fail. It also treats parent_id=0 like NULL.
-- This marker is intentionally a no-op; no product or stock data is changed.
SELECT 1;
