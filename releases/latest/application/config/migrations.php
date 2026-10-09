<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MartPoint — CANONICAL MIGRATION REGISTRY (single source of truth)
 * ================================================================
 * Every schema change ships as a .sql file in updates/migrations/ and MUST be
 * registered here. Three consumers read this file:
 *
 *   1. Updates_model::index()          — runs the chain on existing installs.
 *   2. scripts/mp_release.php          — validates, builds, and packages releases.
 *   3. tools/check_migration_registry  — CI/pre-commit drift guard.
 *
 * RULES
 * -----
 * - Keys are versions; PHP casts numeric-looking string keys to int, so a key
 *   like '4.0.9' becomes int 4.0.9 (= 4.9 float-ish) and comparisons break.
 *   To keep this bulletproof EVERY key is written with a trailing letter, so it
 *   always stays a string. Use the canonical form:  '4.0.9.10v'.
 *   version_compare() treats '4.0.9.10v' as greater than '4.0.9.10' and less
 *   than '4.0.9.11', which is exactly the ordering we need.
 * - The ORDER of this array is the execution order. Keep it ascending.
 * - A version may map to a string (one file) or an array (several files).
 * - Never remove a key once shipped — clients use it to decide what to skip.
 *
 * The registry is intentionally a plain PHP array so it is byte-identical in
 * every environment and diffable in review.
 */
