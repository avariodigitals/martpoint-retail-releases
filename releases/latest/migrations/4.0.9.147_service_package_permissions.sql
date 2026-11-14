-- ============================================================================
-- 4.0.9.147 — Permission keys that existed only in the Roles screen
--
-- Two keys were listed in the Roles form but never seeded into any role, so
-- the screens behind them answered with a silent access-denied page:
--
--   * service_packages_* — no standard role could open /service_packages/add.
--     Granted to every role that already works with Services (the natural
--     parent capability).
--   * opening_positions_delete — the new delete action on Opening Positions,
--     granted to whoever may already review those positions (deleting only
--     works before anything posts, so it is the same level of trust as
--     rejecting).
--
-- Both grants are idempotent and never re-add a key an admin revoked.
-- ============================================================================

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

INSERT IGNORE INTO `db_permissions` (`store_id`, `role_id`, `permissions`)
SELECT p.`store_id`, p.`role_id`, s.`permissions`
FROM (SELECT DISTINCT `store_id`, `role_id` FROM `db_permissions` WHERE `permissions` = 'services_view') p
JOIN (
  SELECT 'service_packages_view' AS `permissions`
  UNION ALL SELECT 'service_packages_add'
  UNION ALL SELECT 'service_packages_edit'
  UNION ALL SELECT 'service_packages_delete'
) s
LEFT JOIN `db_permission_revocations` rv
  ON rv.`store_id` = p.`store_id` AND rv.`role_id` = p.`role_id` AND rv.`permissions` = s.`permissions`
LEFT JOIN `db_permissions` have
  ON have.`store_id` = p.`store_id` AND have.`role_id` = p.`role_id` AND have.`permissions` = s.`permissions`
WHERE rv.`id` IS NULL AND have.`id` IS NULL;

-- Opening positions gain a delete action, held by whoever may already review
-- them: deleting is only possible before anything posts, so it sits at the same
-- level of trust as rejecting. Same idempotent, revocation-aware grant.
INSERT IGNORE INTO `db_permissions` (`store_id`, `role_id`, `permissions`)
SELECT DISTINCT p.`store_id`, p.`role_id`, 'opening_positions_delete'
FROM `db_permissions` p
LEFT JOIN `db_permission_revocations` rv
  ON rv.`store_id` = p.`store_id` AND rv.`role_id` = p.`role_id` AND rv.`permissions` = 'opening_positions_delete'
LEFT JOIN `db_permissions` have
  ON have.`store_id` = p.`store_id` AND have.`role_id` = p.`role_id` AND have.`permissions` = 'opening_positions_delete'
WHERE p.`permissions` = 'opening_positions_review'
  AND rv.`id` IS NULL AND have.`id` IS NULL;
