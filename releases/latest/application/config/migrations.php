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
          '4.0.9.127v'  => '4.0.9.127_physio_and_fleet_recovery.sql',
          '4.0.9.128v'  => '4.0.9.128_provisioned_admin_login.sql',
          '4.0.9.129v'  => '4.0.9.129_fleet_delivery_and_import_navigation.sql',
          '4.0.9.130v'  => '4.0.9.130_physio_bed_accounts.sql',
          '4.0.9.131v'  => '4.0.9.131_oil_plant_quotation_sections.sql',
          '4.0.9.132v'  => '4.0.9.132_plant_manual_quote_lines.sql',
          '4.0.9.133v'  => '4.0.9.133_physio_system_accounts.sql',
          '4.0.9.134v'  => '4.0.9.134_lagos_location_repair.sql',
          '4.0.9.135v'  => '4.0.9.135_physio_final_assessment.sql',
          '4.0.9.136v'  => '4.0.9.136_physio_user_signatures.sql',
          '4.0.9.137v'  => '4.0.9.137_skincare_reference_themes.sql',
          '4.0.9.138v'  => '4.0.9.138_skincare_appointment_channel.sql',
          // .139 Fleet enrol columns — the fleet_key no longer travels in the
          // (public) release manifest, so installs pick it up out of band via
          // install_key. This guarantees the three columns exist; the code
          // guards are field_exists() so a missing column is a silent skip.
          '4.0.9.139v'  => '4.0.9.139_fleet_enrol_columns.sql',
          // .140 Admin & System screens become real permissions. Site Settings,
          // System Update, Permission Audit and the other technical screens were
          // reachable by any owner-class role via is_store_admin(); they are now
          // gated on their own keys, granted to administrators only.
          '4.0.9.140v'  => '4.0.9.140_admin_system_permission_flags.sql',
          // .141 Visit history reads as plain English. Strips the duplicated
          // "<username> — " prefix from stored intake notes (the actor is now
          // shown in its own "by ..." line as a first name) and sentence-cases
          // the boilerplate handover note.
          '4.0.9.141v'  => '4.0.9.141_visit_history_plain_english.sql',
          // .142 Care Queue rework — the therapist's mandatory completion
          // remark (assessment_remark / recommended_amount /
          // recommended_sessions) and the db_sales.encounter_id link that ties
          // a check-in bill back to its visit.
          '4.0.9.142v'  => '4.0.9.142_care_queue_billing.sql',
          // .143 Referral partners — the lab/hospital/doctor directory a
          // therapist picks from, the per-patient referral to one of them, and
          // the dated meeting log that records what happened at the partner.
          '4.0.9.143v'  => '4.0.9.143_physio_referral_partners.sql',
          // .144 Care Queue visit type. The waiting-for-nurse desk decides
          // whether a visit is an assessment or a treatment session, which
          // forks the journey (assessment goes through Finance, a treatment
          // session closes straight from the therapist) and records the nurse
          // tasked with a treatment patient.
          '4.0.9.144v'  => '4.0.9.144_care_queue_visit_type.sql',
          // .145 Care type at billing. The physio marks the bill inpatient or
          // outpatient when raising it; the mark lives on the bill and on the
          // care episode, so a patient's history reads as a series of spells
          // instead of a status that gets converted.
          '4.0.9.145v'  => '4.0.9.145_care_type_billing.sql',
          // .146 Rooms and room-class daily rates. Wards gain physical rooms,
          // each room belongs to a price category (Private AC, Semi-Private,
          // General...), and the daily bed charge prices from that category.
          // Existing installs keep billing on the facility-wide bed rate until
          // rooms are organised.
          '4.0.9.146v'  => '4.0.9.146_physio_rooms_and_rate_classes.sql',
          // .147 Service package permissions reach existing roles. The keys
          // were listed in the Roles screen but never seeded into any role, so
          // /service_packages/add answered with a silent access-denied.
          '4.0.9.147v'  => '4.0.9.147_service_package_permissions.sql',
          // .148 Referral configuration. Referring a patient stops meaning
          // free-typing: reasons and urgency statuses become the store's own
          // editable lists (dropdown with an "Other" write-in), a notable set
          // of hospitals is seeded as the starting directory, and the partner
          // table grows source_system/external_key so a shared-directory sync
          // can land later without a rebuild.
          '4.0.9.148v'  => '4.0.9.148_referral_options_and_seeds.sql',
          // .149 Treatment plan from the visit. The physiotherapist's
          // assessment recommendation now lands as a real treatment plan
          // linked back to the visit (db_treatment_plans.encounter_id), so
          // the Treatment Plans screen shows what was prescribed and a
          // re-saved remark amends that one plan instead of duplicating it.
          '4.0.9.149v'  => '4.0.9.149_treatment_plan_encounter.sql',
          // .150 Delivery fee reconciliation. The classic POS wrote a round_off
          // that swallowed the discount, coupon and delivery fee, and the fee
          // itself sits on db_sales rather than on any item line — so the
          // printed breakdown never added up to the grand total (receipt
          // 22,500 vs 17,500 of goods). Rewrites db_sales.round_off only;
          // grand_total and paid_amount are never touched, so no money moves.
          '4.0.9.150v'  => '4.0.9.150_shipping_round_off_repair.sql',
          // .151 Admissions belong to a care episode. A care episode is one
          // spell of care under one type, and the patient profile already
          // groups history that way — but an admission never said which spell
          // it was, so the ward record and the episode record described the
          // same stay without meeting. Adds db_admissions.episode_id plus
          // treatment_plan_id, which replaces the care_plan free-text box
          // (typed once, shown once, read by nothing). Existing admissions are
          // backfilled onto the episode that was running when the patient was
          // admitted.
          '4.0.9.151v'  => '4.0.9.151_admission_care_episode.sql',
          // .152 The treatment chart a physio signs for each session
          // (db_session_treatment_notes, one signed row per booked session),
          // plus two grants the Care Queue review asked for: sessions_note_add
          // for whoever completes a session, and appointments_reschedule split
          // out of appointments_edit so re-dating a booking is the desk's call,
          // not every login that can edit one.
          '4.0.9.152v'  => '4.0.9.152_session_treatment_notes.sql',
          // .153 The chart names the physio who gave the session, separately
          // from the one who typed it, and credits whoever amends it later —
          // because whoever is on duty may be covering someone else's course.
          '4.0.9.153v'  => '4.0.9.153_treatment_chart_therapist.sql',
          // Run on already-updated fleets too; this column was previously unregistered.
          '4.0.9.154v' => '4.0.9.154_physio_document_secret.sql',
          '4.0.9.155v' => '4.0.9.155_garden_foundation.sql',
          '4.0.9.156v' => '4.0.9.156_service_schedules.sql',
          '4.0.9.157v' => '4.0.9.157_service_project_context.sql',
          '4.0.9.158v' => '4.0.9.158_service_fulfilment_sources.sql',
          '4.0.9.159v' => '4.0.9.159_garden_project_workbench.sql',
          '4.0.9.160v' => '4.0.9.160_garden_production.sql',
          '4.0.9.161v' => '4.0.9.161_branch_addresses.sql',
          // .162 An online or table-QR order now belongs to one outlet, so a
          // customer ordering from one of several locations actually orders
          // from that location — it is the premise for per-outlet stock,
          // kitchen routing and outlet reports, not a reporting nicety.
          '4.0.9.162v' => '4.0.9.162_online_order_locations.sql',
          // .163/.164/.165 are the storefront content, journal and category
          // hierarchy changes already present on disk. Keep them in the
          // canonical chain so existing fleets receive the same storefront
          // schema as fresh installs.
          '4.0.9.163v' => '4.0.9.163_storefront_theme_content.sql',
          '4.0.9.164v' => '4.0.9.164_storefront_journal.sql',
          '4.0.9.165v' => '4.0.9.165_shared_category_hierarchy.sql',
          // .166 One lifecycle for every channel that takes an order, and a
          // written record of each move. Counter, Online Store and table QR
          // orders are now driven by the same rules instead of two status
          // columns that could contradict each other. Numbered past the
          // storefront migrations already on disk so no install can mark one
          // applied and silently skip the other.
          '4.0.9.166v' => '4.0.9.166_order_status_log.sql',
          // .167 The storefront menu becomes merchant-configured. nav_json on
          // db_storefront_settings holds the menu tree (pages, categories,
          // custom links, one level of submenus); empty means the automatic
          // All-Products-plus-six-categories rail stays as it was.
          '4.0.9.167v' => '4.0.9.167_storefront_nav_menu.sql',
          '4.0.9.168v' => '4.0.9.168_restaurant_order_engine.sql',
          '4.0.9.169v' => '4.0.9.169_production_exact_once.sql',
		  // .170 M3 cashier, delivery and offline replay controls. Keep this in
		  // the canonical registry so existing installs receive the controls
		  // through the normal updater rather than only through test fixtures.
		  '4.0.9.170v' => '4.0.9.170_restaurant_m3_controls.sql',
		  // .171 adds the shared WhatsApp/Instagram/Messenger conversation log
		  // used by the omnichannel workspace. It is additive and idempotent.
		  '4.0.9.171v' => '4.0.9.171_messaging_channels.sql',
		  '4.0.9.172v' => '4.0.9.172_restaurant_food_dispositions.sql',
		  '4.0.9.173v' => '4.0.9.173_weight_pricing.sql',
		  // .174 registers the two building-materials themes without touching
		  // any existing theme selection. .175 adds the B2B selling mode,
		  // specification tables, editorial kinds and product documents they
		  // rely on. Additive and idempotent.
		  '4.0.9.174v' => '4.0.9.174_building_materials_themes.sql',
		  '4.0.9.175v' => '4.0.9.175_storefront_b2b.sql',
		  // .176 hardens manual invoice receipts with a store-scoped
		  // idempotency key. Additive; legacy callers are unchanged.
		  '4.0.9.176v' => '4.0.9.176_sales_payment_idempotency.sql',
		  // .177 retires the generic Hardware & Materials storefront entry now
		  // that Building Materials has two dedicated catalogue themes.
		  '4.0.9.177v' => '4.0.9.177_retire_hardware_theme.sql',
		  // .178 adds the optional prescription attachment consumed by pharmacy
		  // storefront checkout; it remains additive for all other stores.
		  '4.0.9.178v' => '4.0.9.178_pharmacy_online_prescription.sql',
		  '4.0.9.179v' => '4.0.9.179_uploadable_html_themes.sql',
		  // .180 is an opt-in-only partner catalogue/fulfilment flag. Existing
		  // storefronts remain first-party by default.
		  '4.0.9.180v' => '4.0.9.180_partner_marketplace_flag.sql',
		  '4.0.9.181v' => '4.0.9.181_building_project_procurement.sql',
		  '4.0.9.182v' => '4.0.9.182_building_project_delivery.sql',
		  '4.0.9.183v' => '4.0.9.183_building_project_installation.sql',
		  '4.0.9.184v' => '4.0.9.184_building_project_finance.sql',
		  '4.0.9.185v' => '4.0.9.185_building_project_after_sales.sql',
		  // .186 exposes pharmacy branches and controlled partner/pickup hubs to
		  // the public store while retaining the actual stock source separately.
		  '4.0.9.186v' => '4.0.9.186_pharmacy_fulfilment_locations.sql',
		  // .187 connects public prescription requests to the existing
		  // pharmacy medical-notes queue and records review state separately.
		  '4.0.9.187v' => '4.0.9.187_pharmacy_online_workflow.sql',
		  // .188 adds an optional footer-only logo for the shared retail
		  // storefront shell; existing header branding remains unchanged.
		  '4.0.9.188v' => '4.0.9.188_storefront_footer_logo.sql',
		  // .189 controls sale and stock labels without changing stock or price
		  // authority. It defaults on for existing and new stores.
		  '4.0.9.189v' => '4.0.9.189_sale_stock_labels.sql',
		  // .190 republishes the category-tree runtime files in the release so
		  // fleets that received an incomplete prior overlay can recover safely.
		  // The SQL marker is intentionally a no-op; the repair is in the PHP
		  // payload and must still advance existing stores past .189.
		  '4.0.9.190v' => '4.0.9.190_category_tree_release_repair.sql',
		  // .191 hardens POS startup for legacy fleets whose optional tables or
		  // newer item columns are not installed yet. Runtime guards are in the
		  // PHP payload; this marker delivers them through the normal updater.
		  '4.0.9.191v' => '4.0.9.191_pos_legacy_compatibility.sql',
		  // .192 keeps ordinary legacy items with a NULL service_bit visible in
		  // POS. Runtime-only repair; no product data is modified.
		  '4.0.9.192v' => '4.0.9.192_pos_sellable_items_null_fix.sql',
		  // .193 retries the POS catalogue with core columns and accepts legacy
		  // parent_id=0 values, so a partially migrated fleet is not blank.
		  '4.0.9.193v' => '4.0.9.193_pos_catalogue_fallback.sql',
		  // .194 republishes the POS view with its partials and encodes imported
		  // text safely so client-side startup cannot be broken by invalid UTF-8.
		  '4.0.9.194v' => '4.0.9.194_pos_runtime_repair.sql',
		  '4.0.9.195v' => '4.0.9.195_audit_runtime_repair.sql',
		  // .196 corrects the legacy seeded install hostname at the client on
		  // its next authenticated heartbeat; no database schema change.
		  '4.0.9.196v' => '4.0.9.196_legacy_install_domain_cleanup.sql',
		  // Reserved versions only: unfinished payout centralisation and AI Growth
		  // are deferred, not part of the approved retail production upgrade.
		  // Original development SQL stays local; existing tables are untouched.
		  '4.0.9.197v' => '4.0.9.197_deferred_foundation_marker.sql',
		  '4.0.9.198v' => '4.0.9.198_deferred_foundation_marker.sql',
		  // .199 adds optional, store-scoped extra photos to storefront products.
		  '4.0.9.199v' => '4.0.9.199_storefront_product_gallery.sql',
		  // .200 separates the Business Owner permission role from Store Admin
		  // and counts Business Owner accounts as normal licensed tenant users.
		  '4.0.9.200v' => '4.0.9.200_business_owner_role_and_license_seats.sql',
		  // .201 adds optional counter/kitchen/bar routing to retail items. The
		  // default is counter, so existing stores keep their current workflow.
		  '4.0.9.201v' => '4.0.9.201_restaurant_fulfilment_routes.sql',
		  // .202 adds store-configurable digital/analogue weighing and makes
		  // catalogue descriptions visible in commercial and storefront flows.
		  // Settings are stored with the existing store settings infrastructure.
		  '4.0.9.202v' => '4.0.9.202_weight_scale_and_product_descriptions.sql',
		  // .203 fixes POS scale-mode persistence on legacy modular schemas and
		  // ensures stepped-weight prices are calculated exactly once on save.
		  '4.0.9.203v' => '4.0.9.203_weight_scale_persistence_and_pos_repair.sql',
		  // .204 honours configured POS quantity precision in inventory movement.
		  '4.0.9.204v' => '4.0.9.204_pos_decimal_quantities.sql',
          '4.0.9.205v' => '4.0.9.205_pharmacy_modes.sql',
          // .206 adds opt-in FEFO/FIFO batch allocation with auditable sale and return links.
          '4.0.9.206v' => '4.0.9.206_inventory_rotation.sql',
          '4.0.9.207v' => '4.0.9.207_legacy_customer_history.sql',
          '4.0.9.208v' => '4.0.9.208_messaging_credits.sql',
          // .209 republishes the full user-directory/role-selector repair and
          // resolves Store Admin by role identity rather than hard-coded ID 2.
          '4.0.9.209v' => '4.0.9.209_business_owner_directory.sql',
          '4.0.9.210v' => '4.0.9.210_messaging_setup_and_navigation.sql',
          '4.0.9.211v' => '4.0.9.211_standard_invoice_lifecycle.sql',
          '4.0.9.212v' => '4.0.9.212_retail_document_stability.sql',
          '4.0.9.213v' => '4.0.9.213_messaging_table_separation.sql',
          '4.0.9.214v' => '4.0.9.214_sales_render_and_login_runtime.sql',
          '4.0.9.215v' => '4.0.9.215_invoice_payments_and_setup_checkpoints.sql',
];
