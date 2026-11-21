-- Status records returned to Meta after a signed data-deletion request.
-- The originating Meta user ID is deliberately not retained here.
CREATE TABLE IF NOT EXISTS `db_ai_ads_deletion_requests` (
  `confirmation_code` CHAR(40) NOT NULL,
  `completed_at` DATETIME NOT NULL,
  PRIMARY KEY (`confirmation_code`),
  KEY `idx_ai_ads_deletion_completed` (`completed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
