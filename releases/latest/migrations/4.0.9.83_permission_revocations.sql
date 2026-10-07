-- ============================================================
-- 4.0.9.83 — Permission revocation ledger
-- Additive physio role sync must never resurrect a grant an
-- administrator deliberately removed. When a role's permission
-- set is edited (Roles_model::save re-builds it by delete-then-
-- insert), the removed grant keys are recorded here; the
-- additive syncs (sync_physio_role_permissions, owner/admin syncs)
-- skip any key present in this ledger.
-- ============================================================

CREATE TABLE IF NOT EXISTS `db_permission_revocations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `role_id` int NOT NULL,
  `permissions` varchar(100) NOT NULL,
  `revoked_by` varchar(50) DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_revocation` (`store_id`,`role_id`,`permissions`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
