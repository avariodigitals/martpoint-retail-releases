-- 4.0.9.177 — retire the generic Hardware & Materials storefront theme
-- Building Materials now uses Framework Atelier or Vector Systems. Existing
-- merchants keep their records; Theme_engine will resolve a retired selection
-- to the first allowed dedicated theme on the next request.
UPDATE `db_storefront_themes`
SET `status` = 0
WHERE `theme_key` = 'hardware';
