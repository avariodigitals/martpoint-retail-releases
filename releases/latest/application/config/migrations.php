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
];
