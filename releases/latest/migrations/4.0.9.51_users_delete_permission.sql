-- 4.0.9.51 — grant users_delete to owner-level roles that predate the key.
-- Business Owner / Admin / Store Admin / Partner defaults all include
-- users_delete, but roles created before it existed were never granted it,
-- and reseed_missing_permissions() skips roles that already have any rows.
-- Idempotent: NOT EXISTS guard makes re-runs a no-op.

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT r.store_id, r.id, 'users_delete'
FROM db_roles r
WHERE r.status = 1
  AND (
       UPPER(r.role_name) LIKE '%BUSINESS OWNER%'
    OR UPPER(r.role_name) LIKE '%ADMIN%'
    OR UPPER(r.role_name) LIKE '%PARTNER%'
  )
  AND NOT EXISTS (
      SELECT 1 FROM db_permissions p
      WHERE p.role_id = r.id AND p.permissions = 'users_delete'
  );
