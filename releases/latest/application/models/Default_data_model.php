<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Default Data Seeder for MartPoint Retail
 * Creates default roles, permissions, and expense categories
 * during installation or first setup.
 * 
 * Rules:
 * - Idempotent: checks before creating to avoid duplicates
 * - Safe for existing installations: skips if data already exists
 * - Does NOT modify sales, POS, stock, invoice, payment logic
 */
class Default_data_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
    }

    // ============================================================
    // MAIN ENTRY POINT
    // ============================================================

    /**
     * Create all default data for a store
     * @param int $store_id
     * @return array Results
     */
    public function seed_store_defaults($store_id = null) {
        if (empty($store_id)) {
            $store_id = get_current_store_id();
        }

        $results = array(
            'roles_created'     => 0,
            'permissions_created' => 0,
            'categories_created'  => 0,
            'errors'            => array()
        );

        // 1. Create default roles
        $role_results = $this->create_default_roles($store_id);
        $results['roles_created'] = $role_results['count'];
        if (!empty($role_results['errors'])) {
            $results['errors'] = array_merge($results['errors'], $role_results['errors']);
        }

        // 2. Create default expense categories
        $cat_results = $this->create_default_expense_categories($store_id);
        $results['categories_created'] = $cat_results['count'];
        if (!empty($cat_results['errors'])) {
            $results['errors'] = array_merge($results['errors'], $cat_results['errors']);
        }

        return $results;
    }

    // ============================================================
    // DEFAULT ROLES & PERMISSIONS
    // ============================================================

    /**
     * Create default roles with mapped permissions for a store
     */
    public function create_default_roles($store_id) {
        $results = array('count' => 0, 'errors' => array());

        // Define roles
        $roles_config = array(
            'Business Owner' => array(
                'description' => 'Store Owner / Founder / Managing Director. Full access except Super Admin.',
                'permissions' => $this->get_business_owner_permissions()
            ),
            'Partner' => array(
                'description' => 'Implementation / Setup Partner. Can configure business settings, users and master data.',
                'permissions' => $this->get_partner_permissions()
            ),
            'Manager' => array(
                'description' => 'Store Manager. Access to operations, no Users/Roles/Settings.',
                'permissions' => $this->get_manager_permissions()
            ),
            'Cashier' => array(
                'description' => 'Sales & Checkout Staff. POS and Sales only.',
                'permissions' => $this->get_cashier_permissions()
            ),
            'Accountant' => array(
                'description' => 'Finance & Accounts Officer. View sales/purchases, manage expenses & accounts.',
                'permissions' => $this->get_accountant_permissions()
            ),
            'Inventory Officer' => array(
                'description' => 'Stock & Inventory Staff. Manage items, purchases, stock transfers and adjustments. No POS/Sales, no FSTR reports.',
                'permissions' => $this->get_inventory_officer_permissions()
            ),
            'Production Supervisor' => array(
                'description' => 'Factory Supervisor. Approves stage output, QC, corrections and job completion.',
                'permissions' => $this->get_production_supervisor_permissions()
            ),
            'Production Operator' => array(
                'description' => 'Machine Operator. Reports output, rejects, scrap and waste per shift.',
                'permissions' => $this->get_production_operator_permissions()
            ),
            'Service Engineer' => array(
                'description' => 'Field / Service Engineer. Views customers and equipment, updates assigned service jobs and records calibration.',
                'permissions' => $this->get_service_engineer_permissions()
            )
        );

        foreach ($roles_config as $role_name => $config) {
            // Check if role already exists for this store
            $exists = $this->db->query(
                "SELECT id FROM db_roles WHERE store_id = ? AND UPPER(role_name) = UPPER(?)",
                array($store_id, $role_name)
            )->num_rows();

            if ($exists > 0) {
                continue; // Skip existing
            }

            // Insert role
            $role_data = array(
                'store_id'    => $store_id,
                'role_name'   => $role_name,
                'description' => $config['description'],
                'status'      => 1
            );

            if ($this->db->insert('db_roles', $role_data)) {
                $role_id = $this->db->insert_id();
                $results['count']++;

                // Assign permissions
                $perm_count = $this->assign_permissions($role_id, $store_id, $config['permissions']);
            } else {
                $results['errors'][] = "Failed to create role: {$role_name}";
            }
        }

        return $results;
    }

    /**
     * Assign permissions to a role
     */
    private function assign_permissions($role_id, $store_id, $permissions) {
        if (empty($permissions)) return 0;

        $batch = array();
        foreach ($permissions as $perm) {
            $batch[] = array(
                'store_id'    => $store_id,
                'role_id'     => $role_id,
                'permissions' => $perm
            );
        }

        if (!empty($batch)) {
            $this->db->insert_batch('db_permissions', $batch);
            return count($batch);
        }
        return 0;
    }

    /**
     * Re-seed missing default permissions for existing standard roles.
     * For each standard role (Business Owner, Manager, Cashier, Accountant, Inventory Officer),
     * adds any permissions from the default set that are not already in db_permissions.
     * This fixes roles that were created before new permissions were added.
     *
     * @param int $store_id
     * @return int Number of permissions added
     */
    public function reseed_missing_permissions($store_id = null) {
        if (empty($store_id)) {
            $store_id = get_current_store_id();
        }
        if (empty($store_id)) {
            return 0;
        }

        $added = 0;

        // Create any missing standard roles first (idempotent: skips existing)
        $this->create_default_roles($store_id);

        // Get all roles for this store (except Super Admin role id 1)
        $roles = $this->db->where('store_id', $store_id)->where('id !=', 1)->get('db_roles')->result();

        foreach ($roles as $role) {
            $default_perms = $this->get_role_default_permissions($role->role_name);
            if (empty($default_perms)) {
                continue; // Not a standard role
            }

            // Get existing permissions for this role
            $existing = $this->db->where('role_id', $role->id)->get('db_permissions')->result_array();
            $existing_keys = array_column($existing, 'permissions');

            // Do not overwrite existing role permissions (admin changes must persist).
            // Only seed defaults when the role has no permissions at all.
            if (!empty($existing_keys)) {
                continue;
            }

            // Find missing permissions
            $missing = array_diff($default_perms, $existing_keys);
            if (empty($missing)) {
                continue;
            }

            // Insert missing permissions
            $batch = array();
            foreach ($missing as $perm) {
                $batch[] = array(
                    'store_id'    => $store_id,
                    'role_id'     => $role->id,
                    'permissions' => $perm
                );
            }
            if (!empty($batch)) {
                $this->db->insert_batch('db_permissions', $batch);
                $added += count($batch);
            }
        }

        return $added;
    }

    /**
     * Clinical grant keys a "full control" role must hold.
     *
     * Store administrators (Admin / Business Owner / Store Admin) are meant to
     * have every capability — the per-role presets exist for the *staff* roles
     * beneath them. Clinical permissions are checked with physio_can(), which
     * deliberately has NO inv_userid 1/2 bypass, so an administrator whose role
     * was seeded from a retail preset holds none of the clinical keys and lands
     * on a clinic rail with no clinical screens at all (the clinic links are
     * gated on clinical grants). This is the exact key set the clinic rail and
     * clinical screens read.
     *
     * 'clinical_reports_view' / 'clinical_export' / 'clinical_cross_branch' are
     * included so the administrator can see every branch and every report.
     */
    public function get_clinical_admin_permissions() {
        return array(
            // Register, episodes, appointments and the care queue
            'patients_view','patients_add','patients_edit','patients_merge','patients_export',
            'episodes_view','episodes_add','episodes_edit','episodes_close',
            'appointments_view','appointments_add','appointments_edit','appointments_cancel',
            'care_queue_view','care_checkin',
            'vitals_view','vitals_add',
            // Encounters, assessments, investigations
            'encounters_view','encounters_add','encounters_finalize','encounters_amend',
            'assessments_view','assessments_add','assessments_finalize',
            'investigations_view','investigations_request',
            'investigations_result_enter','investigations_review',
            // Plans and treatment sessions
            'plans_view','plans_add','plans_amend',
            'sessions_view','sessions_checkin','sessions_complete',
            // Ward, nursing and portering
            'admissions_view','admissions_manage','beds_manage',
            'nursing_tasks_view','nursing_tasks_complete','nursing_notes_add',
            'porter_tasks_view','porter_tasks_complete',
            'referrals_view','referrals_manage',
            'meals_view','meals_manage',
            'leave_manage','leave_approve',
            'daily_billing_view','daily_billing_run',
            'deceased_record',
            'discharge_recommend','discharge_decide',
            // Documents, money and opening positions
            'patient_docs_view','patient_docs_upload','patient_docs_release','patient_docs_clinical_view',
            'patient_funds_view','patient_funds_add','payment_evidence_verify',
            'funds_adjust_request','refund_request',
            'patient_billing_view','patient_billing_add',
            'opening_positions_view','opening_positions_enter','opening_positions_review',
            // Experience, portal, templates and imports
            'patient_feedback_view','patient_feedback_manage','testimonial_publish',
            'portal_manage','assessment_templates_manage',
            'imports_view','imports_run','imports_rollback',
            // Clinical administration
            'clinical_reports_view','clinical_export','clinical_cross_branch',
            'md_authority',
        );
    }

    /**
     * Owner-level ADMINISTRATION grants for a store's owner role.
     *
     * Separate from the clinical set above. A store provisioned with its own
     * role preset (the clinic: Physiotherapist, Nurse, Receptionist ... Partner,
     * Business Owner) can end up with a Business Owner role that is outranked by
     * its own Partner role — on this install the clinic's Partner held
     * business_setup / approval_settings_edit / payment_modes_* while its
     * Business Owner held none of them, and no role held roles_view, so the
     * owner could not open Staff & permissions either.
     *
     * Every key below is held by an existing owner-class role on a known-good
     * store, so this invents no new vocabulary:
     *   store 2 'Business Owner' (32)  -> roles_view / roles_add / roles_edit
     *   store 1 'Store Admin'    (2)   -> smtp_settings, roles_*
     *   store 1/2 'Partner'      (36/38)-> business_setup, approval_settings_edit
     *   paystack_settings              -> held by the payment-configuring roles
     *
     * Deliberately EXCLUDED: roles_delete. Store 2's Business Owner does not hold
     * it either — deleting a role is a destructive act that stays with the
     * install-level admin.
     */
    public function get_store_owner_permissions() {
        return array(
            // Staff, roles and permissions
            'roles_view','roles_add','roles_edit',
            // Business setup, store profile, expiry rules
            'business_setup','store_view','expiry_settings',
            // Payments configuration
            'paystack_settings','payment_modes_view','payment_modes_add',
            'payment_modes_edit','payment_modes_delete',
            // Notifications, email and approval authority
            'smtp_settings','approval_settings_edit',
        );
    }

    /**
     * Give a store's owner role(s) the owner-administration grants.
     *
     * Additive and idempotent — inserts only what the role does not already hold,
     * never deletes, never overwrites an admin's deliberate revoke of a key it
     * already lacks... it only ever ADDS the keys in
     * get_store_owner_permissions(), so a role that already holds them is a
     * no-op on re-run.
     *
     * @param int  $store_id
     * @param bool $apply  false = report the diff without writing
     * @return array role => ['role_id'=>int,'added'=>int,'missing'=>array]
     */
    public function sync_store_owner_permissions($store_id, $apply = true) {
        $store_id = (int)$store_id;
        if (empty($store_id)) {
            return array();
        }
        return $this->grant_to_roles(
            $store_id,
            $this->get_store_owner_permissions(),
            $apply
        );
    }

    /**
     * Shared worker for owner grants: insert only the keys the store's owner
     * role(s) do not already hold. Roles are matched by the same name list the
     * owner gate uses (store_owner_role_names()), so the gate and the grant can
     * never drift apart.
     */
    private function grant_to_roles($store_id, array $wanted, $apply) {
        $out = array();
        if (empty($wanted) || !function_exists('store_owner_role_ids')) {
            return $out;
        }

        // Match on the store's own owner-class roles. store_owner_role_ids()
        // resolves by name within the store, so a store with no such role
        // simply yields nothing to update.
        $role_ids = store_owner_role_ids($store_id);
        if (empty($role_ids)) {
            return $out;
        }

        $roles = $this->db->where_in('id', $role_ids)->get('db_roles')->result();
        foreach ($roles as $role) {
            $existing = $this->db->select('permissions')->where('role_id', $role->id)
                ->get('db_permissions')->result_array();
            $held = array_map('strval', array_column($existing, 'permissions'));
            $missing = array_values(array_diff($wanted, $held));
            $missing = array_values(array_diff($missing, $this->revoked_keys($store_id, (int) $role->id)));

            $added = 0;
            if ($apply && !empty($missing)) {
                $added = $this->assign_permissions($role->id, $store_id, $missing);
            }
            $out[trim((string)$role->role_name)] = array(
                'role_id' => (int)$role->id,
                'added'   => (int)$added,
                'missing' => $missing,
            );
        }
        return $out;
    }

    /**
     * Give the store's full-control roles the clinical grant set.
     *
     * A store administrator keeps working exactly as before in retail modules
     * (their role already holds those) — this only adds the clinical keys their
     * role preset never contained. Strictly additive: existing rows are left
     * untouched, so an administrator's deliberate edits are not clobbered, and
     * re-running changes nothing.
     *
     * @param int $store_id
     * @param bool $apply  false = report the diff without writing
     * @return array role => ['role_id'=>int,'added'=>int,'missing'=>array]
     */
    public function sync_clinical_admin_permissions($store_id, $apply = true) {
        $store_id = (int)$store_id;
        if (empty($store_id)) {
            return array();
        }
        return $this->grant_clinical_to_roles(
            "store_id = ? AND UPPER(role_name) IN ('ADMIN','STORE ADMIN','BUSINESS OWNER')",
            array($store_id),
            $store_id,
            $apply
        );
    }

    /**
     * The install-level administrator roles (db_store row 1).
     *
     * Historically that row was seeded as "SAAS ADMIN" and carried the demo
     * business name. Newer installers name it after the customer's own store,
     * so nothing should key off the literal string — the row id is the fact.
     *
     * These are the platform operator roles: they exist once for the whole
     * install, not per store, and are meant to hold every capability. They need
     * the clinical grants for the same reason a store administrator does — an
     * install admin who switches a store to the physiotherapy business type
     * must not land on a clinic rail with no clinical screens.
     *
     * @param bool $apply
     * @return array role => ['role_id'=>int,'added'=>int,'missing'=>array]
     */
    public function sync_install_admin_permissions($apply = true) {
        return $this->grant_clinical_to_roles(
            "UPPER(role_name) IN ('ADMIN','STORE ADMIN') AND store_id = (SELECT id FROM db_store ORDER BY id LIMIT 1)",
            array(),
            (int)$this->db->select('id')->order_by('id', 'asc')->limit(1)
                ->get('db_store')->row()->id,
            $apply
        );
    }

    /**
     * Shared worker for the two admin grant syncs: insert only the clinical
     * keys the matched roles do not already hold.
     */
    private function grant_clinical_to_roles($where, array $params, $store_id, $apply) {
        $out = array();
        if (empty($store_id)) {
            return $out;
        }
        $wanted = $this->get_clinical_admin_permissions();
        $roles = $this->db->query("SELECT id, role_name FROM db_roles WHERE {$where}", $params)
            ->result();
        foreach ($roles as $role) {
            $existing = $this->db->select('permissions')->where('role_id', $role->id)
                ->get('db_permissions')->result_array();
            $held = array_map('strval', array_column($existing, 'permissions'));
            $missing = array_values(array_diff($wanted, $held));
            $missing = array_values(array_diff($missing, $this->revoked_keys($store_id, (int) $role->id)));

            $added = 0;
            if ($apply && !empty($missing)) {
                $added = $this->assign_permissions($role->id, $store_id, $missing);
            }
            $out[trim((string)$role->role_name)] = array(
                'role_id' => (int)$role->id,
                'added'   => (int)$added,
                'missing' => $missing,
            );
        }
        return $out;
    }

    /**
     * Align a store's stored feature flags with its industry preset.
     *
     * Why this is needed: changing a store's business type only rewrites the
     * industry setting. The store keeps whichever feature_flags_json it already
     * had, so a store converted from a retail preset keeps that preset's flags
     * and every capability the NEW industry depends on reads back as 0 —
     * mp_feature_enabled() consults the stored flag first. The visible symptom
     * on a converted clinic is a rail missing Ward, Investigates, Sessions and
     * Documents even though the role holds every clinical grant.
     *
     * Policy: only flags that the industry preset requires and that are
     * currently OFF are turned ON, and every change is reported so the operator
     * sees exactly what moved. Flags outside the preset are never touched, so a
     * merchant's deliberate opt-out of an optional capability is preserved.
     * Running it twice changes nothing.
     *
     * @param int  $store_id
     * @param bool $apply  false = report the diff without writing
     * @return array ['industry'=>string,'changed'=>array,'already'=>int,
     *                'unknown'=>int]
     */
    public function sync_industry_feature_flags($store_id, $apply = true) {
        $out = array('industry' => '', 'changed' => array(), 'already' => 0, 'unknown' => 0);
        $store_id = (int) $store_id;
        if (empty($store_id) || !$this->db->table_exists('db_store_industry_settings')) {
            return $out;
        }

        $row = $this->db->select('industry_type, feature_flags_json')
            ->where('store_id', $store_id)->get('db_store_industry_settings')->row();
        if (!$row || empty($row->industry_type)) {
            return $out;
        }
        $out['industry'] = $row->industry_type;

        if (!function_exists('mp_get_business_presets')) {
            $this->load->helper('business_profile');
        }
        // Read the industry preset, NOT mp_get_store_profile(). The profile
        // folds the store's OWN stored flags into 'features' (see the
        // feature_flags_json merge inside mp_get_store_profile), so using it
        // here is self-referential: a flag the store has explicitly set to
        // "0" is removed from 'features' and would never be re-enabled. That
        // silently reduced 20 preset flags to the 9 leftover retail ones.
        $presets = mp_get_business_presets();
        $preset = isset($presets[$row->industry_type])
            ? $presets[$row->industry_type]
            : (isset($presets['general_retail']) ? $presets['general_retail'] : array());
        $wanted = $preset['features'] ?? array();
        if (empty($wanted)) {
            return $out;
        }

        $flags = json_decode((string) $row->feature_flags_json, true);
        if (!is_array($flags)) { $flags = array(); }

        foreach ($wanted as $flag) {
            // A flag that is absent is treated as "not yet set for this
            // industry" and enabled; a flag that is explicitly on is left alone.
            $current = $flags[$flag] ?? null;
            if ((string) $current === '1') { $out['already']++; continue; }
            $out['changed'][$flag] = array('from' => $current, 'to' => '1');
            $flags[$flag] = '1';
        }

        // Anything the preset does not mention is left exactly as it was.
        foreach ($flags as $k => $v) {
            if (!in_array($k, $wanted, true)) { $out['unknown']++; }
        }

        if ($apply && !empty($out['changed'])) {
            $this->db->where('store_id', $store_id)
                ->update('db_store_industry_settings', array(
                    'feature_flags_json' => json_encode($flags),
                ));
        }
        return $out;
    }

    /**
     * Additively sync a store's physiotherapy role presets with the current
     * role map.
     *
     * create_physio_roles() only creates MISSING roles, and
     * reseed_missing_permissions() deliberately skips any role that already
     * holds permissions (so admin edits survive). That leaves roles seeded
     * from an older preset with a partial permission set. This method closes
     * that gap for the physio presets only:
     *
     *  - matching is by role name (case-insensitive) within the store;
     *  - only permission keys in the preset that the role does NOT already
     *    hold are inserted — nothing is ever removed or overwritten, so any
     *    extra permissions an admin granted are preserved;
     *  - pass $apply = false to preview the diff without writing.
     *
     * @return array role => ['role_id'=>int,'added'=>int,'missing'=>array]
     */
    public function sync_physio_role_permissions($store_id, $apply = true) {
        $out = array();
        if (empty($store_id)) {
            return $out;
        }
        foreach ($this->get_physio_role_map() as $role_name => $config) {
            $role = $this->db->query(
                "SELECT id FROM db_roles WHERE store_id = ? AND UPPER(role_name) = UPPER(?)",
                array($store_id, $role_name)
            )->row();
            if (!$role) {
                $out[$role_name] = array('role_id' => null, 'added' => 0,
                    'missing' => array(), 'note' => 'role not present');
                continue;
            }
            $existing = $this->db->select('permissions')->where('role_id', $role->id)
                ->get('db_permissions')->result_array();
            $existing_keys = array_map('strval', array_column($existing, 'permissions'));
            // Never resurrect a grant an admin deliberately removed.
            $revoked = $this->revoked_keys($store_id, (int) $role->id);
            $missing = array_values(array_diff($config['permissions'], $existing_keys));
            $missing = array_values(array_diff($missing, $revoked));
            $added = 0;
            if ($apply && !empty($missing)) {
                $added = $this->assign_permissions($role->id, $store_id, $missing);
            }
            $out[$role_name] = array(
                'role_id' => (int) $role->id,
                'added'   => (int) $added,
                'missing' => $missing,
            );
        }
        return $out;
    }

    /**
     * Recall a previously-recorded revocation (re-grant). Clears the ledger
     * entry so a future sync may restore the preset key.
     */
    public function clear_revocation($store_id, $role_id, $perm) {
        $this->db->where('store_id', (int)$store_id)->where('role_id', (int)$role_id)
            ->where('permissions', (string)$perm)->delete('db_permission_revocations');
        return true;
    }

    /** Record a deliberate revocation so additive sync never re-adds it. */
    public function record_revocation($store_id, $role_id, $perm, $by = null) {
        if(!$this->db->table_exists('db_permission_revocations')) return false;
        $this->db->query(
            'INSERT INTO db_permission_revocations (store_id, role_id, permissions, revoked_by, revoked_at)
             VALUES (?,?,?,?,NOW())
             ON DUPLICATE KEY UPDATE revoked_at=NOW()',
            array((int)$store_id, (int)$role_id, (string)$perm, $by ?: 'system'));
        return true;
    }

    /** Permission keys recorded as deliberately revoked for a role. */
    private function revoked_keys($store_id, $role_id) {
        if(!$this->db->table_exists('db_permission_revocations')) return array();
        $rows = $this->db->where('store_id', (int)$store_id)->where('role_id', (int)$role_id)
            ->get('db_permission_revocations')->result();
        return array_map('strval', array_column($rows, 'permissions'));
    }

    // ============================================================
    // PERMISSION MAPPINGS
    // ============================================================

    /**
     * Business Owner: Full access except Super Admin functions and Help
     */
    private function get_business_owner_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_pur_sal_chart','dashboard_recent_items',
            'dashboard_stock_alert','dashboard_trending_items_chart',
            'recent_sales_invoice_list',
            // Users
            'users_add','users_edit','users_delete','users_view',
            // Tax
            'tax_add','tax_edit','tax_delete','tax_view',
            // Units
            'units_add','units_edit','units_delete','units_view',
            // Payment Types
            'payment_types_add','payment_types_edit','payment_types_delete','payment_types_view',
            // Store
            'store_edit',
            // Items
            'items_add','items_edit','items_delete','items_view',
            'items_category_add','items_category_edit','items_category_delete','items_category_view',
            'brand_add','brand_edit','brand_delete','brand_view',
            'attributes_add','attributes_edit','attributes_delete','attributes_view',
            'variant_add','variant_edit','variant_delete','variant_view',
            'print_labels',
            'import_items',
            // Suppliers
            'suppliers_add','suppliers_edit','suppliers_delete','suppliers_view',
            'import_suppliers',
            // Customers
            'customers_add','customers_edit','customers_delete','customers_view',
            'import_customers','export_customers',
            // Purchases
            'purchase_add','purchase_edit','purchase_delete','purchase_view',
            'purchase_return_add','purchase_return_edit','purchase_return_delete','purchase_return_view',
            'purchase_payment_view','purchase_payment_add','purchase_payment_delete',
            'purchase_return_payment_view','purchase_return_payment_add','purchase_return_payment_delete',
            // Sales
            'sales_add','sales_edit','sales_delete','sales_view',
            'sales_return_add','sales_return_edit','sales_return_delete','sales_return_view',
            'sales_payment_view','sales_payment_add','sales_payment_delete','payments_reconcile',
            'sales_return_payment_view','sales_return_payment_add','sales_return_payment_delete',
            // POS
            'pos',
            // Expenses
            'expense_add','expense_edit','expense_delete','expense_view',
            'expense_category_add','expense_category_edit','expense_category_delete','expense_category_view',
            'show_all_users_expenses',
            // Stock
            'stock_transfer_add','stock_transfer_edit','stock_transfer_delete','stock_transfer_view',
            'stock_adjustment_add','stock_adjustment_edit','stock_adjustment_delete','stock_adjustment_view',
            // Warehouse
            'warehouse_add','warehouse_edit','warehouse_delete','warehouse_view',
            // Accounts
            'accounts_add','accounts_edit','accounts_delete','accounts_view',
            'money_transfer_add','money_transfer_edit','money_transfer_delete','money_transfer_view',
            'money_deposit_add','money_deposit_edit','money_deposit_delete','money_deposit_view',
            'cash_transactions',
            'tills_view','tills_add','tills_edit','tills_delete',
            'cashier_shifts_manage','z_report',
            // Services
            'services_add','services_edit','services_delete','services_view',
            'import_services',
            // Quotations
            'quotation_add','quotation_edit','quotation_delete','quotation_view',
            'show_all_users_quotations',
            // Messaging
            'send_sms','sms_template_view','sms_template_edit',
            'send_email','email_template_view','email_template_edit',
            // Coupons
            'discountCouponAdd','discountCouponEdit','discountCouponDelete','discountCouponView',
            'customerCouponAdd','customerCouponEdit','customerCouponDelete','customerCouponView',
            // Reports
            'sales_report','purchase_report','expense_report','profit_report',
            'stock_report','item_sales_report','expired_items_report',
            'purchase_payments_report','sales_payments_report',
            'sales_tax_report','purchase_tax_report',
            'supplier_items_report','seller_points_report',
                        'return_items_report','stock_transfer_report',
            'sales_summary_report','sales_return_payments',
            'purchase_return_report','sales_return_report',
            'customer_orders_report',
            // FSTR (tax) reports are intentionally NOT granted here —
            // they stay flagged for the Store Admin role only.
            // Fashion Intelligence reports
            'variant_attribute_report','sell_through_report','reorder_suggestion_report',
            'promotions_manage',
            // Advanced
            'cust_adv_payments_add','cust_adv_payments_edit','cust_adv_payments_delete','cust_adv_payments_view',
            'show_all_users_sales_invoices','show_all_users_sales_return_invoices',
            'show_all_users_purchase_invoices','show_all_users_purchase_return_invoices',
            'show_purchase_price',
            // Settings
            'subscription','sms_settings','sms_api_view','sms_api_edit',
            // Online Store (Business Owner can manage online store)
            'online_store_view','online_store_edit',
            // Custom Orders & Nylon Production (full)
            'custom_orders_add','custom_orders_edit','custom_orders_delete','custom_orders_view',
            'nylon_view','nylon_jobs_add','nylon_jobs_edit','nylon_jobs_delete',
            'nylon_report','nylon_approve','nylon_artwork','nylon_costing','nylon_settings',
            // Leads / CRM
            'leads_view','leads_add','leads_edit','leads_delete',
            // Equipment register & service jobs
            'equipment_add','equipment_edit','equipment_view',
            'service_jobs_add','service_jobs_edit','service_jobs_view',
            // Scientific-ops reports
            'quotation_report','procurement_report','warranty_report',
            'equipment_report','service_jobs_report','calibration_report',
            // Debt reminders (view + configure)
            'debt_reminder_view','debt_reminder_manage',
        );
    }

    /**
     * Manager: Operations access, NO Users/Roles/Settings
     */
    private function get_manager_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_pur_sal_chart','dashboard_recent_items',
            'dashboard_stock_alert','dashboard_trending_items_chart',
            'recent_sales_invoice_list',
            // Tax (view only)
            'tax_view',
            // Units (view only)
            'units_view',
            // Payment Types (view only)
            'payment_types_view',
            // Items
            'items_add','items_edit','items_delete','items_view',
            'items_category_add','items_category_edit','items_category_delete','items_category_view',
            'brand_add','brand_edit','brand_delete','brand_view',
            'attributes_add','attributes_edit','attributes_delete','attributes_view',
            'variant_add','variant_edit','variant_delete','variant_view',
            'print_labels',
            'import_items',
            // Suppliers
            'suppliers_add','suppliers_edit','suppliers_delete','suppliers_view',
            'import_suppliers',
            // Customers
            'customers_add','customers_edit','customers_delete','customers_view',
            'import_customers','export_customers',
            // Purchases
            'purchase_add','purchase_edit','purchase_delete','purchase_view',
            'purchase_return_add','purchase_return_edit','purchase_return_delete','purchase_return_view',
            'purchase_payment_view','purchase_payment_add','purchase_payment_delete',
            'purchase_return_payment_view','purchase_return_payment_add','purchase_return_payment_delete',
            // Sales
            'sales_add','sales_edit','sales_delete','sales_view',
            'sales_return_add','sales_return_edit','sales_return_delete','sales_return_view',
            'sales_payment_view','sales_payment_add','sales_payment_delete','payments_reconcile',
            'sales_return_payment_view','sales_return_payment_add','sales_return_payment_delete',
            // POS
            'pos',
            // Expenses
            'expense_add','expense_edit','expense_delete','expense_view',
            'expense_category_add','expense_category_edit','expense_category_delete','expense_category_view',
            'show_all_users_expenses',
            // Stock
            'stock_transfer_add','stock_transfer_edit','stock_transfer_delete','stock_transfer_view',
            'stock_adjustment_add','stock_adjustment_edit','stock_adjustment_delete','stock_adjustment_view',
            // Warehouse
            'warehouse_add','warehouse_edit','warehouse_delete','warehouse_view',
            // Accounts
            'accounts_add','accounts_edit','accounts_delete','accounts_view',
            'money_transfer_add','money_transfer_edit','money_transfer_delete','money_transfer_view',
            'money_deposit_add','money_deposit_edit','money_deposit_delete','money_deposit_view',
            'cash_transactions',
            'tills_view','tills_add','tills_edit','tills_delete',
            'cashier_shifts_manage','z_report',
            // Services
            'services_add','services_edit','services_delete','services_view',
            'import_services',
            // Quotations
            'quotation_add','quotation_edit','quotation_delete','quotation_view',
            'show_all_users_quotations',
            // Messaging (view only)
            'send_sms','sms_template_view',
            'send_email','email_template_view',
            // Coupons (view only)
            'discountCouponView','customerCouponView',
            // Reports
            'sales_report','purchase_report','expense_report','profit_report',
            'stock_report','item_sales_report','expired_items_report',
            'purchase_payments_report','sales_payments_report',
            'sales_tax_report','purchase_tax_report',
            'supplier_items_report','seller_points_report',
            'return_items_report','stock_transfer_report',
            'sales_summary_report','sales_return_payments',
            'purchase_return_report','sales_return_report',
            'customer_orders_report',
            // FSTR (tax) reports are intentionally NOT granted here —
            // they stay flagged for the Store Admin role only.
            // Fashion Intelligence reports
            'variant_attribute_report','sell_through_report','reorder_suggestion_report',
            'promotions_manage',
            // Advanced
            'cust_adv_payments_view',
            'show_all_users_sales_invoices','show_all_users_sales_return_invoices',
            'show_all_users_purchase_invoices','show_all_users_purchase_return_invoices',
            'show_purchase_price',
            // Online Store (Manager can view and fulfill orders, but not edit store settings)
            'online_store_orders',
            // Custom Orders & Nylon Production (Manager is the day-to-day supervisor)
            'custom_orders_add','custom_orders_edit','custom_orders_view',
            'nylon_view','nylon_jobs_add','nylon_jobs_edit',
            'nylon_report','nylon_approve','nylon_artwork','nylon_costing','nylon_settings',
            // Leads / CRM (Manager can work the pipeline but not delete)
            'leads_view','leads_add','leads_edit',
            // Equipment register & service jobs
            'equipment_add','equipment_edit','equipment_view',
            'service_jobs_add','service_jobs_edit','service_jobs_view',
            // Scientific-ops reports
            'quotation_report','procurement_report','warranty_report',
            'equipment_report','service_jobs_report','calibration_report',
        );
    }

    /**
     * Cashier: POS, Sales, Customers, View Items only
     */
    private function get_cashier_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_pur_sal_chart','dashboard_recent_items',
            'dashboard_stock_alert','dashboard_trending_items_chart',
            // Items (view only)
            'items_view','items_category_view','brand_view',
            'print_labels',
            // Customers
            'customers_add','customers_edit','customers_view',
            // Sales (no delete)
            'sales_add','sales_edit','sales_view',
            'sales_return_add','sales_return_edit','sales_return_view',
            'sales_payment_view','sales_payment_add',
            'sales_return_payment_view','sales_return_payment_add',
            // POS
            'pos',
            'cashier_shifts_manage',
            // Quotations (view only)
            'quotation_view',
            // Messaging (view only)
            'send_sms','sms_template_view',
            // Coupons (view only)
            'discountCouponView','customerCouponView',
            // Limited Reports
            'sales_report','sales_payments_report',
            'recent_sales_invoice_list'
        );
    }

    /**
     * Accountant: View sales/purchases, full expenses & accounts, reports
     */
    private function get_accountant_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_pur_sal_chart','dashboard_recent_items',
            'dashboard_stock_alert','dashboard_trending_items_chart',
            // Items (view only)
            'items_view','items_category_view','brand_view',
            // Customers (view only)
            'customers_view',
            // Suppliers (view only)
            'suppliers_view',
            // Sales (view only)
            'sales_view',
            'sales_return_view',
            'sales_payment_view','payments_reconcile',
            'sales_return_payment_view',
            // Purchases (view only)
            'purchase_view',
            'purchase_return_view',
            'purchase_payment_view',
            'purchase_return_payment_view',
            // Expenses (full)
            'expense_add','expense_edit','expense_delete','expense_view',
            'expense_category_add','expense_category_edit','expense_category_delete','expense_category_view',
            'show_all_users_expenses',
            // Accounts (full)
            'accounts_add','accounts_edit','accounts_delete','accounts_view',
            'money_transfer_add','money_transfer_edit','money_transfer_delete','money_transfer_view',
            'money_deposit_add','money_deposit_edit','money_deposit_delete','money_deposit_view',
            'cash_transactions',
            // Customer Advance
            'cust_adv_payments_add','cust_adv_payments_edit','cust_adv_payments_delete','cust_adv_payments_view',
            // Quotations (view only)
            'quotation_view',
            // Messaging (view only)
            'send_sms','sms_template_view',
            'send_email','email_template_view',
            // Reports
            'sales_report','purchase_report','expense_report','profit_report',
            'stock_report',
            'purchase_payments_report','sales_payments_report',
            'sales_tax_report','purchase_tax_report',
            'supplier_items_report','seller_points_report',
                        'return_items_report','stock_transfer_report',
            'sales_summary_report','sales_return_payments',
            'purchase_return_report','sales_return_report',
            'customer_orders_report',
            // Advanced
            'show_all_users_sales_invoices','show_all_users_sales_return_invoices',
            'show_all_users_purchase_invoices','show_all_users_purchase_return_invoices',
            'show_all_users_expenses','show_all_users_quotations',
            'show_purchase_price',
            // Nylon production — finance sees job costing & order balances
            'custom_orders_view','nylon_view','nylon_costing'
        );
    }

    /**
     * Inventory Officer: Items, Purchases, Stock, Warehouse, Suppliers.
     * No POS, no Sales, no Expenses/Accounts, no GST reports.
     */
    private function get_inventory_officer_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_recent_items','dashboard_stock_alert',
            'dashboard_expired_items',
            // Tax (view only)
            'tax_view',
            // Units (view only)
            'units_view',
            // Items (full)
            'items_add','items_edit','items_delete','items_view',
            'items_category_add','items_category_edit','items_category_delete','items_category_view',
            'brand_add','brand_edit','brand_delete','brand_view',
            'attributes_add','attributes_edit','attributes_delete','attributes_view',
            'variant_add','variant_edit','variant_delete','variant_view',
            'print_labels',
            'import_items',
            // Suppliers
            'suppliers_add','suppliers_edit','suppliers_delete','suppliers_view',
            'import_suppliers',
            // Purchases
            'purchase_add','purchase_edit','purchase_delete','purchase_view',
            'purchase_return_add','purchase_return_edit','purchase_return_delete','purchase_return_view',
            'purchase_payment_view','purchase_payment_add',
            'purchase_return_payment_view','purchase_return_payment_add',
            // Stock
            'stock_transfer_add','stock_transfer_edit','stock_transfer_delete','stock_transfer_view',
            'stock_adjustment_add','stock_adjustment_edit','stock_adjustment_delete','stock_adjustment_view',
            // Warehouse
            'warehouse_add','warehouse_edit','warehouse_delete','warehouse_view',
            // Reports (inventory-focused, NO GST reports)
            'purchase_report','stock_report','expired_items_report',
            'purchase_payments_report',
            'supplier_items_report',
            'return_items_report','stock_transfer_report',
            'purchase_return_report',
            // Fashion Intelligence reports
            'variant_attribute_report','reorder_suggestion_report',
            // Advanced
            'show_purchase_price',
            // Nylon production — stock officer reports stage output
            'custom_orders_view','nylon_view','nylon_report'
        );
    }

    /**
     * Production Supervisor: runs the nylon factory floor. Approves stage
     * reports, QC, job completion, corrections/reversals and costing.
     */
    private function get_production_supervisor_permissions() {
        return array(
            'dashboard_view',
            // Items & stock (view + adjustments for corrections)
            'items_view','items_category_view','brand_view','units_view',
            'stock_adjustment_add','stock_adjustment_view',
            'warehouse_view',
            // Customers / orders (view)
            'customers_view','custom_orders_view',
            // Nylon module — full operational access incl. approvals
            'nylon_view','nylon_jobs_add','nylon_jobs_edit','nylon_jobs_delete',
            'nylon_report','nylon_approve','nylon_artwork','nylon_costing','nylon_settings',
            // Reports
            'stock_report','customer_orders_report',
            'show_purchase_price'
        );
    }

    /**
     * Production Operator: reports output, rejects, scrap and waste per shift.
     * Cannot approve, reverse, cost or configure.
     */
    private function get_production_operator_permissions() {
        return array(
            'dashboard_view',
            'items_view','units_view','warehouse_view',
            'custom_orders_view',
            'nylon_view','nylon_report'
        );
    }

    /**
     * Service Engineer: field service staff. Views customers and equipment,
     * works assigned service jobs, records visits/parts and calibration results.
     */
    private function get_service_engineer_permissions() {
        return array(
            'dashboard_view',
            'customers_view',
            'items_view',
            'equipment_view','equipment_edit',
            'service_jobs_view','service_jobs_edit',
        );
    }

    // ============================================================
    // PHYSIOTHERAPY & REHABILITATION ROLES
    // Explicit clinical grants only — physio roles get no delete/approve/
    // export/release rights unless listed. MD authority is a separate key.
    // Checked via physio_can() — the inv_userid 1/2 bypass does not apply.
    // ============================================================

    private function get_physiotherapist_permissions() {
        return array(
            'dashboard_view',
            'services_view',
            'patients_view','patients_add','patients_edit',
            'episodes_view','episodes_add','episodes_edit',
            'appointments_view','appointments_add','appointments_edit','appointments_cancel',
            'care_queue_view',
            'vitals_view','vitals_add',
            'encounters_view','encounters_add','encounters_finalize','encounters_amend',
            'assessments_view','assessments_add','assessments_finalize',
            'investigations_view','investigations_request','investigations_review',
            'plans_view','plans_add','plans_amend',
            'sessions_view','sessions_checkin','sessions_complete',
            'admissions_view',
            'referrals_view','referrals_manage',
            'leave_manage',
            'meals_view',
            'discharge_recommend',
            'patient_docs_view','patient_docs_upload','patient_docs_clinical_view',
            'patient_funds_view',
            'patient_feedback_view',
        );
    }

    private function get_nurse_permissions() {
        return array(
            'dashboard_view',
            'patients_view',
            'care_queue_view',
            'vitals_view','vitals_add',
            'encounters_view',
            'admissions_view',
            'nursing_tasks_view','nursing_tasks_complete','nursing_notes_add',
            'meals_view','meals_manage',
            'leave_manage',
            'patient_docs_view','patient_docs_clinical_view',
        );
    }

    private function get_receptionist_permissions() {
        return array(
            'dashboard_view',
            'leads_view','leads_add','leads_edit',
            'patients_view','patients_add','patients_edit',
            'appointments_view','appointments_add','appointments_edit','appointments_cancel',
            'care_queue_view','care_checkin',
            'sessions_view','sessions_checkin',
            'patient_funds_view','patient_billing_view',
            'patient_docs_view','patient_docs_upload',
            'portal_manage',
        );
    }

    private function get_finance_officer_permissions() {
        return array(
            'dashboard_view',
            'patient_funds_view','patient_funds_add','payment_evidence_verify',
            'funds_adjust_request','refund_request',
            'patient_billing_view','patient_billing_add',
            'opening_positions_view','opening_positions_enter',
            'sessions_view','plans_view','admissions_view',
            'daily_billing_view','daily_billing_run',
            'debt_reminder_view','debt_reminder_manage',
            'sales_view','sales_payment_view','sales_payment_add','payments_reconcile',
            'cust_adv_payments_view','cust_adv_payments_add',
            'expense_view','accounts_view','money_deposit_view',
            'sales_payments_report','receivables_aging_report','cash_flow_report',
            'approval_logs_view',
        );
    }

    private function get_auditor_permissions() {
        return array(
            'dashboard_view',
            'audit_trail_view','approval_logs_view',
            'patient_funds_view','patient_billing_view',
            'debt_reminder_view',
            'opening_positions_view',
            'sales_report','sales_payments_report','receivables_aging_report','cash_flow_report',
        );
    }

    private function get_porter_permissions() {
        return array(
            'porter_tasks_view','porter_tasks_complete',
        );
    }

    private function get_clinical_director_permissions() {
        return array_merge(
            $this->get_physiotherapist_permissions(),
            array(
                'md_authority','can_approve',
                'clinical_cross_branch','clinical_reports_view','clinical_export',
                'investigations_result_enter',
                'admissions_manage','beds_manage',
                'nursing_tasks_view','nursing_notes_add',
                'leave_approve','deceased_record',
                'meals_manage','daily_billing_view',
                'discharge_decide',
                'patient_docs_release',
                'patient_funds_add','payment_evidence_verify','funds_adjust_request','refund_request',
                'patient_billing_view','patient_billing_add',
                'opening_positions_view','opening_positions_enter','opening_positions_review',
                'debt_reminder_view',
                'patient_feedback_manage','testimonial_publish',
                'portal_manage','assessment_templates_manage',
                'imports_view','imports_run','imports_rollback',
            )
        );
    }

    /**
     * Physiotherapy role map — name => permission set. Seeded by
     * create_physio_roles() when the store selects the
     * physiotherapy_rehabilitation business type; also registered in
     * get_role_default_permissions() so reseed can backfill empty roles.
     */
    private function get_physio_role_map() {
        return array(
            'Clinical Director (MD)' => array(
                'description' => 'Medical Director / senior clinician. Clinical decisions, approvals and escalations.',
                'permissions' => $this->get_clinical_director_permissions(),
            ),
            'Physiotherapist' => array(
                'description' => 'Assessments, investigations, plans, treatment notes, referrals, discharge recommendations.',
                'permissions' => $this->get_physiotherapist_permissions(),
            ),
            'Nurse' => array(
                'description' => 'Vitals, observations, assigned care tasks and nursing handover. No financial or discharge authority.',
                'permissions' => $this->get_nurse_permissions(),
            ),
            'Receptionist' => array(
                'description' => 'Leads, registration, appointments, queue/check-in and short tickets. No clinical or discount authority.',
                'permissions' => $this->get_receptionist_permissions(),
            ),
            'Finance Officer' => array(
                'description' => 'Charges, invoices, payment verification, patient funds, statements, reconciliation. Cannot approve own restricted changes.',
                'permissions' => $this->get_finance_officer_permissions(),
            ),
            'Auditor' => array(
                'description' => 'Read-only financial and audit review with discrepancy flags and exports. No clinical access.',
                'permissions' => $this->get_auditor_permissions(),
            ),
            'Porter' => array(
                'description' => 'Patient movement tasks only — destination and assistance instructions. No clinical or finance access.',
                'permissions' => $this->get_porter_permissions(),
            ),
        );
    }

    /**
     * Create the physiotherapy role presets for a store. Idempotent — skips
     * roles that already exist. Called when the store selects the
     * physiotherapy_rehabilitation business type.
     */
    public function create_physio_roles($store_id) {
        $results = array('count' => 0, 'errors' => array());
        if (empty($store_id)) {
            return $results;
        }
        foreach ($this->get_physio_role_map() as $role_name => $config) {
            $exists = $this->db->query(
                "SELECT id FROM db_roles WHERE store_id = ? AND UPPER(role_name) = UPPER(?)",
                array($store_id, $role_name)
            )->num_rows();
            if ($exists > 0) {
                continue;
            }
            if ($this->db->insert('db_roles', array(
                'store_id'    => $store_id,
                'role_name'   => $role_name,
                'description' => $config['description'],
                'status'      => 1,
            ))) {
                $this->assign_permissions($this->db->insert_id(), $store_id, $config['permissions']);
                $results['count']++;
            } else {
                $results['errors'][] = "Failed to create role: {$role_name}";
            }
        }
        return $results;
    }

    /**
     * Partner: Implementation and setup partner.
     * Full setup access (users, roles, business setup, master data) plus operational view.
     */
    private function get_partner_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_pur_sal_chart','dashboard_recent_items',
            'dashboard_stock_alert','dashboard_trending_items_chart',
            'recent_sales_invoice_list',
            // Users
            'users_add','users_edit','users_delete','users_view',
            // Business Setup
            'business_setup',
            // Store
            'store_edit','store_view',
            // Tax
            'tax_add','tax_edit','tax_delete','tax_view',
            // Units
            'units_add','units_edit','units_delete','units_view',
            // Payment Types / Modes
            'payment_types_add','payment_types_edit','payment_types_delete','payment_types_view',
            'payment_modes_add','payment_modes_edit','payment_modes_delete','payment_modes_view',
            // Items
            'items_add','items_edit','items_delete','items_view',
            'items_category_add','items_category_edit','items_category_delete','items_category_view',
            'brand_add','brand_edit','brand_delete','brand_view',
            'attributes_add','attributes_edit','attributes_delete','attributes_view',
            'variant_add','variant_edit','variant_delete','variant_view',
            'print_labels',
            'import_items',
            // Suppliers / Customers
            'suppliers_add','suppliers_edit','suppliers_delete','suppliers_view',
            'import_suppliers',
            'customers_add','customers_edit','customers_delete','customers_view',
            'import_customers','export_customers',
            // Purchases / Sales (view and manage)
            'purchase_add','purchase_edit','purchase_delete','purchase_view',
            'purchase_return_add','purchase_return_edit','purchase_return_delete','purchase_return_view',
            'purchase_payment_view','purchase_payment_add','purchase_payment_delete',
            'purchase_return_payment_view','purchase_return_payment_add','purchase_return_payment_delete',
            'sales_add','sales_edit','sales_delete','sales_view',
            'sales_return_add','sales_return_edit','sales_return_delete','sales_return_view',
            'sales_payment_view','sales_payment_add','sales_payment_delete','payments_reconcile',
            'sales_return_payment_view','sales_return_payment_add','sales_return_payment_delete',
            // Stock / Warehouse
            'stock_transfer_add','stock_transfer_edit','stock_transfer_delete','stock_transfer_view',
            'stock_adjustment_add','stock_adjustment_edit','stock_adjustment_delete','stock_adjustment_view',
            'warehouse_add','warehouse_edit','warehouse_delete','warehouse_view',
            // Accounts
            'accounts_add','accounts_edit','accounts_delete','accounts_view',
            'money_transfer_add','money_transfer_edit','money_transfer_delete','money_transfer_view',
            'money_deposit_add','money_deposit_edit','money_deposit_delete','money_deposit_view',
            'cash_transactions',
            'tills_view','tills_add','tills_edit','tills_delete',
            'cashier_shifts_manage','z_report',
            // Services / Quotations
            'services_add','services_edit','services_delete','services_view',
            'import_services',
            'quotation_add','quotation_edit','quotation_delete','quotation_view',
            // Messaging (sending + store-facing templates only — no API keys)
            'send_sms','sms_template_view','sms_template_edit',
            'send_email',
            // Coupons
            'discountCouponAdd','discountCouponEdit','discountCouponDelete','discountCouponView',
            'customerCouponAdd','customerCouponEdit','customerCouponDelete','customerCouponView',
            // Reports
            'sales_report','purchase_report','expense_report','profit_report',
            'stock_report','item_sales_report','expired_items_report',
            'purchase_payments_report','sales_payments_report',
            'sales_tax_report','purchase_tax_report',
            'supplier_items_report','seller_points_report',
                        'return_items_report','stock_transfer_report',
            'sales_summary_report','sales_return_payments',
            'purchase_return_report','sales_return_report',
            'customer_orders_report',
            // FSTR (tax) reports are intentionally NOT granted here —
            // they stay flagged for the Store Admin role only.
            // Fashion Intelligence
            'variant_attribute_report','sell_through_report','reorder_suggestion_report',
            'promotions_manage',
            // Advanced
            'cust_adv_payments_add','cust_adv_payments_edit','cust_adv_payments_delete','cust_adv_payments_view',
            'show_all_users_sales_invoices','show_all_users_sales_return_invoices',
            'show_all_users_purchase_invoices','show_all_users_purchase_return_invoices',
            'show_all_users_expenses','show_all_users_quotations',
            'show_purchase_price',
            // Settings — store-affecting only. Technical settings stay with
            // the vendor/admin: subscription (license), email provider
            // (smtp_settings), SMS API keys, payment gateways, NIN and
            // system_settings are deliberately NOT granted to partners.
            'expiry_settings',
            'approval_settings_edit','approval_logs_view','can_approve',
            'online_store_view','online_store_edit','online_store_orders',
            'attendance_edit','attendance_view'
        );
    }

    /**
     * Additively backfill permissions for newer module keys onto standard
     * roles that already have permissions. Unlike reseed_missing_permissions,
     * this only ever adds keys that did not exist when the role was created —
     * it never re-adds permissions an admin deliberately removed.
     *
     * Called when a store enables the equipment/service features (e.g. by
     * selecting the Scientific Equipment industry in Business Profile).
     */
    public function seed_module_permissions($store_id = null) {
        if (empty($store_id)) {
            $store_id = get_current_store_id();
        }
        if (empty($store_id)) {
            return 0;
        }

        $module_perms = array(
            'equipment_add','equipment_edit','equipment_view',
            'service_jobs_add','service_jobs_edit','service_jobs_view',
            'quotation_report','procurement_report','warranty_report',
            'equipment_report','service_jobs_report','calibration_report',
        );

        // Ensures the Service Engineer role exists on older stores too (idempotent).
        $this->create_default_roles($store_id);

        $added = 0;
        // Roles can be shared across stores (e.g. the global "Admin" role row
        // lives on store 1 but is referenced by db_permissions rows on other
        // stores), so resolve each role by id rather than by store.
        $role_ids = array_merge(
            array_column($this->db->select('id')->where('store_id', $store_id)->get('db_roles')->result_array(), 'id'),
            array_column($this->db->select('role_id')->distinct()->where('store_id', $store_id)->get('db_permissions')->result_array(), 'role_id')
        );
        foreach (array_unique($role_ids) as $role_id) {
            $role = $this->db->where('id', $role_id)->get('db_roles')->row();
            if (!$role) { continue; }
            $defaults = $this->get_role_default_permissions($role->role_name);
            if (empty($defaults)) { continue; }
            $wanted = array_intersect($module_perms, $defaults);
            if (empty($wanted)) { continue; }
            $existing = array_column(
                $this->db->where('role_id', $role_id)->get('db_permissions')->result_array(),
                'permissions'
            );
            $missing = array_values(array_diff($wanted, $existing));
            if (!empty($missing)) {
                $added += $this->assign_permissions($role_id, $store_id, $missing);
            }
        }
        return $added;
    }

    /**
     * Return the default permission list for a standard role name.
     * Used as a fallback when db_permissions is empty for a role.
     */
    public function get_role_default_permissions($role_name) {
        if (empty($role_name)) {
            return array();
        }

        $role_name = trim($role_name);
        $maps = array(
            'Business Owner' => $this->get_business_owner_permissions(),
            'Partner'        => $this->get_partner_permissions(),
            'Manager'        => $this->get_manager_permissions(),
            'Cashier'        => $this->get_cashier_permissions(),
            'Accountant'     => $this->get_accountant_permissions(),
            'Inventory Officer' => $this->get_inventory_officer_permissions(),
            'Production Supervisor' => $this->get_production_supervisor_permissions(),
            'Production Operator'   => $this->get_production_operator_permissions(),
            'Service Engineer'      => $this->get_service_engineer_permissions(),
            'Admin'          => $this->get_business_owner_permissions(),
            'Clinical Director' => $this->get_clinical_director_permissions(),
            'Physiotherapist' => $this->get_physiotherapist_permissions(),
            'Nurse'          => $this->get_nurse_permissions(),
            'Receptionist'   => $this->get_receptionist_permissions(),
            'Finance Officer'=> $this->get_finance_officer_permissions(),
            'Auditor'        => $this->get_auditor_permissions(),
            'Porter'         => $this->get_porter_permissions(),
        );

        foreach ($maps as $name => $perms) {
            if (stripos($role_name, $name) !== false) {
                return $perms;
            }
        }

        // Loose fallback for owner/manager/admin variants (e.g. "Owner", "Store Owner")
        if (stripos($role_name, 'owner') !== false || stripos($role_name, 'admin') !== false) {
            return $this->get_business_owner_permissions();
        }

        return array();
    }

    // ============================================================
    // DEFAULT EXPENSE CATEGORIES
    // ============================================================

    /**
     * Create default expense categories for a store
     */
    public function create_default_expense_categories($store_id) {
        $results = array('count' => 0, 'errors' => array());

        $categories = array(
            array('name' => 'Rent / Shop Lease',           'description' => 'Monthly rent or lease payments for the business premises'),
            array('name' => 'Staff Salaries & Wages',       'description' => 'Regular salaries and wages paid to staff members'),
            array('name' => 'Staff Allowances',             'description' => 'Additional allowances for staff such as transport, meal, etc.'),
            array('name' => 'Electricity / PHCN',           'description' => 'Electricity bills and power supply charges'),
            array('name' => 'Generator Fuel / Diesel / Petrol', 'description' => 'Fuel costs for backup generators'),
            array('name' => 'Internet & Data Subscription', 'description' => 'Monthly internet and mobile data subscriptions'),
            array('name' => 'POS / Bank Charges',           'description' => 'Transaction fees, POS charges, and bank service charges'),
            array('name' => 'Delivery / Logistics',         'description' => 'Costs for delivering goods to customers'),
            array('name' => 'Transportation',               'description' => 'General transport and fuel costs for business operations'),
            array('name' => 'Supplier Payments',            'description' => 'Payments made to suppliers for goods and stock'),
            array('name' => 'Stock Replenishment',          'description' => 'Costs associated with restocking inventory'),
            array('name' => 'Packaging Materials',          'description' => 'Bags, boxes, tapes, and other packaging supplies'),
            array('name' => 'Repairs & Maintenance',        'description' => 'Equipment and premises repairs and maintenance'),
            array('name' => 'Cleaning & Sanitation',        'description' => 'Cleaning supplies and sanitation services'),
            array('name' => 'Security',                     'description' => 'Security personnel and security system costs'),
            array('name' => 'Shop Supplies',                'description' => 'General shop supplies and consumables'),
            array('name' => 'Marketing & Advertising',    'description' => 'Promotions, flyers, social media ads, and marketing campaigns'),
            array('name' => 'Printing & Stationery',        'description' => 'Receipts, invoices, business cards, and office stationery'),
            array('name' => 'Business Registration / License', 'description' => 'Annual registration fees, licenses, and permits'),
            array('name' => 'Taxes / Levies',               'description' => 'Government taxes, levies, and statutory payments'),
            array('name' => 'Waste Disposal',               'description' => 'Garbage collection and waste management fees'),
            array('name' => 'Staff Feeding / Welfare',      'description' => 'Staff meals, gifts, celebrations, and welfare programs'),
            array('name' => 'Customer Refunds',             'description' => 'Refunds issued to customers for returns or complaints'),
            array('name' => 'Damaged / Expired Goods',      'description' => 'Loss from damaged, expired, or unsellable inventory'),
            array('name' => 'Discounts / Promotions',       'description' => 'Discounts given to customers as part of promotions'),
            array('name' => 'Software Subscription',        'description' => 'Monthly or annual software and SaaS subscriptions'),
            array('name' => 'Phone Calls / Airtime',        'description' => 'Business phone calls and mobile airtime purchases'),
            array('name' => 'Professional Fees',            'description' => 'Legal, accounting, and other professional service fees'),
            array('name' => 'Miscellaneous',              'description' => 'Other expenses not covered by specific categories')
        );

        foreach ($categories as $cat) {
            // Check if category already exists (case-insensitive)
            $exists = $this->db->query(
                "SELECT id FROM db_expense_category WHERE store_id = ? AND UPPER(category_name) = UPPER(?)",
                array($store_id, $cat['name'])
            )->num_rows();

            if ($exists > 0) {
                continue; // Skip duplicate
            }

            // Generate category code
            $maxid = $this->db->query("SELECT COALESCE(MAX(id),0)+1 AS maxid FROM db_expense_category")->row()->maxid;
            $cat_code = 'EC' . str_pad($maxid, 4, '0', STR_PAD_LEFT);

            $data = array(
                'store_id'     => $store_id,
                'category_code'=> $cat_code,
                'category_name'=> $cat['name'],
                'description'  => $cat['description'],
                'created_by'   => 'System',
                'status'       => 1
            );

            if ($this->db->insert('db_expense_category', $data)) {
                $results['count']++;
            } else {
                $results['errors'][] = "Failed to create category: {$cat['name']}";
            }
        }

        return $results;
    }

    // ============================================================
    // INDUSTRY-SPECIFIC DEFAULT DATA
    // ============================================================

    /**
     * Map every business profile to a demo seed pack. Packs preload the
     * master data that profile needs to demo its workflow on day one
     * (units, item categories, recipe categories, brands, sample items and
     * services). Transactional data (sales, purchases, stock transfers)
     * is intentionally NEVER seeded — new deployments start with a clean
     * transaction slate.
     */
    private function _industry_pack_map() {
        return array(
            'general_retail'       => 'retail_basic',
            'multi_branch_retail'  => 'retail_basic',
            'supermarket'          => 'grocery',
            'mini_mart'            => 'grocery',
            'grocery_store'        => 'grocery',
            'provision_store'      => 'grocery',
            'convenience_store'    => 'grocery',
            'pharmacy'             => 'pharma',
            'medical_store'        => 'pharma',
            'restaurant'           => 'food_service',
            'fast_food'            => 'food_service',
            'cafe'                 => 'food_service',
            'pizza_shop'           => 'food_service',
            'shawarma'             => 'food_service',
            'juice_bar'            => 'food_service',
            'buka'                 => 'food_service',
            'canteen'              => 'food_service',
            'electronics'          => 'electronics',
            'computer_store'       => 'electronics',
            'gadget_store'         => 'electronics',
            'appliance_store'      => 'electronics',
            'phone_accessories'    => 'phone_accessories',
            'fashion'              => 'fashion',
            'boutique'             => 'fashion',
            'shoe_store'           => 'fashion',
            'beauty_cosmetics'     => 'beauty_retail',
            'beauty_spa'           => 'beauty_services',
            'salon_barbershop'     => 'beauty_services',
            'makeup_artist'        => 'beauty_services',
            'makeup_studio'        => 'makeup_studio',
            'laundry'              => 'laundry',
            'bakery_cake_studio'   => 'bakery',
            'bookshop'             => 'bookshop',
            'building_materials'   => 'building_materials',
            'paint_store'          => 'paint_store',
            'plumbing_store'       => 'plumbing_store',
            'furniture'            => 'furniture',
            'distributor'          => 'wholesale',
            'wholesaler'           => 'wholesale',
            'butcher'              => 'butchery',
            'frozen_foods_retailer'=> 'frozen',
            'clinic'               => 'healthcare_services',
            'hospital'             => 'healthcare_services',
            'diagnostic_centre'    => 'healthcare_services',
            'physiotherapy_rehabilitation' => 'physio',
            'perfume_shop'         => 'perfume',
            'skincare'             => 'skincare',
            'jewellery_store'      => 'jewellery',
            'agro_dealer'          => 'agro',
            'feed_store'           => 'agro',
            'auto_parts'           => 'auto_parts',
            'tyre_shop'            => 'auto_parts',
            'car_dealership'       => 'auto_dealer',
            'printing'             => 'printing',
            'tailoring'            => 'tailoring',
            'manufacturer'         => 'manufacturing',
            'nylon_polythene'      => 'nylon',
            'scientific_equipment' => 'scientific',
            'service_business'     => 'services',
            'online_store'         => 'digital',
            'creator'              => 'digital',
        );
    }

    /**
     * Demo seed packs. Each pack:
     *  units            => unit_name => [shortcode, parent_shortcode, factor, is_default]
     *  item_categories  => name => description
     *  recipe_categories=> [names]                       (production-style profiles)
     *  brands           => [names]                       (optional)
     *  services         => name => [price, duration, appointment] (optional)
     *  items            => name => [category, unit, sales_price, purchase_price, stock] (optional)
     */
    private function _industry_seed_packs() {
        return array(
            'retail_basic' => array(
                'units' => array(
                    'Piece'   => array('PCS', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Box'     => array('BOX', null, 1, 0),
                    'Kilogram'=> array('KG',  null, 1, 0),
                ),
                'item_categories' => array(
                    'Groceries & Essentials' => 'Everyday groceries and household essentials',
                    'Beverages'              => 'Soft drinks, juices, water and other beverages',
                    'Household & Cleaning'   => 'Cleaning supplies and household items',
                    'Personal Care'          => 'Toiletries and personal care products',
                    'Stationery'             => 'Office and school stationery',
                    'General Merchandise'    => 'Miscellaneous retail items',
                ),
            ),
            'grocery' => array(
                'units' => array(
                    'Piece'   => array('PCS', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Kilogram'=> array('KG',  null, 1, 0),
                    'Gram'    => array('g',   'KG', 1000, 0),
                    'Litre'   => array('L',   null, 1, 0),
                    'Crate'   => array('CRATE', null, 1, 0),
                ),
                'item_categories' => array(
                    'Beverages'             => 'Soft drinks, juices, water, malt and energy drinks',
                    'Snacks & Confectionery'=> 'Biscuits, sweets, chocolates and snacks',
                    'Grains & Staples'      => 'Rice, beans, garri, flour, pasta and other staples',
                    'Dairy & Eggs'          => 'Milk, yoghurt, butter, cheese and eggs',
                    'Fresh Produce'         => 'Fruits, vegetables and fresh foods',
                    'Frozen Foods'          => 'Frozen chicken, fish, meat and vegetables',
                    'Cooking Essentials'    => 'Oils, spices, seasonings and condiments',
                    'Household & Cleaning'  => 'Detergents, soaps and cleaning supplies',
                    'Personal Care'         => 'Toiletries and personal care products',
                    'Baby Products'         => 'Baby food, diapers and baby care',
                ),
            ),
            'pharma' => array(
                'units' => array(
                    'Tablet'  => array('TAB', null, 1, 0),
                    'Capsule' => array('CAP', null, 1, 0),
                    'Card'    => array('CARD', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Bottle'  => array('BTL', null, 1, 0),
                    'Tube'    => array('TUBE', null, 1, 0),
                    'Vial'    => array('VIAL', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Analgesics & Pain Relief'  => 'Pain relievers and anti-inflammatory medicines',
                    'Antibiotics'               => 'Antibiotic medicines (prescription)',
                    'Cold, Flu & Allergy'       => 'Cough syrups, antihistamines and cold remedies',
                    'Vitamins & Supplements'    => 'Multivitamins, supplements and wellness products',
                    'Digestive Health'          => 'Antacids, ORS and digestive remedies',
                    'First Aid & Wound Care'    => 'Bandages, antiseptics and wound care',
                    'Mother & Baby'             => 'Mother and baby care products',
                    'Personal Care & Hygiene'   => 'Soaps, sanitizers and hygiene products',
                    'Medical Devices'           => 'Thermometers, BP monitors and devices',
                ),
            ),
            'food_service' => array(
                'units' => array(
                    'Plate'   => array('PLATE', null, 1, 1),
                    'Portion' => array('PORTION', null, 1, 0),
                    'Cup'     => array('CUP', null, 1, 0),
                    'Bottle'  => array('BTL', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                    'Kilogram'=> array('KG', null, 1, 0),
                ),
                'item_categories' => array(
                    'Mains'               => 'Main dishes and full meals',
                    'Starters & Small Chops'=> 'Appetizers, small chops and finger foods',
                    'Sides & Extras'      => 'Side dishes, extras and add-ons',
                    'Drinks'              => 'Soft drinks, juices, water and beverages',
                    'Desserts'            => 'Desserts and sweet treats',
                    'Combos & Platters'   => 'Combo meals and sharing platters',
                    'Ingredients'         => 'Raw ingredients and kitchen supplies (not for direct sale)',
                ),
                'recipe_categories' => array(
                    'Mains', 'Starters', 'Drinks', 'Desserts', 'Combos',
                ),
            ),
            'electronics' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Box'   => array('BOX', null, 1, 0),
                    'Set'   => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Phones & Tablets'        => 'Mobile phones and tablets',
                    'Laptops & Computers'     => 'Laptops, desktops and computer accessories',
                    'TVs & Audio'             => 'Televisions, speakers and audio equipment',
                    'Home Appliances'         => 'Fridges, freezers, washers and home appliances',
                    'Power & Solar'           => 'Inverters, generators, solar and power solutions',
                    'Accessories'             => 'Chargers, cables, cases and other accessories',
                ),
                'brands' => array('Samsung', 'LG', 'Sony', 'HP', 'Tecno', 'Hisense'),
            ),
            'scientific' => array(
                'units' => array(
                    'Piece'  => array('PCS', null, 1, 1),
                    'Unit'   => array('UNIT', null, 1, 0),
                    'Box'    => array('BOX', null, 1, 0),
                    'Pack'   => array('PACK', null, 1, 0),
                    'Bottle' => array('BTL', null, 1, 0),
                    'Vial'   => array('VIAL', null, 1, 0),
                    'Litre'  => array('L', null, 1, 0),
                    'Set'    => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Analytical Instruments'      => 'Spectrometers, chromatographs, analysers and measuring instruments',
                    'Laboratory Equipment'        => 'Centrifuges, incubators, balances, microscopes and bench equipment',
                    'Reagents & Chemicals'        => 'Reagents, buffers, standards and laboratory chemicals',
                    'Consumables & Glassware'     => 'Pipettes, tubes, glassware and single-use consumables',
                    'Calibration Standards'       => 'Certified reference materials and calibration standards',
                    'Spare Parts & Accessories'   => 'Lamps, probes, electrodes, cables and instrument accessories',
                    'Safety & PPE'                => 'Safety equipment and personal protective equipment',
                ),
                'services' => array(
                    'Installation & Commissioning' => array('price' => 0, 'duration' => 120, 'description' => 'On-site equipment installation and commissioning'),
                    'Preventive Maintenance Visit' => array('price' => 0, 'duration' => 60,  'description' => 'Scheduled preventive maintenance visit'),
                    'Calibration Service'          => array('price' => 0, 'duration' => 60,  'description' => 'Instrument calibration with certificate'),
                    'Repair Call-out'              => array('price' => 0, 'duration' => 60,  'description' => 'On-site diagnostic and repair visit'),
                    'Operator Training'            => array('price' => 0, 'duration' => 240, 'description' => 'Operator and user training session'),
                ),
            ),
            'phone_accessories' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Pack'  => array('PACK', null, 1, 0),
                ),
                'item_categories' => array(
                    'Phone Cases & Covers'    => 'Cases, covers and pouches',
                    'Chargers & Cables'       => 'Chargers, cables and power banks',
                    'Earphones & Audio'       => 'Earphones, headsets and bluetooth audio',
                    'Screen Protectors'       => 'Tempered glass and screen protectors',
                    'Smart Devices'           => 'Smart watches and wearables',
                    'Phone Parts & Repairs'   => 'Screens, batteries and replacement parts',
                ),
            ),
            'fashion' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Pair'  => array('PAIR', null, 1, 0),
                    'Set'   => array('SET', null, 1, 0),
                    'Yard'  => array('YD', null, 1, 0),
                ),
                'item_categories' => array(
                    'Men\'s Wear'          => 'Shirts, trousers, suits and menswear',
                    'Women\'s Wear'        => 'Dresses, tops, skirts and womenswear',
                    'Kids\' Wear'          => 'Children\'s clothing',
                    'Traditional Wear'     => 'Native and traditional attire',
                    'Shoes & Footwear'     => 'Shoes, sneakers, heels and sandals',
                    'Bags & Accessories'   => 'Bags, belts, jewellery and accessories',
                    'Underwear & Lingerie'=> 'Undergarments and lingerie',
                ),
                'brands' => array('Generic', 'Local Designer', 'Imported'),
            ),
            'beauty_retail' => array(
                'units' => array(
                    'Piece'  => array('PCS', null, 1, 1),
                    'Bottle' => array('BTL', null, 1, 0),
                    'Jar'    => array('JAR', null, 1, 0),
                    'Set'    => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Skincare'            => 'Creams, serums, cleansers and skincare',
                    'Makeup'              => 'Foundations, lipsticks, palettes and makeup',
                    'Hair Care'           => 'Shampoos, conditioners, treatments and hair products',
                    'Fragrance'           => 'Perfumes and body sprays',
                    'Tools & Accessories' => 'Brushes, sponges, mirrors and tools',
                ),
                'services' => array(
                    'Product Consultation' => array('price' => 0, 'duration' => '15 min', 'appointment' => 0),
                ),
            ),
            'beauty_services' => array(
                'units' => array(
                    'Session' => array('SESS', null, 1, 1),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Retail Products' => 'Products sold over the counter',
                    'Supplies'        => 'Consumables and supplies used for services',
                ),
                'services' => array(
                    'Haircut / Styling' => array('price' => 5000,  'duration' => '45 min', 'appointment' => 1),
                    'Braiding / Weaving'=> array('price' => 15000, 'duration' => '2 hrs',   'appointment' => 1),
                    'Facial Treatment'  => array('price' => 20000, 'duration' => '1 hr',    'appointment' => 1),
                    'Manicure & Pedicure'=> array('price' => 8000, 'duration' => '1 hr',    'appointment' => 1),
                    'Massage Therapy'   => array('price' => 25000, 'duration' => '1 hr',    'appointment' => 1),
                    'Makeup Session'    => array('price' => 30000, 'duration' => '1.5 hrs', 'appointment' => 1),
                ),
            ),
            'makeup_studio' => array(
                'units' => array(
                    'Session' => array('SESS', null, 1, 1),
                    'Piece'   => array('PCS',  null, 1, 0),
                    'Set'     => array('SET',  null, 1, 0),
                    'Palette' => array('PLT',  null, 1, 0),
                ),
                'item_categories' => array(
                    'Makeup & Cosmetics' => 'Foundations, lipsticks, palettes and colour cosmetics',
                    'Skincare & Prep'    => 'Primers, moisturisers and skin-prep products',
                    'Tools & Brushes'    => 'Brushes, sponges, mirrors and applicators',
                    'Lashes & Brows'     => 'Lashes, brow products and accessories',
                    'Studio Supplies'    => 'Consumables and supplies used for studio services',
                ),
                'brands' => array('Zaron', 'House of Tara', 'Nuban Beauty', 'Maybelline', 'MAC'),
                'services' => array(
                    'Bridal Makeup (Full)'   => array('price' => 80000, 'duration' => '3 hrs',   'appointment' => 1, 'description' => 'Complete bridal look with lashes, touch-up kit and veil setting'),
                    'Bridal Trial Session'   => array('price' => 25000, 'duration' => '1.5 hrs', 'appointment' => 1, 'description' => 'Pre-wedding trial to perfect the bridal look'),
                    'Occasion Glam'          => array('price' => 30000, 'duration' => '1.5 hrs', 'appointment' => 1, 'description' => 'Full glam for events, parties and celebrations'),
                    'Photoshoot / Editorial' => array('price' => 45000, 'duration' => '2 hrs',   'appointment' => 1, 'description' => 'Camera-ready makeup for shoots and editorial work'),
                    'Everyday Soft Glam'     => array('price' => 18000, 'duration' => '1 hr',    'appointment' => 1, 'description' => 'Natural, polished daytime look'),
                    'Brow Shaping & Tinting' => array('price' => 8000,  'duration' => '30 min',  'appointment' => 1, 'description' => 'Brow sculpting, tinting and grooming'),
                    'Lash Extensions'        => array('price' => 15000, 'duration' => '1.5 hrs', 'appointment' => 1, 'description' => 'Classic or volume lash extension application'),
                    'Gele & Headwrap Styling'=> array('price' => 10000, 'duration' => '45 min',  'appointment' => 1, 'description' => 'Traditional gele tying and headwrap styling'),
                    'Makeup Class (1-on-1)'  => array('price' => 50000, 'duration' => '3 hrs',   'appointment' => 1, 'description' => 'Private makeup artistry lesson with a studio artist'),
                    'Studio Consultation'    => array('price' => 0,     'duration' => '20 min',  'appointment' => 1, 'description' => 'Free consultation to plan your look'),
                ),
            ),
            'laundry' => array(
                'units' => array(
                    'Piece'   => array('PCS', null, 1, 1),
                    'Kilogram'=> array('KG', null, 1, 0),
                    'Set'     => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Garments'         => 'Clothing items received for cleaning',
                    'Bedding & Home'   => 'Bedsheets, duvets, curtains and home textiles',
                    'Detergents & Supplies' => 'Detergents and cleaning supplies',
                ),
                'services' => array(
                    'Wash & Iron'   => array('price' => 1000, 'duration' => '24 hrs', 'appointment' => 0),
                    'Wash Only'     => array('price' => 700,  'duration' => '24 hrs', 'appointment' => 0),
                    'Iron Only'     => array('price' => 500,  'duration' => '12 hrs', 'appointment' => 0),
                    'Dry Cleaning'  => array('price' => 2500, 'duration' => '48 hrs', 'appointment' => 0),
                    'Express Service'=> array('price' => 2000, 'duration' => '6 hrs', 'appointment' => 0),
                ),
            ),
            'bakery' => array(
                'units' => array(
                    'Piece'   => array('PCS', null, 1, 1),
                    'Kilogram'=> array('KG', null, 1, 0),
                    'Tray'    => array('TRAY', null, 1, 0),
                    'Pack'    => array('PACK', null, 1, 0),
                ),
                'item_categories' => array(
                    'Breads'               => 'Fresh breads and loaves',
                    'Cakes'                => 'Celebration and everyday cakes',
                    'Pastries & Small Chops'=> 'Meat pies, doughnuts, chin chin and pastries',
                    'Ingredients'          => 'Flour, sugar, butter and baking ingredients',
                    'Packaging'            => 'Cake boxes, bags and packaging materials',
                ),
                'recipe_categories' => array(
                    'Breads', 'Cakes', 'Pastries', 'Icings & Fillings',
                ),
            ),
            'bookshop' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Set'   => array('SET', null, 1, 0),
                    'Pack'  => array('PACK', null, 1, 0),
                ),
                'item_categories' => array(
                    'Fiction'            => 'Novels and story books',
                    'Non-Fiction'        => 'Biographies, self-help and reference',
                    'Textbooks'          => 'School and academic textbooks',
                    'Children\'s Books'  => 'Children\'s stories and activity books',
                    'Religious'          => 'Bibles, Qurans and religious texts',
                    'Stationery'         => 'Pens, notebooks and school supplies',
                ),
            ),
            'building_materials' => array(
                'units' => array(
                    'Bag'     => array('BAG', null, 1, 1),
                    'Piece'   => array('PCS', null, 1, 0),
                    'Length'  => array('LEN', null, 1, 0),
                    'Sheet'   => array('SHEET', null, 1, 0),
                    'Kilogram'=> array('KG', null, 1, 0),
                    'Trip'    => array('TRIP', null, 1, 0),
                ),
                'item_categories' => array(
                    'Cement & Blocks'   => 'Cement, blocks and concrete products',
                    'Roofing'           => 'Roofing sheets, nails and accessories',
                    'Timber & Boards'   => 'Wood, plywood and boards',
                    'Plumbing'          => 'Pipes, fittings and plumbing materials',
                    'Electrical'        => 'Cables, fittings and electrical materials',
                    'Paint & Finishing' => 'Paints, tiles and finishing materials',
                    'Tools & Hardware'  => 'Hand tools and hardware',
                ),
            ),
            'paint_store' => array(
                'units' => array(
                    'Litre'  => array('L', null, 1, 1),
                    'Gallon' => array('GAL', null, 1, 0),
                    'Bucket' => array('BKT', null, 1, 0),
                    'Piece'  => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Emulsion Paints'        => 'Water-based interior and exterior emulsions',
                    'Gloss & Oil Paints'     => 'Oil-based gloss and enamel paints',
                    'Primers & Undercoats'   => 'Primers, sealers and undercoats',
                    'Thinners & Solvents'    => 'Thinners, turpentine and solvents',
                    'Brushes & Rollers'      => 'Paint brushes, rollers and trays',
                    'Waterproofing'          => 'Waterproofing and protective coatings',
                ),
            ),
            'plumbing_store' => array(
                'units' => array(
                    'Piece'  => array('PCS', null, 1, 1),
                    'Length' => array('LEN', null, 1, 0),
                    'Meter'  => array('M', null, 1, 0),
                    'Roll'   => array('ROLL', null, 1, 0),
                ),
                'item_categories' => array(
                    'Pipes & Fittings'    => 'PVC, PPR pipes and fittings',
                    'Valves & Taps'       => 'Taps, valves and faucets',
                    'Water Tanks'         => 'Storage tanks and drums',
                    'Bathroom Fixtures'   => 'WC, basins, showers and fixtures',
                    'Tools & Accessories' => 'Plumbing tools and accessories',
                ),
            ),
            'furniture' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Set'   => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Living Room'      => 'Sofas, centre tables and TV stands',
                    'Bedroom'          => 'Beds, wardrobes and dressers',
                    'Office'           => 'Desks, chairs and office furniture',
                    'Kitchen & Dining' => 'Dining sets and kitchen furniture',
                    'Outdoor'          => 'Outdoor and garden furniture',
                ),
                'services' => array(
                    'Custom Furniture Order' => array('price' => 0, 'duration' => '', 'appointment' => 1),
                    'Delivery & Assembly'    => array('price' => 0, 'duration' => '', 'appointment' => 0),
                ),
            ),
            'wholesale' => array(
                'units' => array(
                    'Carton'  => array('CTN', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                    'Bag'     => array('BAG', null, 1, 0),
                ),
                'item_categories' => array(
                    'Beverages'          => 'Drinks and beverages (bulk)',
                    'Food & Groceries'   => 'Packaged foods and groceries (bulk)',
                    'Household'          => 'Household products (bulk)',
                    'Personal Care'      => 'Personal care products (bulk)',
                    'General Merchandise'=> 'Other wholesale goods',
                ),
            ),
            'butchery' => array(
                'units' => array(
                    'Kilogram'=> array('KG', null, 1, 1),
                    'Gram'    => array('g', 'KG', 1000, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Beef'             => 'Beef cuts and portions',
                    'Goat & Lamb'      => 'Goat meat and lamb cuts',
                    'Pork'             => 'Pork cuts and products',
                    'Chicken & Poultry'=> 'Whole chicken and poultry parts',
                    'Fish & Seafood'   => 'Fresh fish and seafood',
                    'Processed Meats'  => 'Sausages, suya cuts and processed meats',
                ),
            ),
            'frozen' => array(
                'units' => array(
                    'Kilogram'=> array('KG', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Frozen Chicken'      => 'Frozen whole chicken and parts',
                    'Frozen Fish'         => 'Frozen fish and seafood',
                    'Frozen Meat'         => 'Frozen beef, goat and other meats',
                    'Frozen Vegetables'   => 'Frozen vegetables and fries',
                    'Ice Cream & Desserts'=> 'Ice cream and frozen desserts',
                    'Ready Meals'         => 'Frozen ready-to-cook meals',
                ),
            ),
            'healthcare_services' => array(
                'units' => array(
                    'Session' => array('SESS', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Medical Supplies' => 'Consumable medical supplies',
                    'Pharmacy Items'   => 'Dispensed medicines and OTC items',
                    'Lab Consumables'  => 'Laboratory reagents and consumables',
                ),
                'services' => array(
                    'Consultation'        => array('price' => 10000, 'duration' => '30 min', 'appointment' => 1),
                    'Laboratory Test'     => array('price' => 0,     'duration' => '',        'appointment' => 1),
                    'Vaccination'         => array('price' => 0,     'duration' => '15 min',  'appointment' => 1),
                    'Follow-up Visit'     => array('price' => 5000,  'duration' => '15 min',  'appointment' => 1),
                ),
            ),
            'physio' => array(
                'units' => array(
                    'Session' => array('SESS', null, 1, 1),
                    'Day'     => array('DAY',  null, 1, 0),
                    'Piece'   => array('PCS',  null, 1, 0),
                    'Pack'    => array('PACK', null, 1, 0),
                ),
                'item_categories' => array(
                    'Rehab Aids & Supports' => 'Braces, splints, crutches, supports and mobility aids',
                    'Physio Consumables'    => 'Tapes, bandages, gels, electrodes and treatment consumables',
                    'Exercise Equipment'    => 'Resistance bands, balls, weights and home-exercise items',
                ),
                'services' => array(
                    'Initial Assessment'        => array('price' => 0, 'duration' => '60 min', 'appointment' => 1, 'description' => 'First-visit clinical assessment and treatment plan'),
                    'Physiotherapy Session'     => array('price' => 20000, 'duration' => '45 min', 'appointment' => 1, 'description' => 'Standard outpatient treatment session'),
                    'Review / Re-assessment'    => array('price' => 0, 'duration' => '30 min', 'appointment' => 1),
                    'Home Visit'                => array('price' => 0, 'duration' => '60 min', 'appointment' => 1, 'description' => 'Physiotherapy delivered at the patient\'s home'),
                    'Inpatient Day Care'        => array('price' => 0, 'duration' => '',         'appointment' => 0, 'description' => 'Daily inpatient care charge line'),
                ),
            ),
            'perfume' => array(
                'units' => array(
                    'Litre'      => array('L',   null, 1,        0),
                    'Millilitre' => array('ml',  'L',  1000,     1),
                    'Fluid Ounce'=> array('fl oz','L',  33.814,  0),
                    'Kilogram'   => array('KG',  null, 1,        0),
                    'Gram'       => array('g',   'KG', 1000,     0),
                    'Piece'      => array('PCS', null, 1,        0),
                    'Bottle'     => array('',    null, 1,        0),
                    'Decant'     => array('',    null, 1,        0),
                ),
                'item_categories' => array(
                    'Fragrance Oils & Concentrates' => 'Pure perfume oils, dupes and concentrates used in blending',
                    'Solvents & Bases'              => 'Perfumer\'s alcohol, DPG, IPM, distilled water, fixatives',
                    'Bottles & Packaging'           => 'Glass bottles, atomizers, caps, labels, boxes, pouches',
                    'Finished Perfumes'             => 'Bottled fragrances ready for sale',
                    'Decants & Samples'             => 'Small-volume decants and sample vials',
                    'Tester Units'                  => 'Open bottles used as counter testers',
                ),
                'recipe_categories' => array(
                    'Extrait de Parfum',
                    'Eau de Parfum',
                    'Eau de Toilette',
                    'Eau de Cologne',
                    'Perfume Oil / Attar',
                    'Home Fragrance',
                ),
                'services' => array(
                    'Bespoke Blend Consultation' => array('price' => 0, 'duration' => '45 min', 'appointment' => 1),
                ),
            ),
            'skincare' => array(
                'units' => array(
                    'Piece'      => array('PCS', null, 1,    1),
                    'Jar'        => array('JAR', null, 1,    0),
                    'Bottle'     => array('BTL', null, 1,    0),
                    'Tube'       => array('TUBE',null, 1,    0),
                    'Set'        => array('SET', null, 1,    0),
                    'Kilogram'   => array('KG',  null, 1,    0),
                    'Gram'       => array('g',   'KG', 1000, 0),
                    'Litre'      => array('L',   null, 1,    0),
                    'Millilitre' => array('ml',  'L',  1000, 0),
                ),
                'item_categories' => array(
                    'Face Care'           => 'Face creams, moisturisers, day and night creams',
                    'Body Care'           => 'Body butters, body creams, lotions and body oils',
                    'Cleansers & Toners'  => 'Face washes, cleansers, toners and mists',
                    'Serums & Actives'    => 'Concentrated serums, oils and active treatments',
                    'Soaps & Scrubs'      => 'Bar soaps, body scrubs, exfoliants and masks',
                    'Raw Ingredients'     => 'Butters, carrier oils, waxes, clays and actives for formulation',
                    'Packaging'           => 'Jars, bottles, pumps, labels and packaging materials',
                ),
                'recipe_categories' => array(
                    'Face Creams',
                    'Body Butters',
                    'Lotions & Emulsions',
                    'Serums & Oils',
                    'Soaps & Cleansers',
                    'Scrubs & Masks',
                ),
                'services' => array(
                    'Skin Consultation'     => array('price' => 0, 'duration' => '30 min', 'appointment' => 1, 'description' => 'Skin analysis and personalised routine recommendation'),
                    'Facial Treatment'      => array('price' => 20000, 'duration' => '1 hr', 'appointment' => 1, 'description' => 'Deep-cleansing facial using in-house formulations'),
                    'Custom Formulation'    => array('price' => 0, 'duration' => '1 hr', 'appointment' => 1, 'description' => 'Bespoke cream or butter blended to your skin needs'),
                ),
            ),
            'jewellery' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Pair'  => array('PAIR', null, 1, 0),
                    'Set'   => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Rings'     => 'Engagement, wedding and fashion rings',
                    'Necklaces' => 'Chains, pendants and necklaces',
                    'Earrings'  => 'Studs, hoops and drop earrings',
                    'Bracelets' => 'Bangles and bracelets',
                    'Watches'   => 'Wrist watches',
                ),
            ),
            'agro' => array(
                'units' => array(
                    'Kilogram'=> array('KG', null, 1, 1),
                    'Bag'     => array('BAG', null, 1, 0),
                    'Litre'   => array('L', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Seeds & Seedlings'  => 'Seeds, seedlings and planting materials',
                    'Fertilizers'        => 'Fertilizers and soil conditioners',
                    'Agro-Chemicals'     => 'Herbicides, pesticides and fungicides',
                    'Animal Feed'        => 'Livestock and poultry feed',
                    'Veterinary'         => 'Animal health and veterinary products',
                    'Farm Tools'         => 'Farm tools and equipment',
                ),
            ),
            'auto_parts' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Set'   => array('SET', null, 1, 0),
                    'Pair'  => array('PAIR', null, 1, 0),
                    'Litre' => array('L', null, 1, 0),
                ),
                'item_categories' => array(
                    'Engine Parts'      => 'Engine components and parts',
                    'Brakes'            => 'Brake pads, discs and brake parts',
                    'Suspension'        => 'Shocks, struts and suspension parts',
                    'Electrical'        => 'Batteries, alternators and electrical parts',
                    'Filters & Fluids'  => 'Oil, filters and fluids',
                    'Tyres & Wheels'    => 'Tyres, rims and wheel accessories',
                    'Body & Accessories'=> 'Body parts and accessories',
                ),
            ),
            'auto_dealer' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Set'   => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Vehicles'    => 'Cars and vehicles for sale',
                    'Spare Parts' => 'Vehicle spare parts',
                    'Accessories' => 'Car accessories and add-ons',
                ),
                'services' => array(
                    'Vehicle Inspection' => array('price' => 0, 'duration' => '1 hr', 'appointment' => 1),
                    'Test Drive Booking' => array('price' => 0, 'duration' => '30 min', 'appointment' => 1),
                ),
            ),
            'printing' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Ream'  => array('REAM', null, 1, 0),
                    'Sheet' => array('SHEET', null, 1, 0),
                    'Pack'  => array('PACK', null, 1, 0),
                ),
                'item_categories' => array(
                    'Business Cards'       => 'Business cards and complimentary cards',
                    'Flyers & Banners'     => 'Flyers, banners and posters',
                    'Branded Merchandise'  => 'Branded shirts, mugs and merchandise',
                    'Stationery'           => 'Letterheads, envelopes and stationery',
                    'Large Format'         => 'Billboards, signage and large format prints',
                ),
                'services' => array(
                    'Graphic Design' => array('price' => 0, 'duration' => '', 'appointment' => 0),
                ),
            ),
            'tailoring' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Yard'  => array('YD', null, 1, 0),
                    'Set'   => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Fabrics'              => 'Fabric materials by the yard',
                    'Ready-Made'           => 'Ready-made garments',
                    'Accessories & Notions'=> 'Zips, buttons, threads and notions',
                ),
                'services' => array(
                    'Bespoke / Custom Order' => array('price' => 0, 'duration' => '', 'appointment' => 1),
                    'Alterations'            => array('price' => 0, 'duration' => '', 'appointment' => 0),
                ),
            ),
            'manufacturing' => array(
                'units' => array(
                    'Kilogram'=> array('KG', null, 1, 1),
                    'Litre'   => array('L', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                    'Pack'    => array('PACK', null, 1, 0),
                ),
                'item_categories' => array(
                    'Raw Materials'   => 'Raw materials and inputs',
                    'Work In Progress'=> 'Semi-finished goods',
                    'Finished Goods'  => 'Finished products ready for sale',
                    'Packaging'       => 'Packaging materials',
                ),
                'recipe_categories' => array(
                    'Product Formulas',
                ),
            ),
            'nylon' => array(
                'units' => array(
                    'Kilogram' => array('KG', null, 1, 1),
                    'Roll'     => array('ROLL', null, 1, 0),
                    'Piece'    => array('PCS', null, 1, 0),
                    'Bundle'   => array('BNDL', null, 1, 0),
                    'Carton'   => array('CTN', null, 1, 0),
                ),
                'item_categories' => array(
                    'Raw Resin & Additives' => 'HDPE/LDPE resin, masterbatch and additives',
                    'Film Rolls (WIP)'      => 'Extruded or purchased film rolls awaiting conversion or sale',
                    'Finished Bags & Film'  => 'Converted bags, sheets and film ready for sale',
                    'Reusable Scrap'        => 'Trim and offcut recovered for reprocessing',
                    'Packaging & Consumables'=> 'Ink, cores, cartons and strapping',
                ),
                'recipe_categories' => array(
                    'Film Specifications',
                ),
            ),
            'services' => array(
                'units' => array(
                    'Session' => array('SESS', null, 1, 1),
                    'Hour'    => array('HR', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Service Packages' => 'Packaged service offerings',
                    'Supplies'         => 'Materials and supplies used for service delivery',
                    'Retail Products'  => 'Products sold alongside services',
                ),
                'services' => array(
                    'Consultation'     => array('price' => 0, 'duration' => '1 hr', 'appointment' => 1),
                    'Standard Service' => array('price' => 0, 'duration' => '',      'appointment' => 1),
                    'Emergency Call-out'=> array('price' => 0, 'duration' => '',    'appointment' => 1),
                ),
            ),
            'digital' => array(
                'units' => array(
                    'Download' => array('DL', null, 1, 1),
                    'License'  => array('LIC', null, 1, 0),
                    'Piece'    => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Digital Downloads' => 'Ebooks, templates and digital files',
                    'Online Courses'    => 'Courses and learning programs',
                    'Memberships'       => 'Memberships and subscriptions',
                    'Physical Merch'    => 'Physical merchandise and goods',
                ),
            ),
        );
    }

    /**
     * Seed master data a specific industry needs to be productive on day one.
     * Idempotent: rows are matched by name before insert, safe to re-run and
     * safe to call when a store switches industry mid-life.
     *
     * @param int    $store_id
     * @param string $industry_type  e.g. 'perfume_shop'
     * @return array counts created
     */
    public function seed_industry_defaults($store_id, $industry_type) {
        $results = array(
            'units_created' => 0,
            'categories_created' => 0,
            'recipe_categories_created' => 0,
            'brands_created' => 0,
            'services_created' => 0,
        );
        if (empty($store_id) || empty($industry_type)) {
            return $results;
        }

        $packs = $this->_industry_seed_packs();
        $map   = $this->_industry_pack_map();
        $pack_key = isset($map[$industry_type]) ? $map[$industry_type] : 'retail_basic';

        if (empty($packs[$pack_key])) {
            return $results;
        }
        $cfg = $packs[$pack_key];

        // --- Units (with parent/child conversions, e.g. Litre = 1000 ml) ---
        if ($this->db->table_exists('db_units')) {
            $unit_ids = array();
            foreach ($cfg['units'] as $unit_name => $u) {
                list($shortcode, $parent_sc, $factor, $is_default) = $u;
                $exists = $this->db->where('store_id', $store_id)
                    ->where('unit_name', $unit_name)->get('db_units')->row();
                $parent_id = null;
                if ($parent_sc && isset($unit_ids[$parent_sc])) {
                    $parent_id = $unit_ids[$parent_sc];
                }
                if ($exists) {
                    $unit_ids[$shortcode] = $exists->id;
                    // Repair hierarchy/conversion on existing rows — an earlier
                    // version of this map seeded the parent/child direction backwards
                    $fix = array();
                    if ($this->db->field_exists('parent_unit_id', 'db_units')
                        && (int)($exists->parent_unit_id ?? 0) !== (int)($parent_id ?? 0)) {
                        $fix['parent_unit_id'] = $parent_id;
                    }
                    if ($this->db->field_exists('conversion_factor', 'db_units')
                        && abs((float)($exists->conversion_factor ?? 0) - (float)$factor) > 0.000001) {
                        $fix['conversion_factor'] = $factor;
                    }
                    if ($this->db->field_exists('is_default', 'db_units')
                        && (int)($exists->is_default ?? 0) !== (int)$is_default) {
                        $fix['is_default'] = $is_default;
                    }
                    if ($this->db->field_exists('shortcode', 'db_units')
                        && (string)($exists->shortcode ?? '') !== (string)$shortcode) {
                        $fix['shortcode'] = $shortcode;
                    }
                    if (!empty($fix)) {
                        $this->db->where('id', $exists->id)->update('db_units', $fix);
                    }
                    continue;
                }
                $data = array(
                    'store_id'          => $store_id,
                    'unit_name'         => $unit_name,
                    'description'       => '',
                    'status'            => 1,
                );
                if ($this->db->field_exists('shortcode', 'db_units')) {
                    $data['shortcode'] = $shortcode;
                }
                if ($parent_id !== null && $this->db->field_exists('parent_unit_id', 'db_units')) {
                    $data['parent_unit_id'] = $parent_id;
                }
                if ($this->db->field_exists('conversion_factor', 'db_units')) {
                    $data['conversion_factor'] = $factor;
                }
                if ($this->db->field_exists('is_default', 'db_units')) {
                    $data['is_default'] = $is_default;
                }
                if ($this->db->insert('db_units', $data)) {
                    $unit_ids[$shortcode] = $this->db->insert_id();
                    $results['units_created']++;
                }
            }
        }

        // --- Item categories ---
        if ($this->db->table_exists('db_category')) {
            foreach ($cfg['item_categories'] as $cat_name => $desc) {
                $exists = $this->db->where('store_id', $store_id)
                    ->where('category_name', $cat_name)->get('db_category')->row();
                if ($exists) continue;
                $maxid = $this->db->query("SELECT COALESCE(MAX(count_id),0)+1 AS maxid FROM db_category WHERE store_id = ?", array($store_id))->row()->maxid;
                $data = array(
                    'store_id'      => $store_id,
                    'count_id'      => $maxid,
                    'category_name' => $cat_name,
                    'description'   => $desc,
                    'status'        => 1,
                );
                if ($this->db->field_exists('category_code', 'db_category')) {
                    $data['category_code'] = 'CT/' . str_pad($store_id, 2, '0', STR_PAD_LEFT) . '/' . str_pad($maxid, 4, '0', STR_PAD_LEFT);
                }
                if ($this->db->insert('db_category', $data)) {
                    $results['categories_created']++;
                }
            }
        }

        // --- Formula (recipe) categories ---
        if ($this->db->table_exists('db_recipe_categories') && !empty($cfg['recipe_categories'])) {
            foreach ($cfg['recipe_categories'] as $rc_name) {
                $exists = $this->db->where('store_id', $store_id)
                    ->where('name', $rc_name)->get('db_recipe_categories')->row();
                if ($exists) continue;
                if ($this->db->insert('db_recipe_categories', array(
                    'store_id' => $store_id, 'name' => $rc_name, 'status' => 1,
                ))) {
                    $results['recipe_categories_created']++;
                }
            }
        }

        // --- Brands ---
        if ($this->db->table_exists('db_brands') && !empty($cfg['brands'])) {
            foreach ($cfg['brands'] as $brand_name) {
                $exists = $this->db->where('store_id', $store_id)
                    ->where('brand_name', $brand_name)->get('db_brands')->row();
                if ($exists) continue;
                $data = array(
                    'store_id'   => $store_id,
                    'brand_name' => $brand_name,
                    'description'=> '',
                    'status'     => 1,
                );
                if ($this->db->field_exists('brand_code', 'db_brands')) {
                    $maxid = $this->db->query("SELECT COALESCE(MAX(id),0)+1 AS maxid FROM db_brands WHERE store_id = ?", array($store_id))->row()->maxid;
                    $data['brand_code'] = 'BR/' . str_pad($store_id, 2, '0', STR_PAD_LEFT) . '/' . str_pad($maxid, 4, '0', STR_PAD_LEFT);
                }
                if ($this->db->insert('db_brands', $data)) {
                    $results['brands_created']++;
                }
            }
        }

        // --- Services (service-led profiles) ---
        if ($this->db->table_exists('db_services') && !empty($cfg['services'])) {
            $sort = 0;
            foreach ($cfg['services'] as $svc_name => $svc) {
                $exists = $this->db->where('store_id', $store_id)
                    ->where('service_name', $svc_name)->get('db_services')->row();
                if ($exists) { $sort++; continue; }
                if ($this->db->insert('db_services', array(
                    'store_id'             => $store_id,
                    'service_name'         => $svc_name,
                    'price'                => isset($svc['price']) ? $svc['price'] : 0,
                    'service_duration'     => isset($svc['duration']) ? $svc['duration'] : '',
                    'description'          => isset($svc['description']) ? $svc['description'] : '',
                    'requires_appointment' => !empty($svc['appointment']) ? 1 : 0,
                    'sort_order'           => $sort,
                    'status'               => 1,
                ))) {
                    $results['services_created']++;
                }
                $sort++;
            }
        }

        // --- Physiotherapy role presets (explicit clinical permission grants) ---
        if ($industry_type === 'physiotherapy_rehabilitation') {
            $role_results = $this->create_physio_roles($store_id);
            $results['physio_roles_created'] = $role_results['count'];
            $this->sync_clinical_admin_permissions($store_id);
            $this->sync_install_admin_permissions();
            $this->sync_store_owner_permissions($store_id);
            $this->sync_physio_role_permissions($store_id);
        }

        return $results;
    }
}