return [

	// ---------------------------------------------------------------
	// 4.0.x  — core retail schema
	// ---------------------------------------------------------------
	'4.0.0v'      => '3.0_to_4.0.0.sql',
	'4.0.1v'      => '4.0.0_to_4.0.1_purchase_batch.sql',
	'4.0.2v'      => '4.0.1_to_4.0.2.sql',
	'4.0.2.1v'    => '4.0.2_medical_notes.sql',
	'4.0.3v'      => '4.0.2_to_4.0.3_db_store_modularization.sql',
	'4.0.4v'      => '4.0.3_to_4.0.4.sql',
	'4.0.5v'      => '4.0.4_to_4.0.5.sql',
	'4.0.6v'      => '4.0.5_to_4.0.6.sql',
	'4.0.7v'      => '4.0.6_to_4.0.7_fashion_intelligence.sql',
	'4.0.7.1v'    => '4.0.7_attribute_driven_variants.sql',
	'4.0.8v'      => '4.0.8_featured_products.sql',

	// ---------------------------------------------------------------
	// 4.0.9 — compatibility + base extensions
	// ---------------------------------------------------------------
	'4.0.9v'      => '4.0.8_to_4.0.9_compatibility.sql',
	'4.0.9.1v'    => '4.0.8_to_4.0.9_holditems_staff_commission.sql',
	'4.0.9.2v'    => '4.0.9_to_4.0.9.2_promotions_loyalty.sql',
	'4.0.9.3v'    => '4.0.9.3_sku_limit.sql',
	'4.0.9.4v'    => '4.0.9_new_arrival_products.sql',
	'4.0.9.5v'    => '4.0.9_online_excluded.sql',
	'4.0.9.6v'    => '4.0.9.5_to_4.0.9.6_multi_unit_selling.sql',
	'4.0.9.7v'    => '4.0.9.6_to_4.0.9.7_multi_unit_variants.sql',
	'4.0.9.8v'    => '4.0.9.7_to_4.0.9.8_multi_unit_wholesale.sql',
	'4.0.9.9v'    => ['4.0.9.8_newsletter_subscribers.sql', '4.0.9.9_delivery_quote_pending.sql'],
	'4.0.9.10v'   => ['4.0.6_b2b_customer_warehouse.sql', '4.0.9.10_stock_state.sql'],
	'4.0.9.11v'   => '4.0.9.11_leads_crm.sql',
	'4.0.9.12v'   => '4.0.9.12_industry_modules.sql',
	'4.0.9.13v'   => '4.0.9.13_storefront_columns.sql',

	// Gap .14–.19: no file was ever shipped for these numbers. Do not invent keys.

	'4.0.9.20v'   => '4.0.9.20_marquee_setting.sql',
	// Gap .21
	'4.0.9.22v'   => '4.0.9.22_manual_shipping.sql',
	// Gap .23
	'4.0.9.24v'   => ['4.0.9.24_perfumery_module.sql', '4.0.9.24_audit_trail.sql'],
	'4.0.9.25v'   => '4.0.9.25_monnify_integration.sql',
	'4.0.9.26v'   => ['4.0.9.26_bottling_runs.sql', '4.0.9.26_parfum_themes.sql'],
	'4.0.9.27v'   => '4.0.9.27_bottling_sku_link.sql',
	'4.0.9.28v'   => '4.0.9.28_customer_notes.sql',
	'4.0.9.29v'   => '4.0.9.29_storefront_settings_columns.sql',
	'4.0.9.30v'   => '4.0.9.30_stock_adjustment_repair.sql',
	'4.0.9.31v'   => '4.0.9.31_stock_recalculation_repair.sql',
	'4.0.9.32v'   => '4.0.9.32_warehouse_stock_repair.sql',
	// Gaps .33–.34, .36–.37, .39–.42, .44–.45, .47–.49: never shipped.
	'4.0.9.35v'   => '4.0.9.35_assist_ai_settings.sql',
	'4.0.9.38v'   => '4.0.9.38_payment_account_columns.sql',

	// ---------------------------------------------------------------
	// 4.0.9.43+ — fleet, industry modules, physiotherapy
	// NOTE: .43/.46/.50/.51/.52/.54/.57/.58/.59 were previously MISSING from the
	// registry. They are restored here — without them the incident banner, fleet
	// sync, audit-trail toggle, storefront themes and order fulfilment never
	// reached existing installs. See docs for incident-banner contract.
	// ---------------------------------------------------------------
	'4.0.9.43v'   => '4.0.9.43_central_update_fleet.sql',
	'4.0.9.46v'   => '4.0.9.46_audit_trail_toggle.sql',
	'4.0.9.50v'   => '4.0.9.50_nigerian_cities.sql',
	'4.0.9.51v'   => ['4.0.9.51_fleet_payload_mediumtext.sql', '4.0.9.51_users_delete_permission.sql'],
	'4.0.9.52v'   => '4.0.9.52_makeup_studio_themes.sql',
	'4.0.9.53v'   => '4.0.9.53_nylon_polythene.sql',
	'4.0.9.54v'   => '4.0.9.54_scientific_equipment.sql',
	'4.0.9.55v'   => '4.0.9.55_city_shipping.sql',
	'4.0.9.56v'   => '4.0.9.56_skincare.sql',
	'4.0.9.57v'   => '4.0.9.57_verdant.sql',
	'4.0.9.58v'   => '4.0.9.58_order_fulfilment.sql',
	'4.0.9.59v'   => '4.0.9.59_status_incident.sql',

	// ---------------------------------------------------------------
	// 4.0.9.60+ — physiotherapy
	// ---------------------------------------------------------------
	'4.0.9.60v'   => '4.0.9.60_physiotherapy_foundation.sql',
	'4.0.9.61v'   => '4.0.9.61_physiotherapy_intake_queue.sql',
	'4.0.9.62v'   => '4.0.9.62_physiotherapy_clinical.sql',
	'4.0.9.63v'   => '4.0.9.63_tracker_consent.sql',
	'4.0.9.64v'   => '4.0.9.64_physiotherapy_plans_funds.sql',
	'4.0.9.65v'   => '4.0.9.65_feature_coverage_variants.sql',
	'4.0.9.66v'   => '4.0.9.66_customer_import.sql',
	'4.0.9.67v'   => '4.0.9.67_physiotherapy_stage4_hardening.sql',
	'4.0.9.68v'   => '4.0.9.68_payment_reconciliation.sql',
	'4.0.9.69v'   => '4.0.9.69_storefront_coupons.sql',
	'4.0.9.70v'   => '4.0.9.70_physiotherapy_inpatient.sql',
	'4.0.9.71v'   => '4.0.9.71_storefront_events_carts.sql',
	'4.0.9.72v'   => '4.0.9.72_physiotherapy_portal_feedback.sql',
	'4.0.9.73v'   => '4.0.9.73_storefront_phase2_hardening.sql',
	'4.0.9.74v'   => '4.0.9.74_physiotherapy_imports.sql',
	'4.0.9.75v'   => '4.0.9.75_phase3_segments_campaigns.sql',
	'4.0.9.76v'   => '4.0.9.76_physiotherapy_approval_binding.sql',
	'4.0.9.77v'   => '4.0.9.77_phase3_hardening.sql',
	'4.0.9.78v'   => '4.0.9.78_phase4_commerce.sql',
	'4.0.9.79v'   => '4.0.9.79_refund_traceability.sql',
	'4.0.9.80v'   => '4.0.9.80_public_booking.sql',
	'4.0.9.81v'   => '4.0.9.81_debt_reminder_pause.sql',
	'4.0.9.82v'   => '4.0.9.82_debt_reminder_delivery_mode.sql',
	'4.0.9.83v'   => '4.0.9.83_permission_revocations.sql',

	// ---------------------------------------------------------------
	// 4.0.9.84+ — printing (39 tables) + quotation lifecycle
	// ---------------------------------------------------------------
	'4.0.9.84v'   => '4.0.9.84_printing.sql',
	'4.0.9.85v'   => '4.0.9.85_printing_planning.sql',
	'4.0.9.86v'   => '4.0.9.86_printing_services.sql',
	'4.0.9.87v'   => '4.0.9.87_printing_material_issues.sql',
	'4.0.9.88v'   => '4.0.9.88_printing_quotation_link.sql',
	'4.0.9.89v'   => '4.0.9.89_printing_tax.sql',
	'4.0.9.90v'   => '4.0.9.90_printing_themes.sql',
	'4.0.9.91v'   => '4.0.9.91_printing_services_seed.sql',
	'4.0.9.92v'   => '4.0.9.92_printing_line_finance.sql',
	'4.0.9.93v'   => '4.0.9.93_quotation_lifecycle.sql',
	'4.0.9.94v'   => ['4.0.9.94_quotation_email_templates.sql', '4.0.9.94_quote_response_used_link.sql'],
	'4.0.9.95v'   => '4.0.9.95_printing_homepage_sections.sql',
	// ORDER MATTERS: print_works_catalogue_mode adds db_storefront_settings.catalogue_mode,
	// which add_allow_products_online reads in its UPDATE. Reversing these two fails.
	'4.0.9.96v'   => ['4.0.9.96_print_works_catalogue_mode.sql', '4.0.9.96_add_allow_products_online.sql'],
	'4.0.9.97v'   => '4.0.9.97_printing_storefront.sql',
	'4.0.9.98v'   => '4.0.9.98_service_deposit_columns.sql',
	'4.0.9.99v'   => '4.0.9.99_quotation_response_links.sql',
	'4.0.9.100v'  => '4.0.9.100_quotation_response_email.sql',
	'4.0.9.101v'  => '4.0.9.101_quotation_response_schema_repair.sql',

	// Repairs tables that had no installer/migration origin (Sendchamp SMS,
	// storefront customer OTP + sessions). Found by the schema-coverage check.
	'4.0.9.102v'  => '4.0.9.102_orphan_table_repair.sql',

	// Fleet install update-stage columns — lets Central show WHERE an install
	// is stuck in its update, not just that it is outdated.
	'4.0.9.103v'  => '4.0.9.103_fleet_update_stage.sql',

	// Fleet command resume tracking — "Update now" is a multi-minute pipeline
	// that cannot finish in one request; 'resume' lets Central drive it to
	// completion instead of closing it as done after 90 seconds.
	'4.0.9.104v'  => '4.0.9.104_fleet_command_resume.sql',

	// Fleet failure reporting — Central could see an install was outdated but
	// not WHY. Failed backups and stuck migrations were only visible by
	// logging into the install.
	'4.0.9.105v'  => '4.0.9.105_fleet_failure_reporting.sql',

	// Fleet migration-progress reporting — a stalled migration chain blocks an
	// install for ever and was invisible from Central.
	'4.0.9.106v'  => '4.0.9.106_fleet_migration_progress.sql',

	// Printing physical operations. .107 is the machine register, counter
	// readings and the run-level machine confirmation columns; .108 is the
	// consumable issuance lifecycle, maintenance visits and the customer-owned
	// material custody ledger. Both are printing-scoped and idempotent.
	'4.0.9.107v'  => '4.0.9.107_printing_machines.sql',
	'4.0.9.108v'  => '4.0.9.108_printing_ops.sql',
	// .109 restores print_works as the fourth printing theme. .97 retired it
	// in favour of a three-theme set; that decision is reversed, so the theme
	// is switched back on. No store is moved back onto it — see the file.
	'4.0.9.109v'  => '4.0.9.109_restore_print_works_theme.sql',
	// .110 carries the service-led storefront nav and the Appearance
	// "What You Sell" control. No schema change beyond re-asserting
	// catalogue_mode and normalising unreadable values.
	'4.0.9.110v'  => '4.0.9.110_storefront_nav_catalogue.sql',
	// .111 makes the printing workspace reachable: /dashboard routes printing
	// stores to /printing, and the module gate no longer reads a flag key that
	// exists nowhere. Backfills production_workflow for printing stores.
	'4.0.9.111v'  => '4.0.9.111_printing_workspace_reachability.sql',
	// .112 gives the printing workspace a sidebar rail. The module was reachable
	// but unnavigable — no menu entry existed for any of its 13 screens.
	'4.0.9.112v'  => '4.0.9.112_printing_sidebar_rail.sql',
	// .113 ships the Machine Floor screens (machines, readings, maintenance,
	// consumables, customer custody). The tables and model existed since
	// .107/.108 but had no controller or view — only the suite used them.
	'4.0.9.113v'  => '4.0.9.113_printing_machine_floor.sql',
	// .114 restores the shared business menus on the printing rail, renames
	// Online Store to Leads Hub there, sends /pos to the new-job builder, and
	// makes Customer Materials answer "what came in / what remains".
	'4.0.9.114v'  => '4.0.9.114_printing_rail_and_pos.sql',
	// .115 Pass A: industry-aware licence labels, Operations off for printing,
	// Reports merged into Insights, Client Segments linked, Quick Job button.
	'4.0.9.115v'  => '4.0.9.115_industry_labels_and_menu_tidy.sql',
	// .116 Pass B: per-job invoice style (combined / detailed) plus the
	// Equipment Report. Adds db_print_jobs.invoice_style only.
	'4.0.9.116v'  => '4.0.9.116_print_invoice_style.sql',
	// .117 removes the Wholesale/Retail price switch from the sales screen,
	// which also removes it from the quotation→invoice path.
	'4.0.9.117v'  => '4.0.9.117_remove_wholesale_toggle.sql',
	// .118 completes the print quote→invoice write (the last piece of Pass B).
	'4.0.9.118v'  => '4.0.9.118_print_invoice_create.sql',
	// .119 Printing rail reorganisation — navigation only. Reorders into Daily
	// Work / Business Management, removes duplicate destinations, moves suppliers
	// and customer materials, renames Reports and Online Store.
	'4.0.9.119v'  => '4.0.9.119_printing_rail_reorg.sql',
	// .120 Rail fixes: restores the group toggle handlers .119 dropped (five
	// printing menus could not be opened), renames to Online Print Requests and
	// Settings, and sub-groups Marketing and the storefront group.
	'4.0.9.120v'  => '4.0.9.120_rail_fixes.sql',
	// .121 Dashboard licence labels (second hard-coded copy) + printing client
	// profile tabs.
	'4.0.9.121v'  => '4.0.9.121_dashboard_labels_and_client_profile.sql',
	// .122 HOTFIX — clients on .106 could not save ordinary sales at all:
	// "Duplicate entry '0' for key 'idx_quotation_sales_unique'". A blank
	// quotation field posted '' (coerced to 0) and the column carried DEFAULT 0,
	// so the one-to-one index added in .88 rejected every unlinked sale after
	// the first. Normalises stranded 0 links to NULL and drops DEFAULT 0.
	// Write path fixed in Sales_model::verify_save_and_update().
	'4.0.9.122v'  => '4.0.9.122_quotation_id_zero_repair.sql',
        // .123 Orphan-table repair: 15 tables the code queries were created only
        // by 4.0.9.43 / .53 / .54, all BELOW the installer stamp (4.0.9.59), so a
        // FRESH install never created them: the Nylon Factory and the equipment /
        // service-job screens failed with "Table doesn't exist", and the Central
        // fleet registry (db_fleet_installs / db_fleet_commands — which Central
        // itself needs, since it also installs at the stamp) plus institutional
        // customer contacts were silently absent.
        // CREATE TABLE IF NOT EXISTS only: a no-op on every existing install.
        '4.0.9.123v'  => '4.0.9.123_orphan_table_repair_fresh_install.sql',
        // .124 SMS / OTP module: db_bulksmsng (BulkSMSNigeria credentials) and
        // db_storefront_customer_otp.purpose, so OTP codes are scoped to the
        // flow that requested them (storefront / admin_login / pos_confirm /
        // generic). Idempotent — CREATE IF NOT EXISTS + information_schema
        // guarded ALTER, silent on re-run.
        '4.0.9.124v'  => '4.0.9.124_sms_otp_module.sql',
          // .125 Column repair — the COLUMN half of what .123 did for tables.
          // The installer stamps 4.0.9.59 and the runner resumes ABOVE it, so
          // ADD COLUMN statements in migrations <= .59 never reach a fresh
          // install. 41 columns were missing across 12 migrations: the whole
          // fleet callback (fleet_url/fleet_key/install_key/deploy_key), incident
          // banners, audit trail, storefront city shipping/testimonials/background,
          // online-order stock state and fulfilment, custom-order fields,
          // quotation revisions, and sales/purchase shipping links.
          // Every guard is information_schema-based, so existing installs are a
          // complete no-op.
          '4.0.9.125v'  => '4.0.9.125_fresh_install_column_repair.sql',
          // .126 Schema reconciliation — CODE fix (Updater.php now replays the
          // canonical CREATE TABLE IF NOT EXISTS before migrations, healing any
          // table that drifted out of an install) + ledger dedupe here (the
          // "187 of 123 applied" inflation). Safe and idempotent.
          '4.0.9.126v'  => '4.0.9.126_schema_reconciliation.sql',
];
