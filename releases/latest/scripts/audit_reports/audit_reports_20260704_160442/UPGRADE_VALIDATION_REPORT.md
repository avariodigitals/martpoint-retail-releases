# MartPoint Upgrade Validation Report

**Generated:** 2026-07-04 16:04:44

- **[high]** Fresh installer schema import failed
  - Root cause: Query was empty
  - Fix: Check db.txt syntax and installer permissions
- **[high]** Upgrade migration failed
  - Root cause: Row size too large. The maximum row size for the used table type, not counting BLOBs, is 8126. This includes storage overhead, check the manual. You have to change some columns to TEXT or BLOBs
  - Fix: Fix migration SQL syntax
- **[high]** Upgrade migration failed
  - Root cause: Row size too large. The maximum row size for the used table type, not counting BLOBs, is 8126. This includes storage overhead, check the manual. You have to change some columns to TEXT or BLOBs
  - Fix: Fix migration SQL syntax
