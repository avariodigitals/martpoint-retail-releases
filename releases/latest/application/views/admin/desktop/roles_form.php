<?php
/* Role create/edit form with permissions matrix — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
  .mp-role-form { display:grid; grid-template-columns:1fr 1fr; gap:18px 32px; }
  .mp-role-form .mp-form-full { grid-column:1 / -1; }
  .mp-role-form .mp-form-field { display:flex; flex-direction:column; gap:6px; }
  .mp-role-form .mp-form-field label { font-size:13px; font-weight:600; color:var(--mp-ink); margin:0; }
  .mp-role-form .mp-form-field .req { color:var(--mp-danger); margin-left:2px; }
  .mp-role-form .mp-form-field .form-control { width:100%; }
  .mp-role-form .mp-form-field .field-msg { font-size:12px; color:var(--mp-danger); display:none; }

  .mp-perms-card { margin-top:24px; }
  .mp-perms-card .mp-card-head { display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-bottom:1px solid var(--mp-border); }
  .mp-perms-card .mp-card-head h3 { margin:0; font-size:16px; font-weight:700; }
  .mp-perms-search { padding:8px 14px; border:1px solid var(--mp-border); border-radius:8px; font-size:13px; width:220px; }
  .mp-perms-table { width:100%; border-collapse:collapse; }
  .mp-perms-table th { text-align:left; padding:10px 14px; font-size:12px; font-weight:700; color:var(--mp-muted); text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid var(--mp-border); background:var(--mp-bg); }
  .mp-perms-table td { padding:10px 14px; border-bottom:1px solid var(--mp-border); font-size:13px; vertical-align:middle; }
  .mp-perms-table tr:hover td { background:var(--mp-bg); }
  .mp-perms-table .mod-name { font-weight:600; color:var(--mp-ink); }
  .mp-perms-table .perm-cell label { display:inline-flex; align-items:center; gap:5px; cursor:pointer; margin-right:14px; font-size:13px; }
  .mp-perms-table .perm-cell input[type=checkbox] { width:16px; height:16px; cursor:pointer; }
  .mp-perms-table .select-all-cell input[type=checkbox] { width:16px; height:16px; cursor:pointer; }
  .mp-perms-actions { display:flex; gap:10px; padding:12px 20px; border-top:1px solid var(--mp-border); }
  .mp-perms-actions .mp-btn-sm { padding:6px 14px; font-size:12px; border-radius:6px; border:1px solid var(--mp-border); background:var(--mp-bg); cursor:pointer; color:var(--mp-ink); }
  .mp-perms-actions .mp-btn-sm:hover { background:var(--mp-primary); color:#fff; border-color:var(--mp-primary); }
  @media (max-width:768px){ .mp-role-form{ grid-template-columns:1fr; } .mp-perms-search{ width:100%; } }
</style>

<div class="mp-page-head">
  <h1 class="mp-page-title"><?= htmlspecialchars($page_title); ?></h1>
  <p class="mp-page-sub"><?= (!empty($q_id)) ? 'Update role details and permissions.' : 'Create a new role and assign permissions.'; ?></p>
</div>

<form id="role-form" onkeypress="return event.keyCode != 13;" enctype="multipart/form-data">
<div class="mp-card">
<div class="mp-card-body">
    <input type="hidden" id="base_url" value="<?= $base_url; ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="q_id" id="q_id" value="<?= htmlspecialchars($q_id ?? ''); ?>">
    <input type="hidden" name="store_id" id="store_id" value="<?= get_current_store_id(); ?>">

    <div class="mp-role-form">
      <div class="mp-form-field">
        <label for="role_name">Role Name <span class="req">*</span></label>
        <input type="text" class="form-control" id="role_name" name="role_name" value="<?= htmlspecialchars($role_name ?? ''); ?>" autocomplete="off" autofocus>
        <span id="role_name_msg" class="field-msg"></span>
      </div>

      <div class="mp-form-field">
        <label for="description">Description</label>
        <input type="text" class="form-control" id="description" name="description" value="<?= htmlspecialchars($description ?? ''); ?>" autocomplete="off">
        <span id="description_msg" class="field-msg"></span>
      </div>
    </div>
</div>
</div>

<!-- Permissions Matrix -->
<div class="mp-card mp-perms-card">
  <div class="mp-card-head">
    <h3><i class="fa fa-shield"></i> Module Permissions</h3>
    <input type="text" class="mp-perms-search" id="perms_search" placeholder="Search modules...">
  </div>
  <div style="overflow-x:auto;">
    <table class="mp-perms-table" id="perms_table">
      <thead>
        <tr>
          <th style="width:30px;">#</th>
          <th>Module</th>
          <th style="width:80px;">Select All</th>
          <th>Permissions</th>
        </tr>
      </thead>
      <tbody>
      <?php
      // Define permission groups: [module_label, group_id, [permission_key, permission_label]]
      $permission_groups = [
        ['Users', 'users', [
          ['users_add','Add'], ['users_edit','Edit'], ['users_delete','Delete'], ['users_view','View'],
        ]],
        ['Attendance', 'attendance', [
          ['attendance_edit','Edit'], ['attendance_view','View'],
        ]],
        ['Roles', 'roles', [
          ['roles_add','Add'], ['roles_edit','Edit'], ['roles_delete','Delete'], ['roles_view','View'],
        ]],
        ['Tax', 'tax', [
          ['tax_add','Add'], ['tax_edit','Edit'], ['tax_delete','Delete'], ['tax_view','View'],
        ]],
        ['Units', 'units', [
          ['units_add','Add'], ['units_edit','Edit'], ['units_delete','Delete'], ['units_view','View'],
        ]],
        ['Payment Types', 'payment_types', [
          ['payment_types_add','Add'], ['payment_types_edit','Edit'], ['payment_types_delete','Delete'], ['payment_types_view','View'],
        ]],
        ['Payment Modes', 'payment_modes', [
          ['payment_modes_add','Add'], ['payment_modes_edit','Edit'], ['payment_modes_delete','Delete'], ['payment_modes_view','View'],
        ]],
        ['Store Settings', 'store', [
          ['store_edit','Edit'],
        ]],
        ['Expense', 'expense', [
          ['expense_add','Add'], ['expense_edit','Edit'], ['expense_delete','Delete'], ['expense_view','View'],
        ]],
        ['Expense Category', 'expense_category', [
          ['expense_category_add','Add'], ['expense_category_edit','Edit'], ['expense_category_delete','Delete'], ['expense_category_view','View'],
        ]],
        ['Items / Products', 'items', [
          ['items_add','Add'], ['items_edit','Edit'], ['items_delete','Delete'], ['items_view','View'], ['import_items','Import'],
        ]],
        ['Item Category', 'items_category', [
          ['items_category_add','Add'], ['items_category_edit','Edit'], ['items_category_delete','Delete'], ['items_category_view','View'],
        ]],
        ['Brand', 'brand', [
          ['brand_add','Add'], ['brand_edit','Edit'], ['brand_delete','Delete'], ['brand_view','View'],
        ]],
        ['Variants', 'variant', [
          ['variant_add','Add'], ['variant_edit','Edit'], ['variant_delete','Delete'], ['variant_view','View'],
        ]],
        ['Suppliers', 'suppliers', [
          ['suppliers_add','Add'], ['suppliers_edit','Edit'], ['suppliers_delete','Delete'], ['suppliers_view','View'], ['import_suppliers','Import'],
        ]],
        ['Customers', 'customers', [
          ['customers_add','Add'], ['customers_edit','Edit'], ['customers_delete','Delete'], ['customers_view','View'], ['import_customers','Import'],
        ]],
        ['Customer Advance Payments', 'cust_adv_payments', [
          ['cust_adv_payments_add','Add'], ['cust_adv_payments_edit','Edit'], ['cust_adv_payments_delete','Delete'], ['cust_adv_payments_view','View'],
        ]],
        ['Purchase', 'purchase', [
          ['purchase_add','Add'], ['purchase_edit','Edit'], ['purchase_delete','Delete'], ['purchase_view','View'],
          ['purchase_payment_view','Pay View'], ['purchase_payment_add','Pay Add'], ['purchase_payment_delete','Pay Delete'],
        ]],
        ['Purchase Return', 'purchase_return', [
          ['purchase_return_add','Add'], ['purchase_return_edit','Edit'], ['purchase_return_delete','Delete'], ['purchase_return_view','View'],
          ['purchase_return_payment_view','Pay View'], ['purchase_return_payment_add','Pay Add'], ['purchase_return_payment_delete','Pay Delete'],
        ]],
        ['Sales', 'sales', [
          ['sales_add','Add'], ['sales_edit','Edit'], ['sales_delete','Delete'], ['sales_view','View'],
          ['sales_payment_view','Pay View'], ['sales_payment_add','Pay Add'], ['sales_payment_delete','Pay Delete'],
        ]],
        ['Sales Return', 'sales_return', [
          ['sales_return_add','Add'], ['sales_return_edit','Edit'], ['sales_return_delete','Delete'], ['sales_return_view','View'],
          ['sales_return_payment_view','Pay View'], ['sales_return_payment_add','Pay Add'], ['sales_return_payment_delete','Pay Delete'],
        ]],
        ['POS', 'pos', [
          ['pos','POS Access'],
        ]],
        ['Quotation', 'quotation', [
          ['quotation_add','Add'], ['quotation_edit','Edit'], ['quotation_delete','Delete'], ['quotation_view','View'], ['import_services','Import'],
        ]],
        ['Stock Transfer', 'stock_transfer', [
          ['stock_transfer_add','Add'], ['stock_transfer_edit','Edit'], ['stock_transfer_delete','Delete'], ['stock_transfer_view','View'],
        ]],
        ['Stock Adjustment', 'stock_adjustment', [
          ['stock_adjustment_add','Add'], ['stock_adjustment_edit','Edit'], ['stock_adjustment_delete','Delete'], ['stock_adjustment_view','View'],
        ]],
        ['Warehouse / Branch', 'warehouse', [
          ['warehouse_add','Add'], ['warehouse_edit','Edit'], ['warehouse_delete','Delete'], ['warehouse_view','View'],
        ]],
        ['Services', 'services', [
          ['services_add','Add'], ['services_edit','Edit'], ['services_delete','Delete'], ['services_view','View'],
        ]],
        ['Service Packages', 'service_packages', [
          ['service_packages_add','Add'], ['service_packages_edit','Edit'], ['service_packages_delete','Delete'], ['service_packages_view','View'],
        ]],
        ['Memberships', 'memberships', [
          ['memberships_add','Add'], ['memberships_edit','Edit'], ['memberships_delete','Delete'], ['memberships_view','View'],
        ]],
        ['Treatment Notes', 'treatment_notes', [
          ['treatment_notes_add','Add'], ['treatment_notes_edit','Edit'], ['treatment_notes_delete','Delete'], ['treatment_notes_view','View'],
        ]],
        ['Custom Orders', 'custom_orders', [
          ['custom_orders_add','Add'], ['custom_orders_edit','Edit'], ['custom_orders_delete','Delete'], ['custom_orders_view','View'],
        ]],
        ['Production Batches', 'production_batches', [
          ['production_batches_add','Add'], ['production_batches_edit','Edit'], ['production_batches_delete','Delete'], ['production_batches_view','View'],
        ]],
        ['Recipes', 'recipes', [
          ['recipes_add','Add'], ['recipes_edit','Edit'], ['recipes_delete','Delete'], ['recipes_view','View'],
        ]],
        ['Accounts', 'accounts', [
          ['accounts_add','Add'], ['accounts_edit','Edit'], ['accounts_delete','Delete'], ['accounts_view','View'],
        ]],
        ['Money Transfer', 'money_transfer', [
          ['money_transfer_add','Add'], ['money_transfer_edit','Edit'], ['money_transfer_delete','Delete'], ['money_transfer_view','View'],
        ]],
        ['Money Deposit', 'money_deposit', [
          ['money_deposit_add','Add'], ['money_deposit_edit','Edit'], ['money_deposit_delete','Delete'], ['money_deposit_view','View'],
        ]],
        ['Cash Transactions', 'cash_transactions', [
          ['cash_transactions','View'],
        ]],
        ['Discount Coupons', 'discount_coupon', [
          ['discountCouponAdd','Add'], ['discountCouponEdit','Edit'], ['discountCouponDelete','Delete'], ['discountCouponView','View'],
        ]],
        ['Customer Coupons', 'customer_coupon', [
          ['customerCouponAdd','Add'], ['customerCouponEdit','Edit'], ['customerCouponDelete','Delete'], ['customerCouponView','View'],
        ]],
        ['Loyalty & Rewards', 'loyalty', [
          ['loyalty_view','View'], ['loyalty_add','Add'], ['loyalty_edit','Edit'], ['loyalty_delete','Delete'],
        ]],
        ['Gift Cards', 'gift_cards', [
          ['gift_cards_view','View'], ['gift_cards_add','Add'], ['gift_cards_edit','Edit'], ['gift_cards_delete','Delete'],
        ]],
        ['Store Credit', 'store_credit', [
          ['store_credit_view','View'], ['store_credit_add','Add'], ['store_credit_edit','Edit'], ['store_credit_delete','Delete'],
        ]],
        ['Installments', 'installments', [
          ['installment_plans','Plans'], ['installment_payment','Payment'], ['installment_report','Report'],
        ]],
        ['NIN/BVN Verification', 'nin', [
          ['nin_verify','Verify'], ['nin_settings','Settings'], ['nin_usage','Usage'], ['nin_logs','Logs'],
        ]],
        ['Online Store', 'online_store', [
          ['online_store_view','View'], ['online_store_edit','Edit'], ['online_store_orders','Orders'],
        ]],
        ['Approvals', 'approvals', [
          ['approval_settings_edit','Settings'], ['approval_logs_view','Logs'], ['can_approve','Can Approve'],
        ]],
        ['SMS', 'sms', [
          ['send_sms','Send'], ['sms_template_edit','Template Edit'], ['sms_template_view','Template View'], ['sms_api_view','API View'], ['sms_api_edit','API Edit'], ['sms_settings','Settings'],
        ]],
        ['Email', 'email', [
          ['send_email','Send'], ['email_template_edit','Template Edit'], ['email_template_view','Template View'], ['smtp_settings','SMTP Settings'],
        ]],
        ['Dashboard', 'dashboard', [
          ['dashboard_view','View'], ['dashboard_info_box_1','Box 1'], ['dashboard_info_box_2','Box 2'],
          ['dashboard_pur_sal_chart','Chart'], ['dashboard_recent_items','Recent Items'],
          ['dashboard_expired_items','Expired Items'], ['dashboard_stock_alert','Stock Alert'],
          ['dashboard_trending_items_chart','Trending Chart'],
        ]],
        ['Reports', 'reports', [
          ['sales_report','Sales'], ['purchase_report','Purchase'], ['expense_report','Expense'],
          ['profit_report','Profit'], ['stock_report','Stock'], ['item_sales_report','Item Sales'],
          ['purchase_payments_report','Purchase Payments'], ['sales_payments_report','Sales Payments'],
          ['expired_items_report','Expired Items'], ['stock_transfer_report','Stock Transfer'],
          ['supplier_items_report','Supplier Items'], ['seller_points_report','Seller Points'],
          ['sales_return_report','Sales Return'], ['purchase_return_report','Purchase Return'],
          ['return_items_report','Return Items'], ['sales_summary_report','Sales Summary'],
          ['sales_tax_report','Sales Tax'], ['purchase_tax_report','Purchase Tax'],
          ['sales_gst_report','Sales GST'], ['purchase_gst_report','Purchase GST'],
          ['gstr_1_report','GSTR-1'], ['gstr_2_report','GSTR-2'],
          ['customer_orders_report','Customer Orders'], ['load_sheet_report','Load Sheet'],
          ['delivery_sheet_report','Delivery Sheet'], ['sales_return_payments','Sales Return Payments'],
          ['show_purchase_price','Show Purchase Price'],
        ]],
        ['Cross-User Visibility', 'cross_user', [
          ['show_all_users_sales_invoices','Sales'], ['show_all_users_sales_return_invoices','Sales Return'],
          ['show_all_users_purchase_invoices','Purchase'], ['show_all_users_purchase_return_invoices','Purchase Return'],
          ['show_all_users_expenses','Expenses'], ['show_all_users_quotations','Quotations'],
        ]],
        ['Debt Reminder', 'debt_reminder', [
          ['debt_reminder_view','View'], ['debt_reminder_edit','Edit'],
        ]],
        ['Subscription', 'subscription', [
          ['subscription','View'],
        ]],
        ['Expiry Settings', 'expiry_settings', [
          ['expiry_settings','Settings'],
        ]],
        ['Paystack Settings', 'paystack', [
          ['paystack_settings','Settings'],
        ]],
        ['Print Labels', 'print_labels', [
          ['print_labels','Print'],
        ]],
        ['Recent Sales', 'recent_sales', [
          ['recent_sales_invoice_list','View'],
        ]],
      ];

      // Fetch existing permissions for this role (edit mode)
      $existing_perms = [];
      if (!empty($q_id)) {
        $q1 = $this->db->query("SELECT permissions FROM db_permissions WHERE role_id=" . (int)$q_id);
        foreach ($q1->result() as $row) {
          $existing_perms[$row->permissions] = true;
        }
      }

      $i = 1;
      foreach ($permission_groups as $group):
        $label = $group[0];
        $gid = $group[1];
        $perms = $group[2];
      ?>
        <tr class="perm-row" data-module="<?= htmlspecialchars(strtolower($label)); ?>">
          <td><?= $i++; ?></td>
          <td class="mod-name"><?= htmlspecialchars($label); ?></td>
          <td class="select-all-cell">
            <input type="checkbox" class="perm-select-all" id="sa_<?= $gid; ?>" data-group="<?= $gid; ?>">
          </td>
          <td class="perm-cell">
            <?php foreach ($perms as $p): $key = $p[0]; $plabel = $p[1]; ?>
              <label>
                <input type="checkbox" class="perm-item <?= $gid; ?>_all" name="permission[<?= $key; ?>]" id="<?= $key; ?>"
                  <?= isset($existing_perms[$key]) ? 'checked' : ''; ?>>
                <?= htmlspecialchars($plabel); ?>
              </label>
            <?php endforeach; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="mp-perms-actions">
    <button type="button" class="mp-btn-sm" id="perm_check_all"><i class="fa fa-check-square-o"></i> Check All</button>
    <button type="button" class="mp-btn-sm" id="perm_uncheck_all"><i class="fa fa-square-o"></i> Uncheck All</button>
  </div>
</div>

<div class="mp-form-actions" style="margin-top:24px; display:flex; gap:12px; justify-content:center;">
  <?php $is_edit = (!empty($q_id)); ?>
  <button type="button" id="<?= $is_edit ? 'update' : 'save'; ?>" class="mp-qa-btn green"><i class="fa fa-save"></i> <?= $is_edit ? 'Update' : 'Save'; ?></button>
  <a href="<?= base_url('roles/view'); ?>" class="mp-qa-btn">Close</a>
</div>
</form>

<script src="<?= $theme_link; ?>js/roles.js"></script>
<script type="text/javascript">
  // Select-all for each module group
  $('.perm-select-all').on('change', function() {
    var group = $(this).data('group');
    var checked = this.checked;
    $('.' + group + '_all').prop('checked', checked);
  });

  // Check/Uncheck all modules
  $('#perm_check_all').on('click', function() {
    $('.perm-item, .perm-select-all').prop('checked', true);
  });
  $('#perm_uncheck_all').on('click', function() {
    $('.perm-item, .perm-select-all').prop('checked', false);
  });

  // Update select-all state when individual items change
  $('.perm-item').on('change', function() {
    var cls = $(this).attr('class').split(' ').filter(function(c){ return c.endsWith('_all'); });
    if (cls.length) {
      var group = cls[0].replace('_all','');
      var all = $('.' + group + '_all');
      var sa = $('#sa_' + group);
      sa.prop('checked', all.length === all.filter(':checked').length);
    }
  });

  // Search filter
  $('#perms_search').on('keyup', function() {
    var q = $(this).val().toLowerCase();
    $('.perm-row').each(function() {
      var mod = $(this).data('module');
      $(this).toggle(mod.indexOf(q) > -1);
    });
  });

  // Sidebar active state
  $(".roles-add-active-li").addClass("active");
  $(".roles-add-active-li").closest(".mp-nav-group").addClass("open");
  if (!$(".roles-add-active-li").length) {
    $(".roles-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
  }
</script>
