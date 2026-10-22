-- 4.0.9.191 — POS legacy-fleet compatibility marker.
--
-- The runtime repair is intentionally in the PHP payload: older fleets may
-- not have the optional service-staff, expiry-settings, item, or owner-link
-- extensions. This idempotent marker advances those fleets through the normal
-- updater so the compatibility guards are delivered without reinstalling or
-- deleting tenant data.
SELECT 1;
