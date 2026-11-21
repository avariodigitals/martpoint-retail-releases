-- One-time Fleet-side authorization intents for Central-brokered Meta OAuth.
-- The Meta code/token never appears in a browser URL; the handoff is signed
-- with the existing per-install Central/Fleet key and consumed once here.
CREATE TABLE IF NOT EXISTS `db_ai_ads_oauth_states` (
  `nonce` CHAR(48) NOT NULL,
  `store_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`nonce`),
  KEY `idx_ai_ads_oauth_store_created` (`store_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
