-- ============================================================
-- 4.0.9.140 — Admin & System screens become real permissions
--
-- These screens were reachable by any store-owner-class role
-- (is_store_admin() matched BUSINESS OWNER), regardless of what
-- the Roles screen had switched off. They are now gated on their
-- own permission keys, and those keys default to administrators
-- only: ADMIN / STORE ADMIN / SUPER ADMIN.
--
-- Three steps, all idempotent:
--   1. make sure the revocation ledger exists,
--   2. move the flags off every non-administrator role (recording
--      the removal so the additive syncs never re-add them),
--   3. grant the flags to administrator roles that lack them.
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

-- 2a. Record the removals BEFORE deleting them. The owner/admin syncs are
--     additive and run on page load, so without these rows the flags would be
--     handed straight back on the next request.
INSERT IGNORE INTO `db_permission_revocations`
  (`store_id`, `role_id`, `permissions`, `revoked_by`, `revoked_at`)
SELECT COALESCE(p.`store_id`, r.`store_id`, 0), p.`role_id`, p.`permissions`,
       'migration-4.0.9.140', NOW()
FROM `db_permissions` p
JOIN `db_roles` r ON r.`id` = p.`role_id`
WHERE UPPER(TRIM(r.`role_name`)) NOT IN ('ADMIN', 'STORE ADMIN', 'SUPER ADMIN')
  AND p.`permissions` IN (
    'store_profile','business_profile',
    'warehouse_add','warehouse_view',
    'roles_view',
    'send_sms','sms_template_view',
    'site_settings','data_migration',
    'license_management','subscription_plans',
    'sms_api_view','smtp_settings',
    'paystack_settings','monnify_settings',
    'database_backup','system_updates','permission_audit',
    'countries_view','states_view','cities_view',
    'nin_usage','nin_logs'
  );

-- 2b. Now remove them from those roles.
DELETE p
FROM `db_permissions` p
JOIN `db_roles` r ON r.`id` = p.`role_id`
WHERE UPPER(TRIM(r.`role_name`)) NOT IN ('ADMIN', 'STORE ADMIN', 'SUPER ADMIN')
  AND p.`permissions` IN (
    'store_profile','business_profile',
    'warehouse_add','warehouse_view',
    'roles_view',
    'send_sms','sms_template_view',
    'site_settings','data_migration',
    'license_management','subscription_plans',
    'sms_api_view','smtp_settings',
    'paystack_settings','monnify_settings',
    'database_backup','system_updates','permission_audit',
    'countries_view','states_view','cities_view',
    'nin_usage','nin_logs'
  );

-- 3. Administrators keep the screens. Skip anything a ledger row says was
--    deliberately revoked, so this migration never overrides a decision.
INSERT INTO `db_permissions` (`store_id`, `role_id`, `permissions`)
SELECT COALESCE(r.`store_id`, 0), r.`id`, k.`perm`
FROM `db_roles` r
JOIN (
    SELECT 'store_profile'      AS `perm` UNION ALL
    SELECT 'business_profile'            UNION ALL
    SELECT 'warehouse_add'               UNION ALL
    SELECT 'warehouse_view'              UNION ALL
    SELECT 'roles_view'                  UNION ALL
    SELECT 'send_sms'                    UNION ALL
    SELECT 'sms_template_view'           UNION ALL
    SELECT 'site_settings'               UNION ALL
    SELECT 'data_migration'              UNION ALL
    SELECT 'license_management'          UNION ALL
    SELECT 'subscription_plans'          UNION ALL
    SELECT 'sms_api_view'                UNION ALL
    SELECT 'smtp_settings'               UNION ALL
    SELECT 'paystack_settings'           UNION ALL
    SELECT 'monnify_settings'            UNION ALL
    SELECT 'database_backup'             UNION ALL
    SELECT 'system_updates'              UNION ALL
    SELECT 'permission_audit'            UNION ALL
    SELECT 'countries_view'              UNION ALL
    SELECT 'states_view'                 UNION ALL
    SELECT 'cities_view'                 UNION ALL
    SELECT 'nin_usage'                   UNION ALL
    SELECT 'nin_logs'
) k
WHERE UPPER(TRIM(r.`role_name`)) IN ('ADMIN', 'STORE ADMIN', 'SUPER ADMIN')
  AND NOT EXISTS (
    SELECT 1 FROM `db_permissions` p
    WHERE p.`role_id` = r.`id` AND p.`permissions` = k.`perm`
  )
  AND NOT EXISTS (
    SELECT 1 FROM `db_permission_revocations` v
    WHERE v.`role_id` = r.`id` AND v.`permissions` = k.`perm`
  );
