-- 4.0.9.192 — restore legacy POS items whose service_bit is NULL.
--
-- Some existing fleets have ordinary products with a NULL service_bit. The
-- POS runtime treats NULL as a physical item, so this marker delivers the
-- matching runtime condition through the normal updater. No data is changed.
SELECT 1;
