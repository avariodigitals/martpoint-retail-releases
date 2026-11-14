-- Runtime repair: graceful Central messaging setup and industry-independent
-- messaging navigation. Normal upgrades must still apply the registered .208
-- migration first; this marker never advances or bypasses earlier migrations.
SELECT 1;
