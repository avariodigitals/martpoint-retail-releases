-- ============================================================
-- 4.0.9.70 — Physiotherapy Stage 5: inpatient care + closure
-- Wards/beds/occupancy, admissions, transfers + porter tasks,
-- nursing tasks/notes, meals, leave, versioned daily rates,
-- retry-safe daily charges, external referrals, discharge and
-- deceased handling. All statements guarded / idempotent.
-- ============================================================

CREATE TABLE IF NOT EXISTS `db_wards` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `branch_id` int DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `code` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ward` (`store_id`,`name`),
  KEY `ix_ward_store` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_beds` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `ward_id` int NOT NULL,
  `bed_label` varchar(60) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available', -- available|occupied|held|maintenance
  `daily_rate` decimal(15,2) DEFAULT NULL,          -- overrides the versioned bed rate
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bed` (`ward_id`,`bed_label`),
  KEY `ix_bed_store` (`store_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_admissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `branch_id` int DEFAULT NULL,
  `patient_id` int NOT NULL,
  `admission_code` varchar(30) NOT NULL,
  `reason` text DEFAULT NULL,
  `care_plan` text DEFAULT NULL,
  `clinician_user_id` int DEFAULT NULL,
  `admitted_by` int DEFAULT NULL,
  `admitted_at` datetime NOT NULL,
  `outpatient_decision` varchar(20) NOT NULL DEFAULT 'continue', -- continue|pause|replace
  `outpatient_decision_note` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active', -- active|discharged|deceased|transferred_out
  `invoice_id` int DEFAULT NULL,                  -- running db_sales bill
  `closed_at` datetime DEFAULT NULL,              -- actual departure/closure time
  `closed_by` int DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(30) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_adm_code` (`store_id`,`admission_code`),
  KEY `ix_adm_patient` (`store_id`,`patient_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_bed_occupancy` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `bed_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `from_at` datetime NOT NULL,
  `to_at` datetime DEFAULT NULL,                  -- NULL = currently occupied
  `open_reason` varchar(20) NOT NULL DEFAULT 'admission', -- admission|transfer|leave_return
  `close_reason` varchar(20) DEFAULT NULL,        -- transfer|discharge|deceased|leave_release
  `booked_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_occ_bed` (`bed_id`,`to_at`),
  KEY `ix_occ_adm` (`admission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_porter_tasks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `task_type` varchar(30) NOT NULL,               -- bed_transfer|discharge_move|deceased_move|other
  `ref_id` int DEFAULT NULL,                      -- transfer/discharge id
  `admission_id` int DEFAULT NULL,
  `patient_id` int DEFAULT NULL,
  `from_location` varchar(120) DEFAULT NULL,
  `to_location` varchar(120) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',   -- open|in_progress|completed|failed|cancelled
  `requested_by` varchar(50) DEFAULT NULL,
  `assigned_to` int DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `completed_by` int DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_ptask` (`store_id`,`status`,`task_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_bed_transfers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `from_bed_id` int NOT NULL,
  `to_bed_id` int NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `requested_by` varchar(50) DEFAULT NULL,
  `porter_task_id` int DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'requested', -- requested|completed|failed|cancelled
  `failure_note` varchar(255) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_transfer` (`store_id`,`admission_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_nursing_tasks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `task_code` varchar(40) NOT NULL,               -- morning_check|evening_check|weekly_review|adhoc:<id>
  `label` varchar(160) NOT NULL,
  `task_date` date NOT NULL,
  `due_at` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',   -- open|done|suppressed|cancelled
  `done_at` datetime DEFAULT NULL,
  `done_by` int DEFAULT NULL,
  `result_note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ntask` (`admission_id`,`task_code`,`task_date`), -- retry-safe generation
  KEY `ix_ntask` (`store_id`,`status`,`due_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_nursing_notes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `task_id` int DEFAULT NULL,
  `note_type` varchar(20) NOT NULL,               -- observation|handover|review
  `shift` varchar(10) DEFAULT NULL,               -- morning|evening
  `body` text DEFAULT NULL,
  `recorded_by` varchar(50) DEFAULT NULL,
  `recorded_by_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_nnote` (`store_id`,`admission_id`,`note_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_meal_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `name` varchar(80) NOT NULL,
  `slot` varchar(20) NOT NULL DEFAULT 'any',      -- breakfast|lunch|dinner|any
  `charge` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mealtype` (`store_id`,`name`,`slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_meal_orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `meal_date` date NOT NULL,
  `slot` varchar(20) NOT NULL,
  `meal_type_id` int NOT NULL,
  `diet_note` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ordered', -- ordered|provided|skipped|cancelled
  `ordered_by` varchar(50) DEFAULT NULL,
  `provided_by` int DEFAULT NULL,
  `provided_at` datetime DEFAULT NULL,
  `charge_amount` decimal(15,2) NOT NULL DEFAULT 0.00, -- snapshot of type charge
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_meal` (`store_id`,`admission_id`,`meal_date`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_leave_records` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `expected_return` datetime DEFAULT NULL,
  `billing_policy` varchar(15) NOT NULL DEFAULT 'half', -- charge|half|none (snapshot)
  `bed_hold` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'requested', -- requested|approved|out|returned|cancelled
  `requested_by` varchar(50) DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `departed_at` datetime DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `overdue` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_leave` (`store_id`,`admission_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_daily_rates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `rate_code` varchar(30) NOT NULL,               -- bed|nursing|leave|other
  `name` varchar(120) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,               -- NULL = current version
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_rate` (`store_id`,`rate_code`,`effective_from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_daily_charges` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `charge_date` date NOT NULL,
  `charge_code` varchar(40) NOT NULL,             -- bed|nursing|leave|meal:<order_id>
  `description` varchar(255) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 1,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sales_id` int DEFAULT NULL,
  `sales_item_id` int DEFAULT NULL,
  `status` varchar(15) NOT NULL DEFAULT 'posted',
  `posted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_charge` (`admission_id`,`charge_date`,`charge_code`), -- one charge per rule per day
  KEY `ix_charge` (`store_id`,`admission_id`,`charge_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_external_referrals` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `admission_id` int DEFAULT NULL,
  `episode_id` int DEFAULT NULL,
  `destination` varchar(160) NOT NULL,
  `reason` text DEFAULT NULL,
  `urgency` varchar(15) NOT NULL DEFAULT 'routine', -- routine|urgent|emergency
  `requested_by` varchar(50) DEFAULT NULL,
  `handover_doc_id` int DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',   -- open|departed|procedure|returned|reviewed|cancelled
  `departed_at` datetime DEFAULT NULL,
  `procedure_status` varchar(255) DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `return_assessment_id` int DEFAULT NULL,
  `reviewed_by` varchar(50) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `exception_flag` tinyint(1) NOT NULL DEFAULT 0, -- emergency departure without handover docs
  `exception_note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_extref` (`store_id`,`patient_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_discharges` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `admission_id` int NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'recommended', -- recommended|approved|rejected|completed
  `recommended_by` int DEFAULT NULL,
  `recommended_at` datetime DEFAULT NULL,
  `recommendation_note` varchar(255) DEFAULT NULL,
  `decided_by` int DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `decision_note` varchar(255) DEFAULT NULL,
  `discharge_reason` varchar(255) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `follow_up` varchar(255) DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `actual_discharge_at` datetime DEFAULT NULL,    -- approval alone never closes the admission
  `porter_task_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_discharge` (`store_id`,`admission_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `db_inpatient_policies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `policy_key` varchar(40) NOT NULL,
  `policy_value` varchar(160) DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_policy` (`store_id`,`policy_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Running inpatient invoice linkage
SET @sql := IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_sales' AND column_name='admission_id')=0,
  'ALTER TABLE `db_sales` ADD COLUMN `admission_id` int DEFAULT NULL AFTER `plan_id`, ADD KEY `ix_sales_adm` (`store_id`,`admission_id`)',
  'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Paused/replaced outpatient entitlements (admission package decisions)
-- status column is a plain varchar — no enum change needed.

-- ------------------------------------------------------------
-- Non-cash payment modes: wallet settlements and their negative
-- reversal rows must appear in payment reports but NEVER inflate
-- cash-in-hand / cashier-shift totals. Seeded for every store.
-- ------------------------------------------------------------
INSERT INTO db_payment_modes
  (store_id, code, name, description, enabled, is_default, is_system, sort_order,
   requires_reference, requires_confirmation, affects_cash_in_hand,
   icon_class, status, created_date, created_time, created_by)
SELECT s.id, 'patient_wallet', 'Patient Wallet',
       'Clinical wallet settlement — internal funds, not cash in hand.',
       0, 0, 1, 90, 0, 0, 0, 'fa-hospital-o', 1, CURDATE(), CURTIME(), 'system'
FROM db_store s
WHERE NOT EXISTS (
  SELECT 1 FROM db_payment_modes pm WHERE pm.store_id = s.id AND pm.code = 'patient_wallet'
);

INSERT INTO db_payment_modes
  (store_id, code, name, description, enabled, is_default, is_system, sort_order,
   requires_reference, requires_confirmation, affects_cash_in_hand,
   icon_class, status, created_date, created_time, created_by)
SELECT s.id, 'wallet_reversal', 'Wallet Reversal',
       'Correcting negative wallet settlement — reopens the receivable.',
       0, 0, 1, 91, 0, 0, 0, 'fa-undo', 1, CURDATE(), CURTIME(), 'system'
FROM db_store s
WHERE NOT EXISTS (
  SELECT 1 FROM db_payment_modes pm WHERE pm.store_id = s.id AND pm.code = 'wallet_reversal'
);

-- Stores created before payment modes existed get the base cash mode so
-- cash-in-hand reporting works at all (physio dev store was missing all).
INSERT INTO db_payment_modes
  (store_id, code, name, description, enabled, is_default, is_system, sort_order,
   requires_reference, requires_confirmation, affects_cash_in_hand,
   icon_class, status, created_date, created_time, created_by)
SELECT s.id, 'cash', 'Cash', 'Cash in hand', 1, 1, 1, 1, 0, 0, 1, 'fa-money', 1,
       CURDATE(), CURTIME(), 'system'
FROM db_store s
WHERE NOT EXISTS (
  SELECT 1 FROM db_payment_modes pm WHERE pm.store_id = s.id AND pm.code = 'cash'
);

-- Default inpatient policies for physiotherapy stores (tenant-overridable;
-- seeded only where absent).
INSERT INTO db_inpatient_policies (store_id, policy_key, policy_value, updated_by, updated_at)
SELECT p.store_id, k.policy_key, k.policy_value, 'system', NOW()
FROM (SELECT DISTINCT store_id FROM db_store_industry_settings WHERE industry_type='physiotherapy_rehabilitation'
      UNION SELECT DISTINCT store_id FROM db_store_business_profile WHERE industry_type='physiotherapy_rehabilitation') p
JOIN (
  SELECT 'billing_boundary' AS policy_key, 'calendar' AS policy_value
  UNION ALL SELECT 'admission_day_charge','full'
  UNION ALL SELECT 'discharge_day_charge','none'
  UNION ALL SELECT 'leave_billing','half'
  UNION ALL SELECT 'morning_due','08:00'
  UNION ALL SELECT 'evening_due','20:00'
  UNION ALL SELECT 'auto_settle_wallet','1'
) k
WHERE NOT EXISTS (
  SELECT 1 FROM db_inpatient_policies ip WHERE ip.store_id = p.store_id AND ip.policy_key = k.policy_key
);

-- A non-sellable service item so daily-charge invoice lines join cleanly
-- in item-level sales reports (db_salesitems.item_id is NOT NULL).
INSERT INTO db_items (store_id, item_code, item_name, service_bit, not_for_sale,
                      category_id, unit_id, sales_price, price, purchase_price,
                      profit_margin, tax_id, status, system_ip, system_name,
                      created_by, created_date, created_time)
SELECT p.store_id, 'INP-DAILY', 'Inpatient daily charge', 1, 1,
       NULL, NULL, 0, 0, 0, 0, NULL, 1, 'system', 'migration', 'system',
       CURDATE(), CURTIME()
FROM (SELECT DISTINCT store_id FROM db_store_industry_settings WHERE industry_type='physiotherapy_rehabilitation'
      UNION SELECT DISTINCT store_id FROM db_store_business_profile WHERE industry_type='physiotherapy_rehabilitation') p
WHERE NOT EXISTS (
  SELECT 1 FROM db_items i WHERE i.store_id = p.store_id AND i.item_code = 'INP-DAILY'
);

-- Default meal types for physiotherapy stores (tenant editable).
INSERT INTO db_meal_types (store_id, name, slot, charge, status)
SELECT p.store_id, m.name, m.slot, m.charge, 1
FROM (SELECT DISTINCT store_id FROM db_store_industry_settings WHERE industry_type='physiotherapy_rehabilitation'
      UNION SELECT DISTINCT store_id FROM db_store_business_profile WHERE industry_type='physiotherapy_rehabilitation') p
JOIN (
  SELECT 'Standard meal' AS name, 'any' AS slot, 0.00 AS charge
  UNION ALL SELECT 'Therapeutic diet', 'any', 0.00
  UNION ALL SELECT 'Light / fluids only', 'any', 0.00
) m
WHERE NOT EXISTS (
  SELECT 1 FROM db_meal_types mt WHERE mt.store_id = p.store_id AND mt.name = m.name AND mt.slot = m.slot
);
