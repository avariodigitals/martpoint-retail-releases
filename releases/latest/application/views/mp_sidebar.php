<?php
$CI =& get_instance();
// SVG icon set (Feather-style, matching prototype)
$mp_icons = [
  'dashboard' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
  'pos'       => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
  'sales'     => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
  'catalog'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
  'promo'     => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
  'purchase'  => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
  'inventory' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
  'customers' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
  'finance'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
  'marketing' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 11l8-8 8 8-8 8-8-8z"/><path d="M7 7l10 10"/></svg>',
  'reports'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M23 6l-9.5 9.5-5-5L1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
  'online'    => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
  'ops'       => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
  'admin'     => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
  'list'      => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
  'plus'      => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
  'chevron'   => '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>',
];
?>
<!-- ===== SHELL ===== -->
<div class="mp-shell">
  <nav class="mp-nav">
    <div class="mp-nav-section">
      <a href="<?= base_url('dashboard'); ?>" class="mp-nav-item active"><span class="mp-nav-icon"><?= $mp_icons['dashboard']; ?></span> Dashboard</a>
    </div>

    <!-- Sales -->
    <?php if($CI->permissions('sales_add') || $CI->permissions('sales_view') || $CI->permissions('sales_return_view') || $CI->permissions('quotation_add') || $CI->permissions('quotation_view')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#F97316;"><?= $mp_icons['sales']; ?></span> Sales <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('sales_add')): ?><a href="<?= base_url('sales/add'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Sale</a><?php endif; ?>
        <?php if($CI->permissions('sales_view')): ?><a href="<?= base_url('sales'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales History</a><?php endif; ?>
        <?php if($CI->permissions('sales_payment_view')): ?><a href="<?= base_url('sales_payments/'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Payments</a><?php endif; ?>
        <?php if($CI->permissions('installment_plans') && mp_feature_enabled('payplan')): ?><a href="<?= base_url('installments'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Installments</a><?php endif; ?>
        <?php if($CI->permissions('sales_return_view')): ?><a href="<?= base_url('sales_return'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Returns</a><?php endif; ?>
        <?php if($CI->permissions('quotation_add')): ?><a href="<?= base_url('quotation/add'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Quotation</a><?php endif; ?>
        <?php if($CI->permissions('quotation_view')): ?><a href="<?= base_url('quotation'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Quotation History</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Catalog -->
    <?php if($CI->permissions('items_add') || $CI->permissions('items_view') || $CI->permissions('items_category_view') || $CI->permissions('brand_view') || $CI->permissions('attributes_view') || $CI->permissions('print_labels') || $CI->permissions('import_items') || $CI->permissions('services_add') || $CI->permissions('services_view') || $CI->permissions('service_packages_view') || $CI->permissions('variant_view') || (mp_feature_enabled('price_catalogue') && (is_admin() || is_store_admin()))): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#2563EB;"><?= $mp_icons['catalog']; ?></span> Catalog <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('items_add')): ?><a href="<?= base_url('items/add'); ?>" class="mp-nav-item items-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New <?= mp_label('item'); ?></a><?php endif; ?>
        <?php if($CI->permissions('services_add') && service_module()): ?><a href="<?= base_url('services/add'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Service</a><?php endif; ?>
        <?php if($CI->permissions('service_packages_view') && service_module()): ?><a href="<?= base_url('service_packages'); ?>" class="mp-nav-item service-packages-list-active-li service-packages-view-active-li service_packages-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Service Packages</a><?php endif; ?>
        <?php if($CI->permissions('items_view') || $CI->permissions('services_view') || $CI->permissions('services_add')): ?><a href="<?= base_url('items'); ?>" class="mp-nav-item items-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= mp_label('item'); ?> List</a><?php endif; ?>
        <?php if($CI->permissions('items_category_view')): ?><a href="<?= base_url('category/view'); ?>" class="mp-nav-item category-view-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Categories</a><?php endif; ?>
        <?php if($CI->permissions('brand_view')): ?><a href="<?= base_url('brands/view'); ?>" class="mp-nav-item brand-view-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Brands</a><?php endif; ?>
        <?php if($CI->permissions('attributes_view')): ?><a href="<?= base_url('attributes'); ?>" class="mp-nav-item attributes-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Attributes</a><?php endif; ?>
        <?php if($CI->permissions('print_labels')): ?><a href="<?= base_url('items/labels'); ?>" class="mp-nav-item labels-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Print Labels</a><?php endif; ?>
        <?php if($CI->permissions('import_items')): ?><a href="<?= base_url('import/items'); ?>" class="mp-nav-item import_items-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Import Items</a><?php endif; ?>
        <?php if($CI->permissions('import_services') && service_module()): ?><a href="<?= base_url('import/services'); ?>" class="mp-nav-item import_services-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Import Services</a><?php endif; ?>
        <?php if(mp_feature_enabled('price_catalogue') && (is_admin() || is_store_admin())): ?><a href="<?= base_url('operations/price_catalogue'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Price Catalogue</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Promotions -->
    <?php if($CI->permissions('promotions_manage')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#E11D48;"><?= $mp_icons['promo']; ?></span> Promotions <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('promotions'); ?>" class="mp-nav-item promotions_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Promotion List</a>
        <a href="<?= base_url('promotions/add'); ?>" class="mp-nav-item promotion_form-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> Add Promotion</a>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Purchases -->
    <?php if($CI->permissions('purchase_add') || $CI->permissions('purchase_view') || $CI->permissions('purchase_return_view')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#059669;"><?= $mp_icons['purchase']; ?></span> Purchases <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('purchase_add')): ?><a href="<?= base_url('purchase/add'); ?>" class="mp-nav-item purchase-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Purchase</a><?php endif; ?>
        <?php if($CI->permissions('purchase_view')): ?><a href="<?= base_url('purchase'); ?>" class="mp-nav-item purchase-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Purchase History</a><?php endif; ?>
        <?php if($CI->permissions('purchase_return_view')): ?><a href="<?= base_url('purchase_return'); ?>" class="mp-nav-item purchase-returns-active-li purchase-returns-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Purchase Returns</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Inventory -->
    <?php if($CI->permissions('stock_adjustment_view') || $CI->permissions('stock_transfer_view')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#F59E0B;"><?= $mp_icons['inventory']; ?></span> Inventory <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('inventory'); ?>" class="mp-nav-item inventory-active-li"><span class="mp-nav-icon"><?= $mp_icons['dashboard']; ?></span> Overview</a>
        <?php if($CI->permissions('stock_adjustment_add')): ?><a href="<?= base_url('stock_adjustment/add'); ?>" class="mp-nav-item stock_adjustment_form-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Adjustment</a><?php endif; ?>
        <?php if($CI->permissions('stock_adjustment_view')): ?><a href="<?= base_url('stock_adjustment'); ?>" class="mp-nav-item stock_adjustment_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Stock Adjustments</a><?php endif; ?>
        <?php if($CI->permissions('stock_transfer_add') && warehouse_module()): ?><a href="<?= base_url('stock_transfer/add'); ?>" class="mp-nav-item stock_transfer_form-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Transfer</a><?php endif; ?>
        <?php if($CI->permissions('stock_transfer_view') && warehouse_module()): ?><a href="<?= base_url('stock_transfer/view'); ?>" class="mp-nav-item stock_transfer_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Stock Transfers</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Customers -->
    <?php if($CI->permissions('customers_add') || $CI->permissions('customers_view') || $CI->permissions('suppliers_add') || $CI->permissions('suppliers_view') || $CI->permissions('import_customers') || $CI->permissions('import_suppliers') || $CI->permissions('cust_adv_payments_add') || $CI->permissions('cust_adv_payments_view')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#7C3AED;"><?= $mp_icons['customers']; ?></span> <?= mp_label('customer'); ?>s <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('customers_add')): ?><a href="<?= base_url('customers/add'); ?>" class="mp-nav-item customers_add-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New <?= mp_label('customer'); ?></a><?php endif; ?>
        <?php if($CI->permissions('customers_view')): ?><a href="<?= base_url('customers'); ?>" class="mp-nav-item customers_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= mp_label('customer'); ?> List</a><?php endif; ?>
        <?php if($CI->permissions('suppliers_add')): ?><a href="<?= base_url('suppliers/add'); ?>" class="mp-nav-item suppliers_add-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Supplier</a><?php endif; ?>
        <?php if($CI->permissions('suppliers_view')): ?><a href="<?= base_url('suppliers'); ?>" class="mp-nav-item suppliers_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Supplier List</a><?php endif; ?>
        <?php if($CI->permissions('import_customers')): ?><a href="<?= base_url('import/customers'); ?>" class="mp-nav-item import_customers-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Import Customers</a><?php endif; ?>
        <?php if($CI->permissions('import_suppliers')): ?><a href="<?= base_url('import/suppliers'); ?>" class="mp-nav-item import_suppliers-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Import Suppliers</a><?php endif; ?>
        <?php if($CI->permissions('cust_adv_payments_add')): ?><a href="<?= base_url('customers_advance/add'); ?>" class="mp-nav-item customers_advance_add-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Advance</a><?php endif; ?>
        <?php if($CI->permissions('cust_adv_payments_view')): ?><a href="<?= base_url('customers_advance'); ?>" class="mp-nav-item customers_advance_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Advance List</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Finance -->
    <?php if((($CI->permissions('accounts_add') || $CI->permissions('accounts_view') || $CI->permissions('journal_add') || $CI->permissions('journal_view') || $CI->permissions('money_transfer_view') || $CI->permissions('money_deposit_view') || $CI->permissions('cash_transactions')) && accounts_module()) || $CI->permissions('expense_view') || $CI->permissions('expense_category_view') || $CI->permissions('tills_view')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#E11D48;"><?= $mp_icons['finance']; ?></span> Finance <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if(($CI->permissions('accounts_add') || $CI->permissions('accounts_view') || $CI->permissions('journal_view')) && accounts_module()): ?>
          <?php if($CI->permissions('accounts_add')): ?><a href="<?= base_url('accounts/add'); ?>" class="mp-nav-item accounts-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Account</a><?php endif; ?>
          <?php if($CI->permissions('accounts_view')): ?><a href="<?= base_url('accounts'); ?>" class="mp-nav-item accounts_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Account List</a><?php endif; ?>
          <?php if($CI->permissions('money_transfer_view')): ?><a href="<?= base_url('money_transfer'); ?>" class="mp-nav-item money_transfer_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Money Transfers</a><?php endif; ?>
          <?php if($CI->permissions('money_deposit_view')): ?><a href="<?= base_url('money_deposit'); ?>" class="mp-nav-item money_deposit_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Deposits</a><?php endif; ?>
          <?php if($CI->permissions('cash_transactions')): ?><a href="<?= base_url('accounts/cash_transactions'); ?>" class="mp-nav-item cash_transactions-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Cash Transactions</a><?php endif; ?>
        <?php endif; ?>
        <?php if($CI->permissions('tills_view')): ?><a href="<?= base_url('tills'); ?>" class="mp-nav-item tills-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Tills</a><?php endif; ?>
        <?php if($CI->permissions('expense_view')): ?><a href="<?= base_url('expense'); ?>" class="mp-nav-item expense-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Expense List</a><?php endif; ?>
        <?php if($CI->permissions('expense_category_view')): ?><a href="<?= base_url('expense/category'); ?>" class="mp-nav-item expense-category-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Expense Categories</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Marketing -->
    <?php if(($CI->permissions('discountCouponView') || $CI->permissions('customerCouponView')) || ($CI->permissions('loyalty_view') && mp_feature_enabled('loyalty')) || ($CI->permissions('gift_cards_view') && mp_feature_enabled('gift_cards')) || ($CI->permissions('store_credit_view') && mp_feature_enabled('store_credit'))): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#C026D3;"><?= $mp_icons['marketing']; ?></span> Marketing <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('marketing'); ?>" class="mp-nav-item marketing-overview-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Marketing Overview</a>
        <?php if(($CI->permissions('discountCouponView') || $CI->permissions('customerCouponView')) && !is_admin()): ?>
          <?php if($CI->permissions('customerCouponAdd')): ?><a href="<?= base_url('customer_coupon/generate'); ?>" class="mp-nav-item createCoupon-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> Create Customer Coupon</a><?php endif; ?>
          <?php if($CI->permissions('customerCouponView')): ?><a href="<?= base_url('customer_coupon'); ?>" class="mp-nav-item customerCouponsList-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Customer Coupons</a><?php endif; ?>
          <?php if($CI->permissions('discountCouponAdd')): ?><a href="<?= base_url('discount_coupon/add'); ?>" class="mp-nav-item createDiscountCoupon-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> Create Discount Coupon</a><?php endif; ?>
          <?php if($CI->permissions('discountCouponView')): ?><a href="<?= base_url('discount_coupon/view'); ?>" class="mp-nav-item discountCoupon-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Discount Coupons</a><?php endif; ?>
        <?php endif; ?>
        <?php if($CI->permissions('loyalty_view') && mp_feature_enabled('loyalty')): ?>
          <a href="<?= base_url('loyalty'); ?>" class="mp-nav-item loyalty-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Loyalty Dashboard</a>
          <a href="<?= base_url('loyalty/settings'); ?>" class="mp-nav-item loyalty-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Loyalty Settings</a>
          <a href="<?= base_url('loyalty/tiers'); ?>" class="mp-nav-item loyalty-tiers-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Customer Tiers</a>
          <a href="<?= base_url('loyalty/points_history'); ?>" class="mp-nav-item loyalty-points-history-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Points History</a>
          <a href="<?= base_url('loyalty/bonus_rules'); ?>" class="mp-nav-item loyalty-bonus-rules-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Bonus Rules</a>
          <a href="<?= base_url('loyalty/product_points'); ?>" class="mp-nav-item loyalty-product-points-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Product Points</a>
          <a href="<?= base_url('loyalty/referral_program'); ?>" class="mp-nav-item loyalty-referral-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Referral Program</a>
        <?php endif; ?>
        <?php if($CI->permissions('gift_cards_view') && mp_feature_enabled('gift_cards')): ?><a href="<?= base_url('gift_cards'); ?>" class="mp-nav-item gift-cards-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Gift Cards</a><?php endif; ?>
        <?php if($CI->permissions('store_credit_view') && mp_feature_enabled('store_credit')): ?><a href="<?= base_url('store_credit'); ?>" class="mp-nav-item store-credit-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Store Credit</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Reports -->
    <?php if($CI->permissions('sales_report') || $CI->permissions('profit_report') || $CI->permissions('stock_report') || $CI->permissions('expense_report') || $CI->permissions('purchase_report') || $CI->permissions('item_sales_report') || $CI->permissions('expired_items_report') || $CI->permissions('dashboard_view')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#F97316;"><?= $mp_icons['reports']; ?></span> Reports <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('dashboard_view')): ?><a href="<?= base_url('dashboard/daily_summary'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Daily Business Summary</a><?php endif; ?>
        <?php if($CI->permissions('profit_report')): ?><a href="<?= base_url('reports/profit_loss'); ?>" class="mp-nav-item report-profit-loss-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Profit & Loss</a><?php endif; ?>
        <?php if($CI->permissions('z_report') && mp_feature_enabled('cashier_shifts')): ?><a href="<?= base_url('cashier_shifts'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Z-Report</a><?php endif; ?>
        <?php if($CI->permissions('cashier_shifts_manage') && mp_feature_enabled('cashier_shifts')): ?><a href="<?= base_url('cashier_shifts/manage'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Open/Close Shift</a><?php endif; ?>
        <?php if($CI->permissions('receivables_aging_report')): ?><a href="<?= base_url('reports/receivables_aging'); ?>" class="mp-nav-item report-receivables-aging-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Receivables Aging</a><?php endif; ?>
        <?php if($CI->permissions('inventory_aging_report')): ?><a href="<?= base_url('reports/inventory_aging'); ?>" class="mp-nav-item report-inventory-aging-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Inventory Aging</a><?php endif; ?>
        <?php if($CI->permissions('cash_flow_report')): ?><a href="<?= base_url('reports/cash_flow'); ?>" class="mp-nav-item report-cash-flow-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Cash Flow</a><?php endif; ?>
        <?php if($CI->permissions('variant_attribute_report')): ?><a href="<?= base_url('reports/variant_attribute'); ?>" class="mp-nav-item report-variant-attribute-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Variant Attribute</a><?php endif; ?>
        <?php if($CI->permissions('sell_through_report')): ?><a href="<?= base_url('reports/sell_through'); ?>" class="mp-nav-item report-sell-through-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sell Through</a><?php endif; ?>
        <?php if($CI->permissions('reorder_suggestion_report')): ?><a href="<?= base_url('reports/reorder_suggestion'); ?>" class="mp-nav-item report-reorder-suggestion-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Reorder Suggestion</a><?php endif; ?>
        <?php if($CI->permissions('sales_report')): ?><a href="<?= base_url('reports/sales_and_payments'); ?>" class="mp-nav-item report-sales-and-payments-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales & Payments</a><?php endif; ?>
        <?php if($CI->permissions('customer_orders_report')): ?><a href="<?= base_url('reports/customer_orders'); ?>" class="mp-nav-item report-customer-orders-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Customer Orders</a><?php endif; ?>
        <?php if($CI->permissions('sales_report')): ?><a href="<?= base_url('reports/sales'); ?>" class="mp-nav-item report-sales-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Report</a><?php endif; ?>
        <?php if($CI->permissions('sales_return_report')): ?><a href="<?= base_url('reports/sales_return'); ?>" class="mp-nav-item report-sales-return-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Return Report</a><?php endif; ?>
        <?php if($CI->permissions('purchase_report')): ?><a href="<?= base_url('reports/purchase'); ?>" class="mp-nav-item report-purchase-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Purchase Report</a><?php endif; ?>
        <?php if($CI->permissions('purchase_return_report')): ?><a href="<?= base_url('reports/purchase_return'); ?>" class="mp-nav-item report-purchase-return-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Purchase Return Report</a><?php endif; ?>
        <?php if($CI->permissions('expense_report')): ?><a href="<?= base_url('reports/expense'); ?>" class="mp-nav-item report-expense-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Expense Report</a><?php endif; ?>
        <?php if($CI->permissions('stock_report')): ?><a href="<?= base_url('reports/stock'); ?>" class="mp-nav-item report-stock-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Stock Report</a><?php endif; ?>
        <?php if($CI->permissions('item_sales_report')): ?><a href="<?= base_url('reports/item_sales'); ?>" class="mp-nav-item report-sales-item-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Item Sales Report</a><?php endif; ?>
        <?php if($CI->permissions('purchase_payments_report')): ?><a href="<?= base_url('reports/purchase_payments'); ?>" class="mp-nav-item report-purchase-payments-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Purchase Payments</a><?php endif; ?>
        <?php if($CI->permissions('sales_payments_report')): ?><a href="<?= base_url('reports/sales_payments'); ?>" class="mp-nav-item report-sales-payments-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Payments</a><?php endif; ?>
        <?php if($CI->permissions('stock_transfer_report')): ?><a href="<?= base_url('reports/stock_transfer'); ?>" class="mp-nav-item report-stock-transfer-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Stock Transfer Report</a><?php endif; ?>
        <?php if($CI->permissions('sales_summary_report')): ?><a href="<?= base_url('reports/sales_summary'); ?>" class="mp-nav-item report-sales-summary-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Summary</a><?php endif; ?>
        <?php if($CI->permissions('expired_items_report')): ?><a href="<?= base_url('expired_items_report'); ?>" class="mp-nav-item report-expired-items-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Expired Items</a><?php endif; ?>
        <?php if($CI->permissions('sales_return_payments')): ?><a href="<?= base_url('reports/sales_return_payments'); ?>" class="mp-nav-item report-sales-return-payments-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Return Payments</a><?php endif; ?>
        <?php if($CI->permissions('supplier_items_report')): ?><a href="<?= base_url('reports/supplier_items'); ?>" class="mp-nav-item report-supplier-items-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Supplier Items</a><?php endif; ?>
        <?php if($CI->permissions('seller_points_report')): ?><a href="<?= base_url('reports/seller_points'); ?>" class="mp-nav-item report-seller-points-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Seller Points</a><?php endif; ?>
        <?php if(($CI->permissions('approval_logs_view') || is_store_admin() || $this->session->userdata('role_id') == 1) && mp_feature_enabled('manager_approvals')): ?><a href="<?= base_url('approvals/logs'); ?>" class="mp-nav-item approvals-logs-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Approval Logs</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Online Store -->
    <?php if(($CI->permissions('online_store_view') || $CI->permissions('online_store_orders') || is_store_admin() || $this->session->userdata('role_id') == 1) && mp_feature_enabled('online_store')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#059669;"><?= $mp_icons['online']; ?></span> Online Store <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('online_store_view') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
          <a href="<?= base_url('online_store'); ?>" class="mp-nav-item online_store-active-li"><span class="mp-nav-icon"><?= $mp_icons['dashboard']; ?></span> Store Dashboard</a>
          <a href="<?= base_url('online_store/analytics'); ?>" class="mp-nav-item online_store-analytics-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Analytics</a>
        <?php endif; ?>
        <?php if($CI->permissions('online_store_orders') || $CI->permissions('online_store_view') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
          <a href="<?= base_url('online_store/orders'); ?>" class="mp-nav-item online_store-orders-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Orders</a>
        <?php endif; ?>
        <?php if($CI->permissions('online_store_view') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
          <a href="<?= base_url('online_store/products_online'); ?>" class="mp-nav-item online_store-products-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Online Products</a>
          <a href="<?= base_url('online_store/services'); ?>" class="mp-nav-item online_store-services-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Services</a>
          <?php if(mp_feature_enabled('qr_ordering')): ?><a href="<?= base_url('online_store/qr_codes'); ?>" class="mp-nav-item online_store-qr-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> QR Codes</a><?php endif; ?>
        <?php endif; ?>
        <?php if($CI->permissions('online_store_edit') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
          <div class="mp-nav-subhead">Storefront Content</div>
          <a href="<?= base_url('online_store/homepage_builder'); ?>" class="mp-nav-item online_store-homepage_builder-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Homepage Builder</a>
          <a href="<?= base_url('online_store/banners'); ?>" class="mp-nav-item online_store-banners-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Banners</a>
          <a href="<?= base_url('online_store/brands'); ?>" class="mp-nav-item online_store-brands-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Brands</a>
          <a href="<?= base_url('online_store/testimonials'); ?>" class="mp-nav-item online_store-testimonials-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Testimonials</a>
          <a href="<?= base_url('online_store/instagram'); ?>" class="mp-nav-item online_store-instagram-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Instagram</a>
          <a href="<?= base_url('online_store/faqs'); ?>" class="mp-nav-item online_store-faqs-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> FAQs</a>
          <div class="mp-nav-subhead">Configuration</div>
          <a href="<?= base_url('online_store/appearance'); ?>" class="mp-nav-item online_store-appearance-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Appearance</a>
          <a href="<?= base_url('online_store/domains'); ?>" class="mp-nav-item online_store-domains-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Domains</a>
          <a href="<?= base_url('online_store/settings'); ?>" class="mp-nav-item online_store-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Store Settings</a>
        <?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Operations -->
    <?php
      $ops_flags = ['custom_orders','memberships','treatment_notes','medical_notes','kitchen_workflow','laundry_workflow','production_workflow','recipe_tracking','public_catalogue','delivery_scheduling','serial_number_tracking','imei_tracking','warranty_tracking'];
      $has_ops = false; foreach ($ops_flags as $f) { if (mp_feature_enabled($f)) { $has_ops = true; break; } }
      $has_staff = (mp_feature_enabled('staff_assignment') || mp_feature_enabled('staff_commission')) && (is_admin() || is_store_admin());
      $has_tables = mp_feature_enabled('table_management') && (is_admin() || is_store_admin());
    ?>
    <?php if($has_ops || $has_staff || $has_tables): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#F97316;"><?= $mp_icons['ops']; ?></span> Operations <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if(mp_feature_enabled('custom_orders')): ?><a href="<?= base_url('operations/custom_orders'); ?>" class="mp-nav-item operations-custom_orders-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Custom Orders</a><?php endif; ?>
        <?php if(mp_feature_enabled('production_workflow')): ?><a href="<?= base_url('operations/production_schedule'); ?>" class="mp-nav-item operations-production_schedule-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Production Schedule</a><?php endif; ?>
        <?php if(mp_feature_enabled('recipe_tracking')): ?><a href="<?= base_url('operations/recipes'); ?>" class="mp-nav-item operations-recipes-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Recipe Book</a><?php endif; ?>
        <?php if(mp_feature_enabled('memberships')): ?><a href="<?= base_url('operations/memberships'); ?>" class="mp-nav-item operations-memberships-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Memberships</a><?php endif; ?>
        <?php if(mp_feature_enabled('treatment_notes')): ?><a href="<?= base_url('operations/treatment_notes'); ?>" class="mp-nav-item operations-treatment_notes-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Treatment Notes</a><?php endif; ?>
        <?php if(mp_feature_enabled('medical_notes')): ?><a href="<?= base_url('operations/medical_notes'); ?>" class="mp-nav-item operations-medical_notes-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Medical Notes</a><?php endif; ?>
        <?php if(mp_feature_enabled('kitchen_workflow')): ?><a href="<?= base_url('operations/kitchen'); ?>" class="mp-nav-item operations-kitchen-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Kitchen Display</a><?php endif; ?>
        <?php if(mp_feature_enabled('laundry_workflow')): ?><a href="<?= base_url('operations/laundry'); ?>" class="mp-nav-item operations-laundry-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Laundry Workflow</a><?php endif; ?>
        <?php if(mp_feature_enabled('delivery_scheduling')): ?><a href="<?= base_url('operations/delivery_scheduling'); ?>" class="mp-nav-item operations-delivery_scheduling-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Delivery Scheduling</a><?php endif; ?>
        <?php if(mp_feature_enabled('public_catalogue')): ?><a href="<?= base_url('operations/public_catalogue_settings'); ?>" class="mp-nav-item operations-public_catalogue_settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Public Catalogue</a><?php endif; ?>
        <?php if(mp_feature_enabled('serial_number_tracking') || mp_feature_enabled('imei_tracking') || mp_feature_enabled('warranty_tracking')): ?><a href="<?= base_url('operations/warranty_lookup'); ?>" class="mp-nav-item operations-warranty_lookup-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Warranty Lookup</a><?php endif; ?>
        <?php if($has_staff && mp_feature_enabled('staff_assignment')): ?><a href="<?= base_url('operations/staff_assignment'); ?>" class="mp-nav-item operations-staff_assignment-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Staff Assignment</a><?php endif; ?>
        <?php if($has_staff && mp_feature_enabled('staff_commission')): ?><a href="<?= base_url('operations/staff_commission'); ?>" class="mp-nav-item operations-staff_commission-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Staff Commission</a><?php endif; ?>
        <?php if($has_tables): ?><a href="<?= base_url('operations/table_management'); ?>" class="mp-nav-item operations-table_management-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Table Management</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Administration -->
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#78716C;"><?= $mp_icons['admin']; ?></span> Administration <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('admin'); ?>" class="mp-nav-item admin-dashboard-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Admin Dashboard</a>
        <?php if($CI->permissions('store_edit')): ?><a href="<?= base_url('store_profile/update/'.$this->session->userdata('store_id')); ?>" class="mp-nav-item store_profile-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Store Profile</a><?php endif; ?>
        <?php if($CI->permissions('store_edit')): ?><a href="<?= base_url('business_profile'); ?>" class="mp-nav-item business_profile-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Business Profile</a><?php endif; ?>
        <?php if(($CI->permissions('warehouse_view') || $CI->permissions('warehouse_add')) && warehouse_module()):
          try { $branch_label_nav = mp_label('branch','Branches'); } catch (Exception $e) { $branch_label_nav = 'Branches'; }
        ?>
          <?php if($CI->permissions('warehouse_add')): ?><a href="<?= base_url('warehouse/add'); ?>" class="mp-nav-item warehouse-add-active-li warehouse-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New <?= $branch_label_nav; ?></a><?php endif; ?>
          <?php if($CI->permissions('warehouse_view')): ?><a href="<?= base_url('warehouse'); ?>" class="mp-nav-item warehouse-list-active-li warehouse-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= $branch_label_nav; ?> List</a><?php endif; ?>
        <?php endif; ?>
        <?php if($CI->permissions('store_view') && store_module() && ($this->session->userdata('role_id') == 1 || is_store_admin())): ?>
          <a href="<?= base_url('store/add'); ?>" class="mp-nav-item store-add-active-li store-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> Add Store</a>
          <a href="<?= base_url('store/view'); ?>" class="mp-nav-item store-view-active-li store-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Store List</a>
        <?php endif; ?>
        <?php if($CI->permissions('users_view')): ?><a href="<?= base_url('users/view'); ?>" class="mp-nav-item users-view-active-li users-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Users List</a><?php endif; ?>
        <?php if($CI->permissions('roles_view')): ?><a href="<?= base_url('roles/view'); ?>" class="mp-nav-item roles-view-active-li roles-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Roles List</a><?php endif; ?>
        <?php if($CI->permissions('attendance_view') || $CI->permissions('attendance_edit') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
          <?php if($CI->permissions('attendance_edit') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
            <a href="<?= base_url('attendance/shifts'); ?>" class="mp-nav-item attendance-shifts-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Shifts</a>
            <a href="<?= base_url('attendance/assign_shifts'); ?>" class="mp-nav-item attendance-assign-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Assign Shifts</a>
          <?php endif; ?>
          <?php if($CI->permissions('attendance_view') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
            <a href="<?= base_url('attendance/daily'); ?>" class="mp-nav-item attendance-daily-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Daily Attendance</a>
            <a href="<?= base_url('attendance/report'); ?>" class="mp-nav-item attendance-report-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Attendance Report</a>
          <?php endif; ?>
        <?php endif; ?>
        <?php if($CI->permissions('send_sms')): ?><a href="<?= base_url('sms'); ?>" class="mp-nav-item sms-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Send SMS</a><?php endif; ?>
        <?php if($CI->permissions('sms_template_view')): ?><a href="<?= base_url('templates/sms'); ?>" class="mp-nav-item sms-templates-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> SMS Templates</a><?php endif; ?>
        <?php if($this->session->userdata('role_id') == 1 || is_store_admin()): ?>
          <a href="<?= base_url('site'); ?>" class="mp-nav-item site-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Site Settings</a>
          <a href="<?= base_url('migrate'); ?>" class="mp-nav-item migrate-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Data Migration</a>
          <a href="<?= base_url('subscription_license'); ?>" class="mp-nav-item subscription-license-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> License Management</a>
          <a href="<?= base_url('subscription_plans'); ?>" class="mp-nav-item subscription-plans-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Subscription Plans</a>
        <?php endif; ?>
        <?php if($CI->permissions('sms_api_view')): ?><a href="<?= base_url('sms/api'); ?>" class="mp-nav-item sms-api-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> SMS API</a><?php endif; ?>
        <?php if($CI->permissions('smtp_settings') && ($this->session->userdata('role_id') == 1 || is_store_admin())): ?><a href="<?= base_url('email_settings'); ?>" class="mp-nav-item email-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Email Settings</a><?php endif; ?>
        <?php if($CI->permissions('debt_reminder_view') || is_store_admin() || $this->session->userdata('role_id') == 1): ?><a href="<?= base_url('debt_reminder'); ?>" class="mp-nav-item debt-reminder-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Debt Reminder</a><?php endif; ?>
        <?php if($CI->permissions('gateway_view') && ($this->session->userdata('role_id') == 1 || is_store_admin()) && store_module()): ?><a href="<?= base_url('gateways'); ?>" class="mp-nav-item gateways-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Payment Gateways</a><?php endif; ?>
        <?php if($CI->permissions('package_view') && ($this->session->userdata('role_id') == 1 || is_store_admin()) && store_module()): ?><a href="<?= base_url('package'); ?>" class="mp-nav-item package-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Packages</a><?php endif; ?>
        <?php if($CI->permissions('subscription') && store_module()): ?><a href="<?= base_url('subscribers/list/'.get_current_store_id()); ?>" class="mp-nav-item subscription-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Subscription</a><?php endif; ?>
        <?php if($CI->permissions('tax_view')): ?><a href="<?= base_url('tax'); ?>" class="mp-nav-item tax-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Tax List</a><?php endif; ?>
        <?php if($CI->permissions('units_view')): ?><a href="<?= base_url('units/'); ?>" class="mp-nav-item units-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Units List</a><?php endif; ?>
        <?php if($CI->permissions('payment_types_view')): ?><a href="<?= base_url('payment_types/'); ?>" class="mp-nav-item payment_types-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Payment Types</a><?php endif; ?>
        <?php if($CI->permissions('payment_modes_view')): ?><a href="<?= base_url('payment_modes/'); ?>" class="mp-nav-item payment_modes-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Payment Modes</a><?php endif; ?>
        <?php if($CI->permissions('paystack_settings')): ?><a href="<?= base_url('paystack/settings'); ?>" class="mp-nav-item paystack-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Paystack Settings</a><?php endif; ?>
        <?php if($CI->permissions('expiry_settings') && mp_feature_enabled('expiry_tracking')): ?><a href="<?= base_url('expiry_settings'); ?>" class="mp-nav-item expiry-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Expiry Settings</a><?php endif; ?>
        <?php if($this->session->userdata('role_id') == 1 || is_store_admin()): ?>
          <a href="<?= base_url('currency/view'); ?>" class="mp-nav-item currency-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Currency List</a>
          <a href="<?= base_url('users/dbbackup'); ?>" class="mp-nav-item dbbackup-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Database Backup</a>
          <a href="<?= base_url('system_updates'); ?>" class="mp-nav-item system-updates-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> System Update</a>
          <a href="<?= base_url('manifest'); ?>" class="mp-nav-item manifest-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Manifest Generator</a>
          <a href="<?= base_url('release'); ?>" class="mp-nav-item release-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Build Release</a>
          <a href="<?= base_url('permission_audit'); ?>" class="mp-nav-item permission-audit-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Permission Audit</a>
          <a href="<?= base_url('country'); ?>" class="mp-nav-item country-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Countries</a>
          <a href="<?= base_url('state'); ?>" class="mp-nav-item state-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> States</a>
          <a href="<?= base_url('city'); ?>" class="mp-nav-item city-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Cities</a>
        <?php endif; ?>
        <?php if(($CI->permissions('approval_settings_edit') || is_store_admin() || $this->session->userdata('role_id') == 1) && mp_feature_enabled('manager_approvals')): ?><a href="<?= base_url('approvals/settings'); ?>" class="mp-nav-item approvals-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Security & Approvals</a><?php endif; ?>
        <?php if($CI->permissions('nin_usage')): ?><a href="<?= base_url('ninverify/usage'); ?>" class="mp-nav-item ninverify-usage-active-li"><i class="fa fa-bar-chart mp-nav-icon"></i> NIN Usage</a><?php endif; ?>
        <?php if($CI->permissions('nin_logs')): ?><a href="<?= base_url('ninverify/log'); ?>" class="mp-nav-item ninverify-log-active-li"><i class="fa fa-id-card mp-nav-icon"></i> NIN Verification Log</a><?php endif; ?>
        <a href="<?= base_url('users/password_reset'); ?>" class="mp-nav-item change-password-active-li"><i class="fa fa-lock mp-nav-icon"></i> Change Password</a>
      </div>
    </div></div>

    <div class="mp-nav-spacer"></div>
    <div class="mp-nav-store-card">
      <div class="store-name"><?= htmlspecialchars($this->session->userdata('store_name') ?? 'MartPoint'); ?></div>
      <div class="store-meta">MartPoint Retail v<?= isset($VERSION) ? $VERSION : app_version(); ?></div>
      <?php if($CI->db->table_exists('db_subscription_license')){ $CI->load->model('subscription_license_model','sub_lic'); $sub_status_nav = $CI->sub_lic->get_status(); if($sub_status_nav['status'] === 'ACTIVE'): ?>
      <div class="store-plan"><i class="fa fa-check"></i> <?= htmlspecialchars($sub_status_nav['plan'] ?? 'Pro Plan'); ?> · Active</div>
      <?php endif; } ?>
    </div>
  </nav>

  <!-- ===== MAIN ===== -->
  <main class="mp-main">
    <?php $this->load->view('comman/code_flashdata.php'); ?>
    <div style="clear:both"></div>
