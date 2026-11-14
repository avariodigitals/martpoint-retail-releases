-- Business Owner is a separate, billable tenant role with explicit oversight
-- permissions. Keep it distinct from the non-billable system Store Admin role.
-- Respect any deliberate permission revocation already recorded by an admin.
INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT r.store_id, r.id, 'license_management'
FROM db_roles r
WHERE UPPER(r.role_name) = 'BUSINESS OWNER'
  AND NOT EXISTS (
    SELECT 1 FROM db_permissions p
    WHERE p.store_id = r.store_id
      AND p.role_id = r.id
      AND p.permissions = 'license_management'
  )
  AND NOT EXISTS (
    SELECT 1 FROM db_permission_revocations rv
    WHERE rv.store_id = r.store_id
      AND rv.role_id = r.id
      AND rv.permissions = 'license_management'
  );
