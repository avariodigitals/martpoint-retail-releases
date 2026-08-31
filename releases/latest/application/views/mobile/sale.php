<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title ?? 'Sale'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?= $theme_link; ?>plugins/select2/select2.min.css">
  <style>
    :root {
      --mp-primary: #0057FF;
      --mp-primary-dark: #0044CC;
      --mp-bg: #F1F5F9;
      --mp-surface: #FFFFFF;
      --mp-text: #0F172A;
      --mp-muted: #64748B;
      --mp-border: #E2E8F0;
      --mp-success: #10B981;
      --mp-danger: #EF4444;
      --mp-warning: #F59E0B;
      --mp-ink: #1E293B;
      --safe-bottom: env(safe-area-inset-bottom, 0px);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; box-shadow: 0 0 40px rgba(0,0,0,0.08); }
    .screen { padding: 16px 16px 120px; min-height: 100vh; }
    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-top: 8px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar .sub { font-size: 13px; color: var(--mp-muted); margin-top: 2px; }
    .avatar { width: 38px; height: 38px; border-radius: 50%; background: #E0E7FF; color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }
    .form-group { margin-bottom: 16px; }
    .option-chips { display: flex; flex-wrap: nowrap; border: 1px solid var(--mp-border); border-radius: 14px; overflow: hidden; background: var(--mp-surface); }
    .option-chips button { flex: 1; min-width: 0; padding: 12px 6px; border: none; border-left: 1px solid var(--mp-border); border-radius: 0; background: var(--mp-surface); color: var(--mp-ink); font-size: 14px; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .option-chips button:first-child { border-left: none; border-top-left-radius: 14px; border-bottom-left-radius: 14px; }
    .option-chips button:last-child { border-top-right-radius: 14px; border-bottom-right-radius: 14px; }
    .option-chips button.active { background: var(--mp-primary); color: #fff; border-left-color: var(--mp-primary); }
    .option-chips button.active + button { border-left-color: var(--mp-primary); }
    .form-row { display: flex; gap: 10px; align-items: flex-end; }
    .form-row .form-group { flex: 1; min-width: 0; }
    .payment-row { border: 1px solid var(--mp-border); border-radius: 12px; padding: 12px; margin-bottom: 12px; background: #fff; }
    .plan-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    @media (max-width: 430px) { .plan-grid { grid-template-columns: 1fr; } .recent-grid { grid-template-columns: repeat(2, 1fr); } .screen { padding: 12px 12px 120px; } }
    .form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; }
    .form-control { width: 100%; padding: 16px; border: 1px solid var(--mp-border); border-radius: 14px; font-size: 16px; background: var(--mp-surface); outline: none; min-height: 54px; }
    .form-control:focus { border-color: var(--mp-primary); }
    select.form-control { appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%233b82f6' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 42px; }
    .custom-select { position: relative; }
    .custom-select select { position: absolute; opacity: 0; pointer-events: none; }
    .custom-select-trigger { display: flex; align-items: center; width: 100%; padding: 14px 42px 14px 16px; border: 1px solid var(--mp-border); border-radius: 14px; background: var(--mp-surface); font-size: 16px; min-height: 54px; cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%233b82f6' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; }
    .custom-select-options { display: none; position: absolute; left: 0; right: 0; top: calc(100% + 4px); max-height: 240px; overflow-y: auto; background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; z-index: 300; box-shadow: 0 10px 25px rgba(0,0,0,0.12); }
    .custom-select-options .optgroup-label { padding: 10px 16px; font-weight: 700; color: var(--mp-ink); background: var(--mp-bg); font-size: 13px; }
    .custom-select-options .opt { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid var(--mp-border); }
    .custom-select-options .opt:last-child { border-bottom: none; }
    .custom-select-options .opt:hover, .custom-select-options .opt.active { background: var(--mp-primary); color: #fff; }
    .custom-select-options .opt.selected { background: var(--mp-primary); color: #fff; font-weight: 600; }
    .customer-row { display: flex; gap: 8px; }
    .customer-row .form-control { flex: 1; }
    .customer-row button { width: 54px; border: 1px solid var(--mp-border); border-radius: 14px; background: var(--mp-surface); font-size: 20px; color: var(--mp-primary); }
    .search-bar { display: flex; align-items: center; background: var(--mp-bg); border-radius: 14px; padding: 8px 12px; border: 1px solid var(--mp-border); margin-bottom: 0; }
    .search-bar input { border: none; background: transparent; flex: 1; font-size: 16px; outline: none; min-height: 40px; }
    .search-bar input::placeholder { color: var(--mp-muted); }
    .cart-total { display: flex; justify-content: space-between; align-items: center; margin: 16px 0; padding: 16px; background: var(--mp-surface); border-radius: 16px; border: 1px solid var(--mp-border); }
    .cart-total .label { font-size: 16px; color: var(--mp-muted); }
    .cart-total .value { font-size: clamp(20px, 6vw, 26px); font-weight: 700; color: var(--mp-primary); word-break: keep-all; overflow-wrap: break-word; }
    .btn-primary { width: 100%; padding: 18px; border: none; border-radius: 14px; background: var(--mp-primary); color: white; font-size: 17px; font-weight: 600; cursor: pointer; }
    .btn-primary:active { background: var(--mp-primary-dark); }
    .btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
    .btn-secondary { width: 100%; padding: 18px; border: 1px solid var(--mp-border); border-radius: 14px; background: #fff; color: var(--mp-ink); font-size: 17px; font-weight: 600; cursor: pointer; }
    .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--mp-border); }
    .cart-item:last-child { border-bottom: none; }
    .cart-item .name { font-weight: 600; }
    .cart-item .meta { font-size: 13px; color: var(--mp-muted); }
    .cart-item .qty { display: flex; align-items: center; gap: 12px; background: var(--mp-bg); border-radius: 10px; padding: 4px; }
    .cart-item .qty button { width: 32px; height: 32px; border: none; border-radius: 8px; background: var(--mp-surface); font-size: 18px; font-weight: 600; color: var(--mp-primary); }
    .cart-item .qty span { min-width: 24px; text-align: center; font-weight: 600; }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: rgba(255,255,255,0.96); backdrop-filter: blur(10px); border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 10px 0 calc(10px + var(--safe-bottom)); z-index: 100; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 10px; border: none; background: transparent; color: var(--mp-muted); font-size: 11px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 22px; }
    .nav-item.active { color: var(--mp-primary); }
    .empty-cart { text-align: center; padding: 24px; color: var(--mp-muted); }
    .result-list { border: 1px solid var(--mp-border); border-radius: 14px; background: #fff; margin-top: 8px; overflow: hidden; }
    .result-item { padding: 14px 16px; border-bottom: 1px solid var(--mp-border); cursor: pointer; }
    .result-item:last-child { border-bottom: none; }
    .result-item .title { font-weight: 600; }
    .result-item .meta { font-size: 13px; color: var(--mp-muted); }
    .chip-list { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 4px; }
    .chip { flex: 0 0 auto; padding: 10px 16px; border-radius: 20px; border: 1px solid var(--mp-border); background: #fff; font-size: 13px; font-weight: 500; cursor: pointer; white-space: nowrap; }
    .chip:active { background: var(--mp-bg); }
    .recent-dropdown { border: 1px solid var(--mp-border); border-radius: 16px; background: #fff; padding: 16px; overflow-x: hidden; }
    .recent-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; max-height: 340px; overflow-y: auto; overflow-x: hidden; }
    .recent-item { display: flex; flex-direction: column; align-items: center; padding: 16px; border: 1px solid var(--mp-border); border-radius: 14px; background: #fff; cursor: pointer; text-align: center; min-width: 0; overflow: hidden; }
    .recent-item:active { background: var(--mp-bg); }
    .recent-item img { width: 56px; height: 56px; object-fit: cover; border-radius: 12px; margin-bottom: 10px; background: #f1f5f9; }
    .recent-item .name { font-size: 13px; font-weight: 600; line-height: 1.35; height: 2.7em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; word-break: break-word; }
    .recent-item .price { font-size: 14px; font-weight: 700; color: var(--mp-primary); margin-top: 6px; word-break: break-word; }
    .recent-item .price-alt { font-size: 11px; color: var(--mp-muted); margin-top: 2px; word-break: break-word; }
    .catalog-count { font-size: 12px; color: var(--mp-muted); padding: 0 2px 8px; }
    .catalog-header { display: flex; gap: 10px; position: sticky; top: 0; background: #fff; z-index: 5; padding-bottom: 12px; margin-bottom: 0; }
    .catalog-header .form-control { flex: 1; }
    .catalog-header .custom-select { flex: 1; min-width: 0; }
    .select2-container { width: 100% !important; }
    .select2-container--default .select2-selection--single { height: 54px; border: 1px solid var(--mp-border); border-radius: 14px; font-size: 16px; padding: 12px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 28px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 52px; }
    .due-text { font-size: 13px; color: var(--mp-danger); margin-top: 6px; }
    .modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 500; align-items: center; justify-content: center; padding: 16px; }
    .modal.active { display: flex; }
    .modal-box { background: #fff; border-radius: 20px; padding: 24px; width: 92%; max-width: 620px; text-align: center; max-height: 90vh; overflow-y: auto; position: relative; }
    .modal-box h3 { margin: 0 0 16px; }
    .modal-close { position: absolute; top: 14px; right: 18px; width: 32px; height: 32px; border: none; background: transparent; color: var(--mp-muted); font-size: 26px; line-height: 30px; border-radius: 8px; cursor: pointer; }
    .modal-box .btn { display: block; width: 100%; margin-bottom: 12px; padding: 14px; border-radius: 12px; border: none; font-size: 16px; font-weight: 600; cursor: pointer; }
    .modal-box .btn-primary { background: var(--mp-primary); color: #fff; }
    .modal-box .btn-secondary { background: var(--mp-bg); color: var(--mp-ink); }
    .modal-box .form-row .btn { width: auto; flex: 0 0 auto; margin-bottom: 0; white-space: nowrap; }
    #payment_modal .modal-box { display: flex; flex-direction: column; padding: 0; overflow: hidden; }
    #payment_modal .modal-body { flex: 1; overflow-y: auto; padding: 0 24px 16px; text-align: left; }
    #payment_modal .modal-box h3 { padding: 20px 24px 10px; margin: 0; }
    #payment_modal .modal-actions { padding: 12px 24px; border-top: 1px solid var(--mp-border); background: #fff; }
    #payment_modal .modal-close { top: 18px; right: 20px; }
    @media (max-width: 430px) {
      .modal-box { padding: 16px; width: 100%; border-radius: 16px; max-height: 95vh; }
      .modal-box h3 { font-size: 18px; }
      .modal-box .cart-total { padding: 10px; margin: 8px 0; }
      .modal-box .form-group { margin-bottom: 12px; }
      .modal-box .form-group label { font-size: 13px; }
      .modal-box .form-control { padding: 12px 14px; min-height: 46px; font-size: 15px; }
      .modal-box select.form-control { padding-right: 36px; }
      .modal-box .form-row { gap: 8px; }
      .modal-box .option-chips { flex-wrap: wrap; overflow: visible; }
      .modal-box .option-chips button { flex: 1 1 auto; border: 1px solid var(--mp-border); border-radius: 10px; margin: 2px; min-width: 44px; font-size: 13px; white-space: nowrap; }
      .modal-box .btn { padding: 12px; font-size: 15px; }
      #payment_modal .modal-box { border-radius: 16px; }
      #payment_modal .modal-box h3 { padding: 16px 16px 8px; font-size: 18px; }
      #payment_modal .modal-body { padding: 0 16px 12px; }
      #payment_modal .modal-actions { padding: 10px 16px; }
    }
    #toast {
      position: fixed;
      top: 16px;
      left: 50%;
      transform: translateX(-50%) translateY(-120%);
      max-width: 360px;
      width: calc(100% - 32px);
      padding: 14px 18px;
      border-radius: 14px;
      background: #0F172A;
      color: #fff;
      font-size: 14px;
      font-weight: 500;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      z-index: 1000;
      opacity: 0;
      transition: all 0.3s ease;
      pointer-events: none;
    }
    #toast.active { transform: translateX(-50%) translateY(0); opacity: 1; }
    #toast.error { background: var(--mp-danger); }
    #toast.success { background: var(--mp-success); }
    #toast.warning { background: var(--mp-warning); color: #1a1a1a; }
    @media (min-width: 600px) {
      #app { max-width: 100%; margin: 0; box-shadow: none; }
      .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; }
      .screen { padding: 24px 24px 130px; }
    }
    @media (min-width: 1024px) {
      .screen { padding: 32px 48px 150px; }
      .topbar h1 { font-size: 26px; }
      .cart-item, .result-item { padding: 16px 0; }
    }
    @media (orientation: landscape) and (min-width: 600px) {
      #app { max-width: 100%; margin: 0; box-shadow: none; }
      .topbar { position: fixed; top: 0; left: 0; width: 100%; height: 60px; padding: 0 16px; margin: 0; background: var(--mp-surface); border-bottom: 1px solid var(--mp-border); z-index: 20; }
      .screen { width: 50%; padding: 76px 16px 120px; }
      .bottom-nav { max-width: 50%; left: 0; right: auto; transform: none; }
      #catalog_toggle { display: none; }
      #catalog_dropdown { display: flex !important; flex-direction: column; position: fixed; top: 60px; right: 0; width: 50%; height: calc(100vh - 60px); padding: 16px; margin: 0; border-radius: 0; border: none; border-left: 1px solid var(--mp-border); background: #fff; z-index: 10; overflow-y: auto; }
      #catalog_close { display: none; }
      .recent-grid { grid-template-columns: repeat(3, 1fr); grid-auto-rows: minmax(180px, auto); gap: 18px; max-height: none; width: 100%; }
      .recent-item { height: 100%; justify-content: flex-start; }
      .recent-item .price { margin-top: auto; padding-top: 6px; }
      .recent-item img { width: 64px; height: 64px; }
    }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <div class="topbar-titles">
            <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
            <h1><?= $page_title ?? 'Quick Sale'; ?></h1>
          <div class="sub"><?= $init_code . ' ' . $count_id; ?></div>
        </div>
        <div class="avatar" style="overflow:hidden;">
          <?php
            $avatar_src = '';
            $profile_picture = $this->db->select('profile_picture')->where('id', $this->session->userdata('inv_userid'))->get('db_users')->row()->profile_picture;
            if(!empty($profile_picture) && file_exists(FCPATH . $profile_picture)){
              $avatar_src = $profile_picture;
            }
          ?>
          <?php if(!empty($avatar_src)): ?>
            <img src="<?= base_url($avatar_src); ?>" style="width:100%;height:100%;object-fit:cover;" alt="Profile">
          <?php else: ?>
            <?= strtoupper(substr($display_name,0,1)); ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="form-group">
        <label>Customer</label>
        <div class="customer-row">
          <select class="form-control" id="customer_id" style="flex:1">
            <option value="">Select customer</option>
            <option value="<?= get_walk_in_customer_id(); ?>" selected>Walk-in Customer</option>
          </select>
          <button type="button" onclick="showToast('Add customer flow here', 'warning')">+</button>
        </div>
        <div class="due-text" id="customer_due" style="display:none;">Previous due: <strong>₦ 0.00</strong></div>
      </div>

      <div class="form-group">
        <label>Price Type</label>
        <input type="hidden" id="price_type" value="retail">
        <div class="option-chips" id="price_type_chips">
          <button type="button" data-value="retail" class="active">Retail (MRP)</button>
          <button type="button" data-value="wholesale">Wholesale</button>
        </div>
      </div>

      <div class="form-group">
        <label>Barcode / IMEI</label>
        <div class="search-bar">
          <input type="text" class="form-control" id="barcode_input" placeholder="Scan or type barcode and press Enter" style="border:none;background:transparent;flex:1;min-height:38px;padding:0;" autocomplete="off">
          <button type="button" id="barcode_btn" style="background:none;border:none;color:var(--mp-primary);font-size:20px;">➕</button>
        </div>
      </div>

      <div class="form-group">
        <label>Search Item</label>
        <div class="search-bar">
          <input type="text" class="form-control" id="item_search" placeholder="Type item name, code or SKU" style="border:none;background:transparent;flex:1;min-height:38px;padding:0;">
          <button type="button" id="item_search_btn" style="background:none;border:none;color:var(--mp-primary);font-size:20px;">🔍</button>
        </div>
        <div id="item_results" class="result-list" style="display:none;"></div>
      </div>

      <div class="form-group" id="cart-section">
        <label>Cart</label>
        <div id="cart" style="border:1px solid var(--mp-border); border-radius:16px; padding:0 16px; background:#fff;">
          <div class="empty-cart">No items yet. Search to add.</div>
        </div>
      </div>

      <div class="form-group">
        <button type="button" class="btn-secondary" id="catalog_toggle" style="width:100%; display:flex; align-items:center; justify-content:space-between; padding:12px 16px; margin-bottom:8px;">
          <span><i class="fa fa-th-large"></i> Product Catalog</span>
          <i class="fa fa-chevron-down" id="catalog_chevron"></i>
        </button>
        <div id="catalog_dropdown" class="recent-dropdown" style="display:none;">
          <div class="catalog-header" style="margin-bottom:12px;">
            <select class="form-control" id="catalog_category">
              <option value="">All Cat</option>
            </select>
            <select class="form-control" id="catalog_brand">
              <option value="">All Brands</option>
            </select>
          </div>
          <div class="catalog-count" id="catalog_count">Loading products…</div>
          <div id="catalog_items" class="recent-grid">
            <span class="empty-cart" style="padding:12px;">Loading...</span>
          </div>
          <button type="button" class="btn-secondary" id="catalog_close" style="width:100%; margin-top:8px;">Close</button>
        </div>
      </div>

      <div class="form-group">
        <label>Discount (₦)</label>
        <input type="number" class="form-control" id="discount" value="0" min="0" step="0.01">
      </div>

      <div class="form-group">
        <label>Note</label>
        <textarea class="form-control" id="sales_note" rows="2" style="min-height:80px;" placeholder="Optional note..."></textarea>
      </div>

      <div class="cart-total">
        <div class="label">Total Amount</div>
        <div class="value" id="grand_total">₦ 0.00</div>
      </div>

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:16px;">
        <button class="btn-secondary" id="hold_sale" type="button">Hold</button>
        <button class="btn-secondary" id="split_pay" type="button">Split Pay</button>
        <button class="btn-secondary" id="pay_plan" type="button">PayPlan</button>
        <button class="btn-primary" id="pay_sale" type="button" style="background:var(--mp-success);">Pay</button>
      </div>

    </section>


  </div>

  <div id="toast"></div>

  <div class="modal" id="payment_modal">
    <div class="modal-box">
      <button class="modal-close" type="button" aria-label="Close">&times;</button>
      <h3 id="payment_title" style="text-align:center;">Payment</h3>
      <div class="modal-body">
        <div class="cart-total" style="margin:12px 0; padding:12px;">
        <div class="label">Total</div>
        <div class="value" id="payment_total" style="color:var(--mp-primary);">₦ 0.00</div>
      </div>
      <input type="hidden" id="redeem_discount" value="0">
      <input type="hidden" id="redeem_points" value="0">
      <input type="hidden" id="redeem_store_credit_val" value="0">
      <input type="hidden" id="redeem_gift_card_id" value="">
      <div id="payment_extras">
        <div class="form-row" style="margin-bottom:8px;">
          <div class="form-group" style="flex:1; margin-bottom:0;">
            <label id="customer_advance_label">Advance: ₦ 0.00</label>
            <label style="display:flex; align-items:center; gap:8px; font-weight:500; cursor:pointer; margin-top:4px;">
              <input type="checkbox" id="allow_tot_advance" value="checked" style="width:18px; height:18px;"> Use advance payment
            </label>
          </div>
          <div class="form-group" style="flex:1; margin-bottom:0;">
            <label>Discount Coupon Code</label>
            <input type="text" class="form-control" id="coupon_code" placeholder="Enter coupon code">
          </div>
        </div>
      </div>
      <div id="payment_loyalty" style="display:none; border:1px solid var(--mp-border); border-radius:14px; padding:12px; margin-bottom:16px; background:#fff;">
        <div style="font-weight:700; margin-bottom:10px;">Loyalty & Rewards</div>
        <div class="form-row" style="margin-bottom:8px; align-items:flex-start;">
          <div class="form-group" style="flex:1; margin-bottom:0;">
            <label>Redeem Points (<span id="avail_points">0</span>)</label>
            <input type="number" class="form-control" id="points_redeem_amount" min="0" step="1" placeholder="Points" style="min-height:46px;">
          </div>
          <button type="button" class="btn btn-secondary" id="points_apply" style="margin-bottom:0; background:var(--mp-success); color:#fff; white-space:nowrap;">Apply</button>
        </div>
        <div class="form-row" style="margin-bottom:8px; align-items:flex-start;">
          <div class="form-group" style="flex:1; margin-bottom:0;">
            <label>Store Credit (<span id="avail_store_credit">₦ 0.00</span>)</label>
            <input type="number" class="form-control" id="store_credit_redeem_amount" min="0" step="0.01" placeholder="Amount" style="min-height:46px;">
          </div>
          <button type="button" class="btn btn-secondary" id="store_credit_apply" style="margin-bottom:0; background:#F59E0B; color:#fff; white-space:nowrap;">Apply</button>
        </div>
        <div class="form-row" style="align-items:flex-start;">
          <div class="form-group" style="flex:1; margin-bottom:0;">
            <label>Gift Card</label>
            <input type="text" class="form-control" id="gift_card_number" placeholder="Card #" style="min-height:46px;">
            <input type="number" class="form-control" id="gift_card_redeem_amount" min="0" step="0.01" placeholder="Amount" style="min-height:46px; margin-top:8px;">
          </div>
          <button type="button" class="btn btn-secondary" id="gift_card_apply" style="margin-bottom:0; background:#8B5CF6; color:#fff; white-space:nowrap;">Apply</button>
        </div>
      </div>
      <div class="form-group" id="payment_mode_group">
        <label>Payment Mode</label>
        <select class="form-control" id="payment_mode_select">
          <?php foreach($payment_modes as $pm): ?>
            <option value="<?= $pm->code; ?>" <?= !empty($pm->is_default) ? 'selected' : ''; ?>><?= $pm->name; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" id="payment_amount_group">
        <label>Amount Paid</label>
        <input type="number" class="form-control" id="payment_amount_paid" value="" min="0" step="0.01" placeholder="Enter amount received">
      </div>
      <div class="form-group" id="payment_account_group">
        <label>Account to Credit</label>
        <select class="form-control" id="account_id_select">
          <?= get_accounts_select_list($till_account_id); ?>
        </select>
      </div>
      <div id="payment_rows" style="display:none;"></div>
      <div class="form-group" id="payment_split_summary" style="display:none;">
        <div class="cart-total" style="background:#EFF6FF; border-color:var(--mp-primary); margin:0;">
          <div class="label">Balance</div>
          <div class="value" id="payment_split_balance" style="color:var(--mp-primary);">₦ 0.00</div>
        </div>
      </div>
      <template id="payment_row_template">
        <div class="payment-row" data-index="__IDX__">
          <div class="form-row">
            <div class="form-group" style="flex:1;">
              <label>Amount</label>
              <input type="number" class="form-control payment-row-amount" min="0" step="0.01" value="" placeholder="0.00">
            </div>
            <div class="form-group" style="flex:1;">
              <label>Payment Type</label>
              <select class="form-control payment-row-mode">
                <?php foreach($payment_modes as $pm): ?>
                  <option value="<?= $pm->code; ?>" <?= !empty($pm->is_default) ? 'selected' : ''; ?>><?= $pm->name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Account</label>
            <select class="form-control payment-row-account">
              <?= get_accounts_select_list($till_account_id); ?>
            </select>
          </div>
          <button type="button" class="btn btn-danger payment-row-remove" style="background:#EF4444; color:#fff; margin-bottom:12px;">Remove</button>
        </div>
      </template>
      <button class="btn btn-primary" id="payment_add_row" type="button" style="display:none; margin-bottom:12px; background:var(--mp-primary);">Add Payment Row</button>
      <div class="cart-total" id="payment_change_row" style="display:none; background:#ECFDF5; border-color:#10B981; margin:12px 0;">
        <div class="label" style="color:#065F46;">Change</div>
        <div class="value" id="payment_change_amount" style="color:var(--mp-success);">₦ 0.00</div>
      </div>
      <div id="payment_plan_fields" style="display:none;">
        <div class="plan-grid">
          <div class="form-group">
            <label>Down Payment (%)</label>
            <input type="number" class="form-control" id="bnpl_down_pct" value="30" min="0" max="100" step="1">
          </div>
          <div class="form-group">
            <label>Installments</label>
            <input type="hidden" id="bnpl_count" value="3">
            <div class="option-chips" id="bnpl_count_chips">
              <button type="button" data-value="2">2</button>
              <button type="button" data-value="3" class="active">3</button>
              <button type="button" data-value="4">4</button>
              <button type="button" data-value="6">6</button>
              <button type="button" data-value="8">8</button>
              <button type="button" data-value="12">12</button>
            </div>
          </div>
          <div class="form-group">
            <label>Frequency</label>
            <input type="hidden" id="bnpl_frequency" value="biweekly">
            <div class="option-chips" id="bnpl_frequency_chips">
              <button type="button" data-value="weekly">Weekly</button>
              <button type="button" data-value="biweekly" class="active">Bi-Weekly</button>
              <button type="button" data-value="monthly">Monthly</button>
            </div>
          </div>
        </div>
        <div class="plan-grid">
          <div class="form-group">
            <label>Down Payment Amount</label>
            <input type="number" class="form-control" id="bnpl_down_amt" value="0.00" readonly>
          </div>
          <div class="form-group">
            <label>Each Installment</label>
            <input type="number" class="form-control" id="bnpl_each_amt" value="0.00" readonly>
          </div>
          <div class="form-group">
            <label>First Due Date</label>
            <input type="date" class="form-control" id="bnpl_first_due" value="">
          </div>
        </div>
        <div class="form-group">
          <label>Late Fee / Day</label>
          <input type="number" class="form-control" id="bnpl_late_fee" value="0" min="0" step="0.01">
        </div>
      </div>
      </div>
      <div class="modal-actions">
        <button class="btn btn-primary" id="payment_confirm" type="button" style="margin-bottom:12px;">Confirm</button>
        <button class="btn btn-secondary" id="payment_cancel" type="button" style="margin-bottom:0;">Cancel</button>
      </div>
    </div>
  </div>

  <div class="modal" id="pay_modal">
    <div class="modal-box">
      <button class="modal-close" type="button" aria-label="Close">&times;</button>
      <h3>Payment Successful</h3>
      <p id="pay_message">Sale saved.</p>
      <button class="btn btn-primary" id="btn_print" type="button">Print Receipt</button>
      <button class="btn btn-secondary" id="btn_share" type="button">Share Receipt</button>
      <button class="btn btn-secondary" id="btn_done" type="button" style="margin-bottom:0;">Done</button>
    </div>
  </div>

  <script src="<?= $theme_link; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
  <script src="<?= $theme_link; ?>plugins/select2/select2.min.js"></script>
  <script>
    var base_url = '<?= base_url(); ?>';
    var cart = [];
    var holdData = <?= json_encode($hold ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?>;
    var searchItemsMap = {};
    var catalogItemsMap = {};
    var lastSalesId = 0;
    var lastWhatsAppUrl = '';
    var csrf_name = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';
    var currency = '<?= $this->session->userdata("currency"); ?>';

    function formatMoney(num){
      return currency + ' ' + Number(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    function showToast(message, type){
      type = type || 'error';
      var $toast = $('#toast');
      $toast.removeClass('error success warning').addClass(type).text(message).addClass('active');
      setTimeout(function(){ $toast.removeClass('active'); }, 3000);
    }

    function loadHold(hold){
      console.log('[loadHold] hold', hold);
      if(!hold || !hold.items){ showToast('No held sale data found', 'error'); return; }
      if(hold.items.length === 0){ showToast('Hold has no items', 'warning'); return; }
      showToast('Recalling ' + hold.items.length + ' items', 'success');
      $('#discount').val(hold.discount || 0);
      $('#sales_note').val(hold.sales_note || '');
      cart = hold.items.map(function(it){
        return {
          id: it.id,
          name: it.name || 'Unnamed item',
          price: parseFloat(it.price) || 0,
          purchase_price: parseFloat(it.purchase_price) || 0,
          tax_value: parseFloat(it.tax_value) || 0,
          tax_id: it.tax_id || 0,
          tax_name: it.tax_name || '',
          tax_type: it.tax_type || 'Exclusive',
          qty: parseFloat(it.qty) || 1
        };
      });
      if(hold.customer_id){
        var option = new Option(hold.customer_name || 'Customer', hold.customer_id, true, true);
        $('#customer_id').append(option).trigger('change');
        loadCustomerDue(hold.customer_id);
      }
      $('#price_type').val(hold.price_type || 'wholesale');
      $('#price_type_chips button').removeClass('active').filter('[data-value="'+hold.price_type+'"]').addClass('active');
      $('#price_type').trigger('change');
      renderCart();
    }

    function addPaymentRow(amount){
      var idx = $('.payment-row').length + 1;
      var tpl = $('#payment_row_template').html().replace(/__IDX__/g, idx);
      var $row = $(tpl);
      $row.find('.payment-row-amount').val(amount ? amount.toFixed(2) : '');
      $row.find('.payment-row-remove').on('click', function(){ $(this).closest('.payment-row').remove(); updateSplitTotals(); updateRemoveButtons(); });
      $row.find('.payment-row-amount').on('input', updateSplitTotals);
      $row.find('select').each(function(){ initCustomSelect($(this)); });
      $('#payment_rows').append($row);
      updateSplitTotals();
      updateRemoveButtons();
    }

    function updateRemoveButtons(){
      $('.payment-row').each(function(i){
        $(this).find('.payment-row-remove').toggle(i > 0);
      });
    }

    function updateSplitTotals(){
      var total = getCartTotal();
      var paid = 0;
      $('.payment-row-amount').each(function(){ paid += (parseFloat($(this).val()) || 0); });
      var balance = Math.max(0, total - paid);
      $('#payment_split_balance').text(formatMoney(balance));
    }

    function getPaymentRows(){
      var rows = [];
      $('.payment-row').each(function(){
        var amt = parseFloat($(this).find('.payment-row-amount').val()) || 0;
        if(amt > 0){
          rows.push({
            amount: amt,
            payment_type: $(this).find('.payment-row-mode').val(),
            account_id: $(this).find('.payment-row-account').val()
          });
        }
      });
      return rows;
    }

    function getRedeemDiscount(){
      var total = parseFloat($('#redeem_discount').val()) || 0;
      return total;
    }

    function setRedeemDiscount(extra){
      $('#redeem_discount').val(extra);
      $('#payment_total').text(formatMoney(getCartTotal()));
      updatePaymentChange();
      updateSplitTotals();
    }

    $(document).on('click', '#payment_add_row', function(){
      addPaymentRow(0);
    });

    $(document).on('click', '#points_apply', function(){
      var customer_id = $('#customer_id').val();
      var points = parseInt($('#points_redeem_amount').val()) || 0;
      if(points <= 0){ showToast('Enter points to redeem', 'warning'); return; }
      if(points > parseInt($('#avail_points').text() || 0)){ showToast('Not enough points', 'warning'); return; }
      $.post(base_url + 'loyalty/get_settings_json', {}, function(settings){
        if(settings && settings.redemption_rate){
          var discount = (points / 100) * parseFloat(settings.redemption_rate);
          $('#redeem_points').val(points);
          setRedeemDiscount(parseFloat($('#redeem_discount').val()||0) + discount);
          showToast('Points applied: ' + formatMoney(discount) + ' off', 'success');
        } else {
          showToast('Loyalty settings not available', 'error');
        }
      }, 'json');
    });

    $(document).on('click', '#store_credit_apply', function(){
      var customer_id = $('#customer_id').val();
      var amount = parseFloat($('#store_credit_redeem_amount').val()) || 0;
      if(amount <= 0){ showToast('Enter store credit amount', 'warning'); return; }
      var balance = parseFloat(window.customerExtras.store_credit_balance) || 0;
      if(amount > balance){ showToast('Store credit exceeds balance', 'warning'); return; }
      $('#redeem_store_credit_val').val(amount);
      setRedeemDiscount(parseFloat($('#redeem_discount').val()||0) + amount);
      showToast('Store credit applied: ' + formatMoney(amount) + ' off', 'success');
    });

    $(document).on('click', '#gift_card_apply', function(){
      var customer_id = $('#customer_id').val();
      var amount = parseFloat($('#gift_card_redeem_amount').val()) || 0;
      var cardNumber = $('#gift_card_number').val().trim();
      if(!cardNumber || amount <= 0){ showToast('Enter gift card and amount', 'warning'); return; }
      $.post(base_url + 'gift_cards/validate_card_ajax', { card_number: cardNumber }, function(card){
        if(card.valid && card.balance >= amount){
          $('#redeem_gift_card_id').val(card.card_id);
          setRedeemDiscount(parseFloat($('#redeem_discount').val()||0) + amount);
          showToast('Gift card applied: ' + formatMoney(amount) + ' off', 'success');
        } else {
          showToast('Invalid card or insufficient balance', 'error');
        }
      }, 'json');
    });

    function getActivePriceType(){
      return $('#price_type_chips button.active').data('value') || $('#price_type').val() || 'wholesale';
    }

    function selectedPrice(item){
      var retail = parseFloat(item.mrp_price) || 0;
      var wholesale = parseFloat(item.sales_price) || 0;
      if(getActivePriceType() === 'retail'){
        // Fall back to wholesale when no MRP is set, so a real price is always used.
        return retail > 0 ? retail : wholesale;
      }
      return wholesale;
    }

    function getCartTotal(){
      var subtotal = 0;
      cart.forEach(function(item){
        var line = item.qty * item.price;
        if(item.tax_type === 'Exclusive'){
          line += (line * item.tax_value / 100);
        }
        subtotal += line;
      });
      var discount = parseFloat($('#discount').val()) || 0;
      var redeem = parseFloat($('#redeem_discount').val()) || 0;
      return Math.max(0, subtotal - discount - redeem);
    }

    function updatePaymentChange(){
      var total = getCartTotal();
      var paid = parseFloat($('#payment_amount_paid').val()) || 0;
      if(paid > total){
        $('#payment_change_amount').text(formatMoney(paid - total));
        $('#payment_change_row').show();
      } else {
        $('#payment_change_row').hide();
      }
    }

    function calculatePlan(){
      var total = getCartTotal();
      var downPct = parseFloat($('#bnpl_down_pct').val()) || 0;
      var count = parseInt($('#bnpl_count').val()) || 0;
      var downAmt = Math.round((total * downPct / 100) * 100) / 100;
      var remain = Math.round((total - downAmt) * 100) / 100;
      var eachAmt = count > 0 ? (Math.round((remain / count) * 100) / 100) : 0;
      $('#bnpl_down_amt').val(downAmt.toFixed(2));
      $('#bnpl_each_amt').val(eachAmt.toFixed(2));
    }

    $(document).on('input', '#bnpl_down_pct', function(){
      calculatePlan();
    });

    $(document).on('click', '.option-chips button', function(){
      $(this).siblings().removeClass('active');
      $(this).addClass('active');
      var $chips = $(this).closest('.option-chips');
      if($chips.attr('id') === 'bnpl_count_chips'){
        $('#bnpl_count').val($(this).data('value'));
      } else if($chips.attr('id') === 'bnpl_frequency_chips'){
        $('#bnpl_frequency').val($(this).data('value'));
      } else if($chips.attr('id') === 'price_type_chips'){
        $('#price_type').val($(this).data('value')).trigger('change');
      }
      calculatePlan();
    });

    function renderCart(){
      var html = '';
      if(cart.length === 0){
        $('#cart').html('<div class="empty-cart">No items yet. Search to add.</div>');
      } else {
        cart.forEach(function(item, index){
          html += '<div class="cart-item" data-index="'+index+'">' +
            '<div>' +
              '<div class="name">'+(item.name || 'Unnamed item')+'</div>' +
              '<div class="meta">'+formatMoney(item.price)+' · '+item.tax_type+'</div>' +
            '</div>' +
            '<div class="qty">' +
              '<button class="qty-minus">-</button>' +
              '<span>'+item.qty+'</span>' +
              '<button class="qty-plus">+</button>' +
            '</div>' +
          '</div>';
        });
        $('#cart').html(html);
      }
      var total = getCartTotal();
      $('#grand_total').text(formatMoney(total));
      if($('#payment_modal').hasClass('active')){
        $('#payment_total').text(formatMoney(total));
        updatePaymentChange();
      }
    }

    function addItem(item){
      if(!item || !item.id) return;
      var price = selectedPrice(item);
      if(price <= 0){
        showToast('No ' + ($('#price_type').val() === 'retail' ? 'retail (MRP)' : 'wholesale') + ' price for this item.', 'error');
        return;
      }
      var found = cart.find(function(c){ return c.id == item.id; });
      if(found){
        found.qty += 1;
      } else {
        cart.push({
          id: item.id,
          name: item.item_name,
          price: price,
          purchase_price: parseFloat(item.purchase_price) || 0,
          tax_value: parseFloat(item.tax_value) || 0,
          tax_id: item.tax_id || 0,
          tax_name: item.tax_name || '',
          tax_type: item.tax_type || 'Exclusive',
          qty: 1
        });
      }
      $('#item_results').hide();
      $('#item_search').val('');
      $('#barcode_input').val('');
      renderCart();
      showToast(item.item_name + ' added', 'success');
    }

    function showResults(items){
      searchItemsMap = {};
      if(items.length === 0){
        $('#item_results').hide();
        return;
      }
      var html = '';
      items.forEach(function(item){
        searchItemsMap[item.id] = item;
        html += '<div class="result-item" data-id="'+item.id+'">' +
          '<div class="title">'+item.item_name+'</div>' +
          '<div class="meta">'+formatMoney(selectedPrice(item))+' · Stock: '+(item.stock || 0)+'</div>' +
        '</div>';
      });
      $('#item_results').html(html).show();
    }

    function searchItems(q){
      if(!q || q.length < 2) return;
      $.get(base_url + 'mobile/item_search', { q: q, price_type: getActivePriceType(), _: new Date().getTime() }, function(data){
        if(typeof data === 'string') data = JSON.parse(data);
        showResults(data || []);
      }, 'json');
    }

    function addByBarcode(q){
      if(!q) return;
      $.get(base_url + 'mobile/item_search', { q: q, price_type: getActivePriceType(), _: new Date().getTime() }, function(data){
        if(typeof data === 'string') data = JSON.parse(data);
        var items = data || [];
        if(items.length === 1){
          addItem(items[0]);
        } else if(items.length > 1){
          showResults(items);
        } else {
          showToast('Item not found: ' + q, 'warning');
        }
      });
    }

    function getThumb(url){
      return url || 'theme/images/no_image.png';
    }

    function loadCategories(){
      $.get(base_url + 'mobile/categories', { _: new Date().getTime() }, function(data){
        if(typeof data === 'string') data = JSON.parse(data);
        var categories = data || [];
        var html = '<option value="">All Cat</option>';
        categories.forEach(function(cat){
          html += '<option value="'+cat.id+'">'+cat.category_name+'</option>';
        });
        $('#catalog_category').html(html);
        initCustomSelect($('#catalog_category'), function(){ loadCatalog(); });
      }).fail(function(){
        $('#catalog_category').html('<option value="">All Cat</option>');
        initCustomSelect($('#catalog_category'), function(){ loadCatalog(); });
      });
    }

    function loadBrands(){
      $.get(base_url + 'mobile/brands', { _: new Date().getTime() }, function(data){
        if(typeof data === 'string') data = JSON.parse(data);
        var brands = data || [];
        var html = '<option value="">All Brands</option>';
        brands.forEach(function(brand){
          html += '<option value="'+brand.id+'">'+brand.brand_name+'</option>';
        });
        $('#catalog_brand').html(html);
        initCustomSelect($('#catalog_brand'), function(){ loadCatalog(); });
      }).fail(function(){
        $('#catalog_brand').html('<option value="">All Brands</option>');
        initCustomSelect($('#catalog_brand'), function(){ loadCatalog(); });
      });
    }

    function loadCatalog(){
      var category = $('#catalog_category').val();
      var brand = $('#catalog_brand').val();
      var activeType = getActivePriceType();
      $.get(base_url + 'mobile/item_search', { q: '', category: category, brand: brand, price_type: activeType, _: new Date().getTime(), limit: 100 }, function(data){
        if(typeof data === 'string') data = JSON.parse(data);
        var items = data || [];
        $('#catalog_count').text(items.length + ' product' + (items.length === 1 ? '' : 's'));
        if(items.length === 0){
          $('#catalog_items').html('<span class="empty-cart" style="padding:12px;">No products match this filter</span>');
          return;
        }
        catalogItemsMap = {};
        var activeLabel = activeType === 'retail' ? 'Retail' : 'Wholesale';
        var altLabel = activeType === 'retail' ? 'Wholesale' : 'Retail';
        var html = '';
        items.forEach(function(item){
          catalogItemsMap[String(item.id)] = item;
          var retail = parseFloat(item.mrp_price) || 0;
          var wholesale = parseFloat(item.sales_price) || 0;
          // Fallback: if retail (MRP) is not set, use wholesale so a real price always shows.
          if(retail <= 0) retail = wholesale;
          var active = activeType === 'retail' ? retail : wholesale;
          var altPrice = activeType === 'retail' ? wholesale : retail;
          var thumb = base_url + getThumb(item.item_image);
          html += '<div class="recent-item" data-id="'+item.id+'">' +
                    '<img src="'+thumb+'" alt="" onerror="this.src=\'' + base_url + 'theme/images/no_image.png\'">' +
                    '<div class="name">'+item.item_name+'</div>' +
                    '<div class="price">'+formatMoney(active)+'</div>' +
                    '<div class="price-alt">'+altLabel+': '+formatMoney(altPrice)+'</div>' +
                  '</div>';
        });
        $('#catalog_items').html(html);
      }).fail(function(){
        $('#catalog_count').text('Failed to load products');
        $('#catalog_items').html('<span class="empty-cart" style="padding:12px;">Could not load products. Tap a filter to retry.</span>');
      });
    }

    function loadCustomerDue(customer_id){
      if(!customer_id){
        $('#customer_due').hide();
        return;
      }
      $.get(base_url + 'mobile/customer_due/' + customer_id, function(data){
        if(typeof data === 'string') data = JSON.parse(data);
        window.customerExtras = data || {};
        window.customerMobile = (data.mobile || '').replace(/\D/g, '');
        var due = parseFloat(data.due) || 0;
        if(due > 0){
          $('#customer_due').html('Previous due: <strong>' + formatMoney(due) + '</strong>').show();
        } else {
          $('#customer_due').hide();
        }
        $('#customer_advance_label').text('Advance: ' + formatMoney(data.tot_advance || 0));
        $('#avail_points').text(data.loyalty_points || 0);
        $('#avail_store_credit').text(formatMoney(data.store_credit_balance || 0));
        if((data.loyalty_points || 0) > 0 || (data.store_credit_balance || 0) > 0 || (data.gift_card_balance || 0) > 0){
          $('#payment_loyalty').show();
        } else {
          $('#payment_loyalty').hide();
        }
      });
    }

    $('#customer_id').select2({
      ajax: {
        url: base_url + 'mobile/customer_search',
        dataType: 'json',
        delay: 250,
        data: function(params){ return { q: params.term }; },
        processResults: function(data){ return { results: data.map(function(r){ return { id: r.id, text: r.customer_name }; }) }; }
      },
      minimumInputLength: 1
    }).on('change', function(){
      loadCustomerDue($(this).val());
    });

    $('#item_search').on('input', function(){
      var q = $(this).val().trim();
      if(q.length >= 2) searchItems(q);
      else $('#item_results').hide();
    });

    $('#item_search_btn').on('click', function(){
      searchItems($('#item_search').val().trim());
    });

    $(document).on('click', '.result-item', function(){
      var id = $(this).data('id');
      addItem(searchItemsMap[id]);
    });

    $(document).on('click', '.recent-item[data-id]', function(){
      var item = catalogItemsMap[String($(this).data('id'))];
      if(!item){ showToast('Product not found', 'error'); return; }
      addItem(item);
    });

    $('#catalog_toggle').on('click', function(){
      var open = $('#catalog_dropdown').toggle().is(':visible');
      $('#catalog_chevron').toggleClass('fa-chevron-down fa-chevron-up');
    });

    $('#catalog_close').on('click', function(){
      $('#catalog_dropdown').hide();
      $('#catalog_chevron').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });

    $('#barcode_input').on('keypress', function(e){
      if(e.which === 13){
        addByBarcode($(this).val().trim());
      }
    });

    $('#barcode_btn').on('click', function(){
      addByBarcode($('#barcode_input').val().trim());
    });

    $(document).on('click', '.qty-plus', function(){
      var idx = $(this).closest('.cart-item').data('index');
      cart[idx].qty += 1;
      renderCart();
    });

    $(document).on('click', '.qty-minus', function(){
      var idx = $(this).closest('.cart-item').data('index');
      cart[idx].qty -= 1;
      if(cart[idx].qty <= 0) cart.splice(idx, 1);
      renderCart();
    });

    $(document).on('change', '#price_type', function(){
      var q = $('#item_search').val().trim();
      if(q.length >= 2) searchItems(q);
      loadCatalog();
      renderCart();
    });

    $(document).on('input', '#discount', function(){
      renderCart();
    });

    $(document).on('change', '#catalog_category', function(){
      loadCatalog();
    });

    $(document).on('change', '#catalog_brand', function(){
      loadCatalog();
    });

    $('#payment_amount_paid').on('input', function(){
      updatePaymentChange();
    });

    var selectedAction = '';

    function openPayment(action){
      var customer_id = $('#customer_id').val();
      if(!customer_id){ showToast('Please select a customer', 'warning'); return; }
      if(cart.length === 0){ showToast('Please add at least one item', 'warning'); return; }

      selectedAction = action;
      $('#redeem_discount').val('0');
      $('#redeem_points').val('0');
      $('#redeem_store_credit_val').val('0');
      $('#redeem_gift_card_id').val('');
      var total = getCartTotal();
      $('#payment_total').text(formatMoney(total));
      $('#payment_change_row').hide();

      if(action === 'pay'){
        $('#payment_title').text('Pay Sale');
        $('#payment_mode_group').hide();
        $('#payment_amount_group').show();
        $('#payment_account_group').show();
        $('#payment_plan_fields').hide();
        $('#payment_rows').hide();
        $('#payment_add_row').hide();
        $('#payment_split_summary').hide();
        $('#payment_amount_paid').val(total.toFixed(2)).attr('placeholder','Enter amount received');
        $('#payment_confirm').text('Confirm Payment');
        initCustomSelect($('#payment_mode_select'));
        initCustomSelect($('#account_id_select'));
      } else if(action === 'split'){
        $('#payment_title').text('Split Payment');
        $('#payment_mode_group').hide();
        $('#payment_amount_group').hide();
        $('#payment_account_group').hide();
        $('#payment_plan_fields').hide();
        $('#payment_split_summary').show();
        $('#payment_change_row').hide();
        $('#payment_rows').show().html('');
        $('#payment_add_row').show();
        addPaymentRow(0);
        $('#payment_confirm').text('Confirm Split');
      } else if(action === 'plan'){
        $('#payment_title').text('PayPlan');
        $('#payment_mode_group').show();
        $('#payment_amount_group').hide();
        $('#payment_account_group').show();
        $('#payment_plan_fields').show();
        $('#payment_rows').hide();
        $('#payment_add_row').hide();
        $('#payment_split_summary').hide();
        $('#payment_mode_select').val($('#payment_mode_select option:first').val());
        var today = new Date();
        var inOneWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
        $('#bnpl_first_due').val(inOneWeek.toISOString().split('T')[0]);
        calculatePlan();
        $('#payment_confirm').text('Confirm Plan');
        initCustomSelect($('#payment_mode_select'));
        initCustomSelect($('#account_id_select'));
      }

      updatePaymentChange();
      $('#payment_modal').addClass('active');
    }

    function submitSale(action){
      var customer_id = $('#customer_id').val();
      if(!customer_id){ showToast('Please select a customer', 'warning'); return; }
      if(cart.length === 0){ showToast('Please add at least one item', 'warning'); return; }

      var total = getCartTotal();
      var inputPaid = parseFloat($('#payment_amount_paid').val()) || 0;
      var amountPaid = 0;

      if(action === 'pay'){
        if(inputPaid > 0 && inputPaid < total){
          showToast('Amount received must be at least the total', 'warning'); return;
        }
        amountPaid = inputPaid > 0 ? inputPaid : -1;
      } else if(action === 'split'){
        var rows = getPaymentRows();
        if(rows.length === 0){
          showToast('Please add at least one payment row', 'warning'); return;
        }
        var rowTotal = rows.reduce(function(s, r){ return s + r.amount; }, 0);
        if(rowTotal < total){
          showToast('Total split payments must equal the sale total (' + formatMoney(total) + ')', 'warning'); return;
        }
        if(rowTotal > total){
          showToast('Total split payments cannot exceed the sale total (' + formatMoney(total) + ')', 'error'); return;
        }
        amountPaid = rowTotal;
      } else if(action === 'plan'){
        calculatePlan();
        var downPct = parseFloat($('#bnpl_down_pct').val()) || 0;
        amountPaid = parseFloat($('#bnpl_down_amt').val()) || 0;
        if(amountPaid > total){
          showToast('Down payment cannot exceed total', 'warning'); return;
        }
      } else if(action === 'hold'){
        amountPaid = 0;
      }

      var btn = (action === 'hold' ? '#hold_sale' : (action === 'split' ? '#split_pay' : (action === 'plan' ? '#pay_plan' : '#pay_sale')));
      var original = $(btn).text();
      $(btn).prop('disabled', true).text('Processing...');
      $('#payment_confirm, #payment_cancel').prop('disabled', true);

      var payload = {
        customer_id: customer_id,
        payment_type: (action === 'pay') ? 'Cash' : $('#payment_mode_select').val(),
        account_id: $('#account_id_select').val(),
        action: action,
        warehouse_id: '',
        discount: (parseFloat($('#discount').val()) || 0) + (parseFloat($('#redeem_discount').val()) || 0),
        sales_note: $('#sales_note').val() || '',
        amount_paid: amountPaid,
        redeem_points: $('#redeem_points').val() || 0,
        redeem_store_credit: $('#redeem_store_credit_val').val() || 0,
        redeem_gift_card_id: $('#redeem_gift_card_id').val() || '',
        redeem_gift_card_amount: $('#gift_card_redeem_amount').val() || 0,
        coupon_code: $('#coupon_code').val() || '',
        allow_tot_advance: $('#allow_tot_advance').is(':checked') ? 'checked' : '',
        cart: cart
      };
      if(action === 'split'){
        payload.payment_rows = getPaymentRows();
      }
      if(action === 'plan'){
        payload.bnpl = {
          down_pct: $('#bnpl_down_pct').val(),
          count: $('#bnpl_count').val(),
          frequency: $('#bnpl_frequency').val(),
          down_amt: $('#bnpl_down_amt').val(),
          each_amt: $('#bnpl_each_amt').val(),
          first_due: $('#bnpl_first_due').val(),
          late_fee: $('#bnpl_late_fee').val()
        };
      }
      // Keep CSRF in payload AND send as header for maximum compatibility
      payload[csrf_name] = csrf_hash;

      var headers = {};
      headers[csrf_name] = csrf_hash;
      
      $.ajax({
        url: base_url + (action === 'hold' ? 'mobile/hold' : 'mobile/save'),
        type: 'POST',
        data: JSON.stringify(payload),
        contentType: 'application/json',
        dataType: 'json',
        headers: headers,
        success: function(res){
          if(res.status === 'success'){
            if(action === 'pay' && res.sales_id){
              lastSalesId = res.sales_id;
              lastWhatsAppUrl = res.whatsapp_url || '';
              $('#payment_modal').removeClass('active');
              $('#payment_confirm, #payment_cancel').prop('disabled', false);
              $('#pay_message').text('Sale #' + res.sales_id + ' saved.');
              $('#pay_modal').addClass('active');
            } else {
              window.location.href = res.redirect;
            }
          } else {
            showToast(res.message || 'Failed to save', 'error');
            $(btn).prop('disabled', false).text(original);
            $('#payment_confirm, #payment_cancel').prop('disabled', false);
          }
        },
        error: function(xhr){
          showToast('Save error: ' + xhr.responseText, 'error');
          $(btn).prop('disabled', false).text(original);
          $('#payment_confirm, #payment_cancel').prop('disabled', false);
        }
      });
    }

    $('#pay_sale').on('click', function(){ openPayment('pay'); });
    $('#hold_sale').on('click', function(){ submitSale('hold'); });
    $('#split_pay').on('click', function(){ openPayment('split'); });
    $('#pay_plan').on('click', function(){ openPayment('plan'); });

    $('#payment_confirm').on('click', function(){ submitSale(selectedAction); });
    $('#payment_cancel').on('click', function(){ $('#payment_modal').removeClass('active'); });

    $('#btn_print').on('click', function(){
      window.open(base_url + 'sales/print_invoice_pos/' + lastSalesId, '_blank');
    });

    $('#btn_share').on('click', function(){
      if(lastWhatsAppUrl){
        window.open(lastWhatsAppUrl, '_blank');
      } else {
        showToast('Receipt link not available yet', 'warning');
      }
    });

    $('#btn_done').on('click', function(){
      window.location.href = base_url + 'mobile';
    });

    function initCustomSelect($sel, onChange){
      if($sel.data('custom-done')) return;
      $sel.data('custom-done', true);
      var $wrap = $('<div class="custom-select"></div>');
      $sel.wrap($wrap);
      $wrap = $sel.closest('.custom-select');
      var $trigger = $('<div class="custom-select-trigger"></div>');
      var $options = $('<div class="custom-select-options"></div>');
      var selVal = $sel.val();
      var selectedText = '';

      $sel.children().each(function(){
        var $opt = $(this);
        if($opt.is('optgroup')){
          $options.append('<div class="optgroup-label">'+$opt.attr('label')+'</div>');
          $opt.children('option').each(function(){
            var $o = $(this);
            var text = $o.text();
            var value = $o.val();
            var cls = 'opt' + (value == selVal ? ' selected' : '');
            if(value == selVal) selectedText = text;
            $options.append('<div class="'+cls+'" data-value="'+value+'">'+text+'</div>');
          });
        } else {
          var text = $opt.text();
          var value = $opt.val();
          var cls = 'opt' + (value == selVal ? ' selected' : '');
          if(value == selVal) selectedText = text;
          $options.append('<div class="'+cls+'" data-value="'+value+'">'+text+'</div>');
        }
      });

      $trigger.text(selectedText || $sel.find('option:first').text());
      $wrap.append($trigger).append($options);

      $trigger.on('click', function(e){
        e.stopPropagation();
        $('.custom-select-options').not($options).hide();
        $options.toggle();
      });

      $options.on('click', '.opt', function(e){
        e.stopPropagation();
        $options.find('.opt').removeClass('selected');
        $(this).addClass('selected');
        var val = $(this).data('value');
        $sel.val(val).trigger('change');
        $trigger.text($(this).text());
        $options.hide();
        if(typeof onChange === 'function') onChange(val);
      });

      $sel.on('change', function(){
        var val = $(this).val();
        $options.find('.opt').removeClass('selected').filter(function(){ return $(this).data('value') == val; }).addClass('selected');
        $trigger.text($options.find('.opt.selected').text() || $sel.find('option:first').text());
      });
    }

    $(document).on('click', function(){
      $('.custom-select-options').hide();
    });

    $(document).on('click', '.modal-close', function(){
      $(this).closest('.modal').removeClass('active');
    });

    $(function(){
      loadCategories();
      loadBrands();
      loadCatalog();
      if(holdData && holdData.items){
        loadHold(holdData);
      } else {
        var urlParams = new URLSearchParams(window.location.search);
        var holdId = parseInt(urlParams.get('hold_id')) || 0;
        if(holdId > 0){
          $.get(base_url + 'mobile/get_hold/' + holdId, function(data){
            if(data && data.items){
              loadHold(data);
            } else {
              showToast('Hold not found or empty', 'error');
            }
          }, 'json').fail(function(){
            showToast('Could not load hold', 'error');
          });
        }
      }
    });
  </script>
  <?php $this->load->view('mobile/bottom_nav', ['active' => ($active ?? 'sale')]); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
