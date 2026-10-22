-- 4.0.9.190 — category-tree release repair marker.
--
-- The corresponding runtime repair is delivered in the PHP release payload:
-- application/helpers/category_tree_helper.php and
-- application/models/Category_tree_model.php.
-- Keep this migration harmless and idempotent; it exists to move existing
-- fleets from .189 to the repaired release so their updater will apply the
-- PHP files.
SELECT 1;
