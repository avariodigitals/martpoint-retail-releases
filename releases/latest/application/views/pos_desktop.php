<?php
/*
 * The POS is a standalone full-page app: it does NOT load mp_layout or the clinic
 * shell, it defines its own palette and header. That is why the clinic's POS link
 * appeared to lead to "the old shell" — it led to the retail-blue page while the
 * rest of the clinic was green.
 *
 * Rather than adopt the whole shell here (the POS is deliberately a distraction-
 * free, full-screen selling surface), the palette follows the store's business
 * type, and a Clinapp link is offered so a clinic user is never stranded inside
 * the till with no way back.
 */
$mp_pos_profile = function_exists('mp_get_store_profile') ? mp_get_store_profile() : [];
$mp_is_clinic = function_exists('physio_enabled') && $this->session->userdata('logged_in') && (($mp_pos_profile['industry_type'] ?? '') === 'physiotherapy_rehabilitation');
$mp_is_pharmacy = in_array(($mp_pos_profile['industry_type'] ?? ''), ['pharmacy', 'medical_store'], true);
$mp_is_restaurant = function_exists('mp_is_restaurant_workspace') && mp_is_restaurant_workspace();
$mp_pos_palette = $mp_is_restaurant ? [
    'primary' => '#5E4D66', 'primary_dark' => '#4E3F55', 'bg' => '#F7F6F2',
    'text' => '#3A3733', 'muted' => '#77736D', 'border' => '#E9E4DD',
    'success' => '#2F9A67', 'danger' => '#D6534F', 'warning' => '#D68A32', 'ink' => '#262522',
] : ($mp_is_clinic ? [
    'primary' => '#176753', 'primary_dark' => '#104d40', 'bg' => '#eef3f0',
    'text' => '#1e2d28', 'muted' => '#687a72', 'border' => '#d8e2dc',
    'success' => '#176753', 'danger' => '#c95f49', 'warning' => '#a96e24', 'ink' => '#1e2d28',
] : ($mp_is_pharmacy ? [
    'primary' => '#0F766E', 'primary_dark' => '#0B5B55', 'bg' => '#F2F8F7',
    'text' => '#16323A', 'muted' => '#6B7F86', 'border' => '#D7E7E5',
    'success' => '#0F766E', 'danger' => '#C94F4F', 'warning' => '#B7791F', 'ink' => '#16323A',
] : [
    'primary' => '#0057FF', 'primary_dark' => '#0044CC', 'bg' => '#F5F4F0',
    'text' => '#292524', 'muted' => '#78716C', 'border' => '#E7E5E4',
    'success' => '#059669', 'danger' => '#DC2626', 'warning' => '#F59E0B', 'ink' => '#44403C',
]));
// Database text imported from older fleets is not always valid UTF-8.
// Never allow one name to turn an inline assignment into `const products = ;`.
$pos_json = static function ($value) {
    return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(get_store_name() ?: 'MartPoint') ?> — <?= $mp_is_pharmacy ? 'Dispensing & Checkout' : ($mp_is_restaurant ? 'Restaurant Order Desk' : 'Point of Sale') ?></title>
  <link rel="icon" href="<?= $favicon_url ?>">
  <link rel="apple-touch-icon" href="<?= $favicon_url ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root {
      --mp-primary: <?= $mp_pos_palette['primary'] ?>;
      --mp-primary-dark: <?= $mp_pos_palette['primary_dark'] ?>;
      --mp-pay: #D97706;
      --mp-pay-dark: #B45309;
      --mp-bg: <?= $mp_pos_palette['bg'] ?>;
      --mp-surface: #FFFFFF;
      --mp-text: <?= $mp_pos_palette['text'] ?>;
      --mp-muted: <?= $mp_pos_palette['muted'] ?>;
      --mp-border: <?= $mp_pos_palette['border'] ?>;
      --mp-success: <?= $mp_pos_palette['success'] ?>;
      --mp-danger: <?= $mp_pos_palette['danger'] ?>;
      --mp-warning: <?= $mp_pos_palette['warning'] ?>;
      --mp-ink: <?= $mp_pos_palette['ink'] ?>;
      --shadow-sm: 0 1px 2px rgba(41, 37, 36, 0.05);
      --shadow: 0 10px 25px -5px rgba(41, 37, 36, 0.08), 0 4px 10px -4px rgba(41, 37, 36, 0.04);
    }
    body.dark {
      --mp-bg: #1C1917;
      --mp-surface: #292524;
      --mp-text: #F5F4F0;
      --mp-muted: #A8A29E;
      --mp-border: #44403C;
      --mp-ink: #E7E5E4;
      --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 4px 10px -4px rgba(0, 0, 0, 0.3);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; height: 100%; }
    body {
      font-family: <?= $mp_is_pharmacy ? "'Manrope'" : "'Inter'" ?>, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: var(--mp-bg);
      color: var(--mp-text);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    <?php if($mp_is_pharmacy): ?>
    body.pharmacy-pos .app-header { background:#fbfefe; border-bottom-color:#d7e7e5; }
    body.pharmacy-pos .app-header { box-shadow:0 1px 0 rgba(15,118,110,.08); }
    body.pharmacy-pos .catalog, body.pharmacy-pos .cart, body.pharmacy-pos .sidebar { background:#f8fcfc; }
    body.pharmacy-pos .workspace { grid-template-columns:220px minmax(0,1fr) 440px; gap:16px; padding:18px; background:#eef6f5; }
    body.pharmacy-pos .panel, body.pharmacy-pos .catalog, body.pharmacy-pos .cart { border-radius:12px; box-shadow:0 4px 14px rgba(22,50,58,.05); }
    body.pharmacy-pos .panel-title { letter-spacing:.01em; }
    body.pharmacy-pos .catalog-header { flex-wrap:wrap; padding:14px 16px; background:#fbfefe; }
    body.pharmacy-pos .pharmacy-catalog-title { width:100%; display:flex; align-items:baseline; justify-content:space-between; gap:12px; }
    body.pharmacy-pos .pharmacy-catalog-title strong { font-size:15px; color:#16323a; }
    body.pharmacy-pos .pharmacy-catalog-title small { color:#6b7f86; font-size:11px; }
    body.pharmacy-pos .product-card { border-radius:12px; padding:14px; }
    body.pharmacy-pos .product-card:hover { transform:none; box-shadow:0 7px 18px rgba(15,118,110,.10); }
    body.pharmacy-pos .product-thumb { border-radius:10px; }
    body.pharmacy-pos .pharmacy-medicine-thumb { background:#e5f4f1; color:#0f766e; }
    body.pharmacy-pos .pharmacy-medicine-thumb i { font-size:24px; }
    body.pharmacy-pos .cart { border-top:3px solid #0f766e; }
    body.pharmacy-pos .cart-header { padding:15px 16px 13px; }
    body.pharmacy-pos .cart-header h2 { font-size:17px; letter-spacing:-.02em; }
    body.pharmacy-pos .customer-name-display { color:#0f766e!important; }
    body.pharmacy-pos .pay-btn { font-size:16px; letter-spacing:.01em; }
    body.pharmacy-pos .panel, body.pharmacy-pos .cart-header, body.pharmacy-pos .cart-summary { border-color:#d7e7e5; }
    body.pharmacy-pos .pay-btn { background:#0f766e; }
    body.pharmacy-pos .pay-btn:hover { background:#0b5b55; }
    body.pharmacy-pos .pharmacy-consult-card { margin:12px 0 0; padding:12px; border:1px solid #cfe4e4; border-radius:14px; background:#eff9f7; }
    body.pharmacy-pos .pharmacy-consult-copy { display:flex; gap:9px; align-items:center; min-width:0; }
    body.pharmacy-pos .pharmacy-consult-copy strong, body.pharmacy-pos .pharmacy-consult-copy small { display:block; }
    body.pharmacy-pos .pharmacy-consult-copy strong { font-size:12px; }
    body.pharmacy-pos .pharmacy-consult-copy small { color:#6b7f86; font-size:10px; margin-top:2px; }
    body.pharmacy-pos .pharmacy-consult-icon { width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:9px; background:#d9f0ea; color:#0f766e; flex-shrink:0; }
    body.pharmacy-pos .pharmacy-consult-btn { width:100%; margin-top:9px; border:1px solid #9bcac7; border-radius:9px; background:#fff; color:#0f766e; padding:8px 10px; font-size:12px; font-weight:800; cursor:pointer; }
    body.pharmacy-pos .pharmacy-consult-summary { margin-top:8px; color:#47636a; font-size:11px; white-space:pre-line; border-top:1px solid #cfe4e4; padding-top:8px; }
    body.pharmacy-pos .pharmacy-consult-modal .modal { max-width:560px; }
    body.pharmacy-pos .pharmacy-voice-btn { width:36px; height:36px; flex:0 0 36px; display:inline-flex; align-items:center; justify-content:center; border:1px solid #cfe4e4; border-radius:9px; background:#eff9f7; color:#0f766e; padding:0; font-size:13px; font-weight:800; cursor:pointer; }
    body.pharmacy-pos .pharmacy-voice-btn.listening { background:#0f766e; color:#fff; }
    <?php endif; ?>

    .app-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      padding: 12px 24px;
      background: var(--mp-surface);
      border-bottom: 1px solid var(--mp-border);
      box-shadow: var(--shadow-sm);
      z-index: 20;
      overflow: visible;
    }
    .brand { flex-shrink: 0; }
    /* Clinic escape hatch: the POS is full-screen with no shell nav, so a clinic
       user needs one obvious way back to their workspace. */
    .mp-back { display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0; padding: 8px 14px; border-radius: 9px; background: rgba(255,255,255,.14); color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; }
    .mp-back:hover { background: rgba(255,255,255,.24); color: #fff; text-decoration: none; }
    .header-actions { flex-shrink: 0; }
    .intelligence {
      flex: 1;
      min-width: 0;
      display: flex;
      align-items: center;
      gap: 10px;
      background: var(--mp-bg);
      border: 1px solid var(--mp-border);
      border-radius: 12px;
      padding: 8px 14px;
      overflow: hidden;
    }
    .intelligence-label {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 800;
      color: var(--mp-ink);
      text-transform: uppercase;
      letter-spacing: 0.04em;
      white-space: nowrap;
      flex-shrink: 0;
    }
    .marquee {
      flex: 1;
      min-width: 0;
      overflow: hidden;
      white-space: nowrap;
      mask-image: linear-gradient(to right, transparent, black 24px, black calc(100% - 24px), transparent);
    }
    .marquee-track {
      display: inline-flex;
      white-space: nowrap;
      animation: marquee 30s linear infinite;
    }
    .marquee-track:hover { animation-play-state: paused; }
    @keyframes marquee {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }
    .marquee-items { display: inline-flex; }
    .marquee-item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-right: 48px;
      font-size: 13px;
      font-weight: 500;
      color: var(--mp-ink);
    }
    .marquee-item.unsold { color: var(--mp-danger); }
    .marquee-item.trend { color: var(--mp-pay); }
    .marquee-item.stock { color: var(--mp-warning); }
    .brand h1 {
      font-size: 18px;
      font-weight: 700;
      margin: 0;
      color: var(--mp-primary);
    }
    .brand .sub {
      font-size: 12px;
      color: var(--mp-muted);
      font-weight: 500;
    }
    .header-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .header-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 16px;
      border: 1px solid var(--mp-border);
      border-radius: 12px;
      background: var(--mp-surface);
      color: var(--mp-ink);
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.15s ease;
      text-decoration: none;
    }
    .header-btn:hover { background: var(--mp-bg); }
    .header-btn.primary {
      background: var(--mp-primary);
      border-color: var(--mp-primary);
      color: #fff;
    }
    .header-btn.primary:hover { background: var(--mp-primary-dark); }
    .header-btn.success {
      background: var(--mp-success);
      border-color: var(--mp-success);
      color: #fff;
    }
    .header-btn.success:hover { opacity: 0.9; }
    .badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 20px;
      height: 20px;
      padding: 0 6px;
      background: var(--mp-danger);
      color: #fff;
      border-radius: 10px;
      font-size: 12px;
      font-weight: 700;
    }
    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #fff;
    }
    .user-menu { position: relative; }
    .user-chip {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 6px 12px 6px 6px;
      border: 1px solid var(--mp-border);
      border-radius: 24px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      user-select: none;
    }
    .user-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: #E0E7FF;
      color: var(--mp-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 700;
    }
    .user-dropdown {
      position: absolute;
      top: calc(100% + 8px);
      right: 0;
      min-width: 220px;
      background: var(--mp-surface);
      border: 1px solid var(--mp-border);
      border-radius: 14px;
      box-shadow: var(--shadow);
      padding: 8px;
      opacity: 0;
      transform: translateY(-8px) scale(0.98);
      pointer-events: none;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      z-index: 50;
    }
    .user-dropdown.show { opacity: 1; transform: translateY(0) scale(1); pointer-events: auto; }
    .dropdown-item {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;
      padding: 10px 12px;
      border: none;
      border-radius: 10px;
      background: transparent;
      color: var(--mp-ink);
      font-size: 14px;
      font-weight: 600;
      text-align: left;
      cursor: pointer;
    }
    .dropdown-item:hover { background: var(--mp-bg); }
    .dropdown-item.danger { color: var(--mp-danger); }
    .dropdown-divider { height: 1px; background: var(--mp-border); margin: 6px 4px; }

    .workspace {
      flex: 1;
      display: grid;
      grid-template-columns: 200px 1fr 480px;
      gap: 20px;
      padding: 20px;
      overflow: hidden;
    }

    .sidebar {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .panel {
      background: var(--mp-surface);
      border: 1px solid var(--mp-border);
      border-radius: 16px;
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }
    .panel-title {
      padding: 16px 16px 0;
      font-size: 14px;
      font-weight: 700;
      color: var(--mp-ink);
    }
    .category-list {
      list-style: none;
      margin: 0;
      padding: 8px;
      max-height: 200px;
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
    }
    .category-list li {
      padding: 12px 12px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 600;
      color: var(--mp-muted);
      cursor: pointer;
      transition: all 0.15s ease;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .category-list li:hover { background: var(--mp-bg); color: var(--mp-ink); }
    .category-list li.active { background: var(--mp-primary); color: #fff; }
    .category-list li .count {
      font-size: 12px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 10px;
      background: rgba(0,0,0,0.05);
    }
    .category-list li.active .count { background: rgba(255,255,255,0.25); }

    .insight-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
      padding: 0 16px 16px;
    }
    .insight-item { display: flex; flex-direction: column; gap: 6px; }
    .insight-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 13px;
      font-weight: 600;
    }
    .insight-top .rank {
      width: 22px;
      color: var(--mp-muted);
      font-weight: 700;
    }
    .insight-top .name {
      flex: 1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      padding: 0 8px;
    }
    .insight-top .sold { color: var(--mp-primary); }
    .insight-bar {
      height: 6px;
      border-radius: 999px;
      background: var(--mp-bg);
      overflow: hidden;
    }
    .insight-bar .fill {
      height: 100%;
      border-radius: 999px;
      background: var(--mp-primary);
      transition: width 0.4s ease;
    }

    .progress-track {
      height: 10px;
      border-radius: 999px;
      background: var(--mp-bg);
      overflow: hidden;
    }
    .progress-fill {
      height: 100%;
      border-radius: 999px;
      background: var(--mp-primary);
      transition: width 0.4s ease;
    }
    .target-meta {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      color: var(--mp-muted);
      margin-top: 8px;
    }
    .target-amount {
      font-size: 22px;
      font-weight: 700;
      color: var(--mp-primary);
      margin: 4px 0;
    }

    .catalog {
      display: flex;
      flex-direction: column;
      min-width: 0;
      background: var(--mp-surface);
      border: 1px solid var(--mp-border);
      border-radius: 16px;
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }
    .catalog-header {
      padding: 16px;
      border-bottom: 1px solid var(--mp-border);
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .search-bar {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border: 1px solid var(--mp-border);
      border-radius: 14px;
      background: var(--mp-bg);
    }
    .search-bar svg { color: var(--mp-muted); flex-shrink: 0; }
    .search-bar input {
      border: none;
      background: transparent;
      outline: none;
      font-size: 15px;
      width: 100%;
      color: var(--mp-text);
    }
    .search-bar input::placeholder { color: var(--mp-muted); }
    .view-toggle {
      display: flex;
      align-items: center;
      gap: 2px;
      padding: 4px;
      border: 1px solid var(--mp-border);
      border-radius: 12px;
      background: var(--mp-bg);
      flex-shrink: 0;
    }
    .view-toggle button {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border: none;
      border-radius: 9px;
      background: transparent;
      color: var(--mp-muted);
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .view-toggle button.active {
      background: var(--mp-surface);
      color: var(--mp-primary);
      box-shadow: var(--shadow-sm);
    }
    .view-toggle button:hover:not(.active) { color: var(--mp-text); }

    .products.list-mode {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .product-row {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 14px;
      border: 1px solid var(--mp-border);
      border-radius: 12px;
      background: var(--mp-surface);
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .product-row:hover {
      border-color: var(--mp-primary);
      box-shadow: var(--shadow-sm);
    }
    .product-row.out { opacity: 0.6; cursor: not-allowed; }
    .product-row .row-thumb {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      background: var(--mp-bg);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--mp-muted);
      flex-shrink: 0;
      overflow: hidden;
      position: relative;
    }
    .product-row .row-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .product-row .row-info { flex: 1; min-width: 0; }
    .product-row .row-name {
      font-size: 14px;
      font-weight: 600;
      line-height: 1.3;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .product-row .row-meta {
      font-size: 12px;
      color: var(--mp-muted);
      margin-top: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .stock-chip {
      display: inline-flex;
      align-items: center;
      font-size: 11px;
      font-weight: 700;
      padding: 3px 9px;
      border-radius: 20px;
      flex-shrink: 0;
      white-space: nowrap;
    }
    .stock-chip.in { background: rgba(5,150,105,.1); color: var(--mp-success); }
    .stock-chip.low { background: rgba(217,119,6,.12); color: var(--mp-warning); }
    .stock-chip.out { background: rgba(220,38,38,.1); color: var(--mp-danger, #dc2626); }
    .product-row .row-price {
      font-size: 15px;
      font-weight: 700;
      color: var(--mp-primary);
      white-space: nowrap;
    }
    .pos-sale-badge {
      display: inline-flex;
      align-items: center;
      background: rgba(220,38,38,.1);
      color: var(--mp-danger, #dc2626);
      border-radius: 20px;
      padding: 3px 8px;
      font-size: 10px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .03em;
      margin-right: 5px;
    }
    .pos-old-price { color: var(--mp-muted); text-decoration: line-through; font-size: 11px; font-weight: 500; margin-left: 5px; }
    .product-row .add-btn {
      width: 32px;
      height: 32px;
      border: none;
      border-radius: 9px;
      background: var(--mp-primary);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      flex-shrink: 0;
    }
    .product-row .add-btn:hover { background: var(--mp-primary-dark); }
    .product-row .add-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .products {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 16px;
      align-content: start;
    }
    .product-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 16px;
      border: 1px solid var(--mp-border);
      border-radius: 16px;
      background: var(--mp-surface);
      cursor: pointer;
      transition: all 0.15s ease;
      text-align: center;
      position: relative;
    }
    .product-card:hover {
      border-color: var(--mp-primary);
      box-shadow: var(--shadow);
      transform: translateY(-2px);
    }
    .new-tag {
      position: absolute;
      top: 10px;
      left: 10px;
      background: var(--mp-success);
      color: #fff;
      font-size: 10px;
      font-weight: 800;
      padding: 4px 8px;
      border-radius: 20px;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .sale-tag {
      position: absolute;
      top: 10px;
      left: 10px;
      background: rgba(220,38,38,.95);
      color: #fff;
      font-size: 10px;
      font-weight: 800;
      padding: 4px 8px;
      border-radius: 20px;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .price-tag {
      position: absolute;
      top: 10px;
      right: 10px;
      background: var(--mp-bg);
      color: var(--mp-primary);
      font-size: 11px;
      font-weight: 700;
      padding: 4px 8px;
      border-radius: 20px;
    }
    .product-thumb {
      width: 72px;
      height: 72px;
      border-radius: 14px;
      background: var(--mp-bg);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 12px;
      color: var(--mp-muted);
    }
    .product-name {
      font-size: 14px;
      font-weight: 600;
      line-height: 1.3;
      min-height: 2.6em;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      line-clamp: 2;
      -webkit-box-orient: vertical;
    }
    .product-price {
      font-size: 16px;
      font-weight: 700;
      color: var(--mp-primary);
      margin: 8px 0 12px;
    }
    .product-card .add-btn {
      width: 36px;
      height: 36px;
      border: none;
      border-radius: 10px;
      background: var(--mp-primary);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    .product-card .add-btn:hover { background: var(--mp-primary-dark); }

    .cart {
      display: flex;
      flex-direction: column;
      background: var(--mp-surface);
      border: 1px solid var(--mp-border);
      border-radius: 16px;
      box-shadow: var(--shadow-sm);
      overflow-y: auto;
      overflow-x: hidden;
    }
    .cart-header {
      padding: 16px;
      border-bottom: 1px solid var(--mp-border);
    }
    .cart-header h2 { margin: 0; font-size: 16px; font-weight: 700; }
    .price-type {
      margin-top: 14px;
    }
    .price-type-label {
      display: block;
      font-size: 12px;
      color: var(--mp-muted);
      margin-bottom: 6px;
      font-weight: 500;
    }
    .option-chips {
      display: flex;
      border: 1px solid var(--mp-border);
      border-radius: 12px;
      overflow: hidden;
      background: var(--mp-surface);
    }
    .option-chips button {
      flex: 1;
      padding: 10px 8px;
      border: none;
      border-left: 1px solid var(--mp-border);
      background: var(--mp-surface);
      color: var(--mp-ink);
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
    }
    .option-chips button:first-child { border-left: none; }
    .option-chips button.active { background: var(--mp-primary); color: #fff; border-left-color: var(--mp-primary); }
    .customer-select {
      margin-top: 14px;
      position: relative;
    }
    .customer-search {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid var(--mp-border);
      border-radius: 12px;
      font-size: 14px;
      background: var(--mp-surface);
      outline: none;
    }
    .customer-dropdown {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      max-height: 260px;
      overflow-y: auto;
      background: var(--mp-surface);
      border: 1px solid var(--mp-border);
      border-radius: 12px;
      z-index: 20;
      margin-top: 4px;
      box-shadow: var(--shadow);
    }
    .customer-option {
      display: flex;
      align-items: center;
      gap: 9px;
      padding: 10px 14px;
      font-size: 14px;
      cursor: pointer;
      border-bottom: 1px solid var(--mp-border);
    }
    .customer-option .mp-person-avatar { flex-shrink: 0; }
    .customer-option-copy { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .customer-option:last-child { border-bottom: none; }
    .customer-option:hover, .customer-option.active { background: var(--mp-primary); color: #fff; }
    .customer-option.hidden { display: none; }
    .customer-select select { display: none; }
    .cart-items {
      flex: 1;
      min-height: 160px;
      overflow-y: auto;
      padding: 12px 16px;
    }
    .cart-empty {
      text-align: center;
      padding: 32px 16px;
      color: var(--mp-muted);
      font-size: 14px;
    }
    .cart-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px 0;
      border-bottom: 1px solid var(--mp-border);
    }
    .cart-item:last-child { border-bottom: none; }
    .cart-item .item-info { flex: 1; min-width: 0; }
    .cart-item .item-name { font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cart-item .item-meta { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .cart-item .item-total { font-weight: 700; font-size: 14px; white-space: nowrap; }
    .cart-item.weight-cart-item {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      column-gap: 10px;
      row-gap: 8px;
    }
    .weight-cart-item .item-info { grid-column: 1 / -1; }
    .weight-cart-item .qty-control { grid-column: 1 / -1; width:100%; justify-content:space-between; }
    .weight-cart-item .weight-entry { flex:1; justify-content:space-between; }
    .weight-cart-item .item-total { grid-column: 1; justify-self:end; align-self:center; }
    .weight-cart-item .remove-btn { grid-column: 2; align-self:center; }
    .qty-control {
      display: flex;
      align-items: center;
      gap: 6px;
      background: var(--mp-bg);
      border-radius: 10px;
      padding: 3px;
    }
    .qty-control button {
      width: 28px;
      height: 28px;
      border: none;
      border-radius: 8px;
      background: var(--mp-surface);
      color: var(--mp-primary);
      font-size: 18px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .qty-control span { min-width: 24px; text-align: center; font-weight: 700; font-size: 14px; }
    .weight-entry { display:flex; align-items:center; gap:28px; width:100%; min-width:0; white-space:nowrap; }
    .weight-part { display:flex; align-items:center; gap:4px; flex:1; min-width:0; }
    .weight-part .weight-label { color:var(--mp-muted); font-size:11px; font-weight:700; text-transform:uppercase; }
    .weight-entry input { width:96px; height:38px; padding:4px 8px; border:1px solid var(--mp-border); border-radius:8px; background:var(--mp-surface); color:var(--mp-ink); font-size:18px; font-weight:700; text-align:center; }
    .weight-entry .weight-total { min-width:96px; color:var(--mp-ink); font-size:12px; font-weight:700; text-align:center; }
    .remove-btn {
      width: 28px;
      height: 28px;
      border: none;
      background: transparent;
      color: var(--mp-danger);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .cart-summary {
      padding: 16px;
      border-top: 1px solid var(--mp-border);
      background: var(--mp-bg);
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
      margin-bottom: 8px;
      color: var(--mp-muted);
    }
    .summary-row.total {
      font-size: 20px;
      font-weight: 700;
      color: var(--mp-text);
      margin-top: 12px;
      padding-top: 12px;
      border-top: 1px solid var(--mp-border);
    }
    .discount-control {
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 10px 0;
    }
    .discount-control input {
      flex: 1;
      padding: 10px 12px;
      border: 1px solid var(--mp-border);
      border-radius: 10px;
      font-size: 14px;
      outline: none;
    }
    .discount-control input:disabled { background: var(--mp-bg); color: var(--mp-muted); }
    .clock-notice {
      font-size: 12px;
      color: var(--mp-warning);
      margin: 8px 0;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .payment-methods {
      display: flex;
      gap: 8px;
      margin: 12px 0;
      flex-wrap: wrap;
    }
    .method-chip {
      padding: 8px 14px;
      border: 1px solid var(--mp-border);
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
      background: var(--mp-surface);
      cursor: pointer;
    }
    .method-chip.active { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .pay-btn {
      width: 100%;
      padding: 16px;
      border: none;
      border-radius: 14px;
      background: #22c55e;
      color: #fff;
      font-size: 17px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 8px;
    }
    .pay-btn:hover { background: #16a34a; }
    .pay-btn:disabled { opacity: 0.6; cursor: not-allowed; }
    .pay-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin-top: 10px;
    }
    .pay-action {
      padding: 12px 6px;
      border: 1px solid var(--mp-border);
      border-radius: 12px;
      background: var(--mp-surface);
      color: var(--mp-ink);
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .pay-action:hover { background: var(--mp-bg); }
    .pay-action:disabled { opacity: 0.5; cursor: not-allowed; }
    .payment-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--mp-muted);
      margin: 12px 0 6px;
    }

    .copyright {
      text-align: center;
      padding: 10px 24px;
      background: var(--mp-surface);
      border-top: 1px solid var(--mp-border);
      color: var(--mp-muted);
      font-size: 12px;
    }

    .toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 200;
      display: flex;
      flex-direction: column;
      gap: 10px;
      pointer-events: none;
    }
    .toast {
      background: var(--mp-surface);
      color: var(--mp-text);
      border: 1px solid var(--mp-border);
      border-radius: 0;
      box-shadow: var(--shadow);
      padding: 14px 16px;
      min-width: 300px;
      max-width: 420px;
      display: flex;
      align-items: flex-start;
      gap: 12px;
      transform: translateX(120%);
      opacity: 0;
      transition: all 0.45s cubic-bezier(0.16, 1, 0.3, 1);
      pointer-events: auto;
    }
    .toast.show { transform: translateX(0); opacity: 1; }
    .toast.hide { transform: translateX(-120%); opacity: 0; }
    .toast-icon {
      width: 32px;
      height: 32px;
      border-radius: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .toast.success .toast-icon { background: #D1FAE5; color: var(--mp-success); }
    .toast.danger .toast-icon { background: #FEE2E2; color: var(--mp-danger); }
    .toast.warning .toast-icon { background: #FEF3C7; color: #B45309; }
    .toast-content { flex: 1; min-width: 0; }
    .toast-title { font-size: 14px; font-weight: 700; margin: 0 0 2px; }
    .toast-message { font-size: 13px; color: var(--mp-muted); line-height: 1.35; }
    .toast-close {
      width: 24px;
      height: 24px;
      border: none;
      background: transparent;
      color: var(--mp-muted);
      font-size: 20px;
      line-height: 22px;
      cursor: pointer;
      border-radius: 0;
      flex-shrink: 0;
    }
    .toast-close:hover { background: var(--mp-bg); }

    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(15,23,42,0.45);
      z-index: 500;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.25s ease;
    }
    .modal-backdrop.active { opacity: 1; pointer-events: auto; }
    .modal {
      background: var(--mp-surface);
      border-radius: 20px;
      padding: 28px;
      width: 92%;
      max-width: 420px;
      box-shadow: var(--shadow);
      text-align: center;
      transform: scale(0.96) translateY(12px);
      opacity: 0;
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modal-backdrop.active .modal { transform: scale(1) translateY(0); opacity: 1; }
    
    #receiptContent {
      text-align: center;
    }
    
    .modal-icon {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: #FEF3C7;
      color: #B45309;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 14px;
    }
    .modal h3 { margin: 0 0 8px; font-size: 18px; }
    .modal p { margin: 0 0 20px; color: var(--mp-muted); font-size: 14px; }
    .modal .form-group {
      text-align: left;
      margin-bottom: 14px;
    }
    .modal .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 6px;
      color: var(--mp-ink);
    }
    .modal .form-group input {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid var(--mp-border);
      border-radius: 12px;
      font-size: 15px;
      outline: none;
    }
    .modal .form-group input:focus { border-color: var(--mp-primary); }
    .modal-actions {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }
    .modal-actions button {
      flex: 1;
      padding: 12px;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      border: none;
    }
    .modal-actions .btn-secondary { background: var(--mp-bg); color: var(--mp-ink); }
    .modal-actions .btn-primary { background: var(--mp-primary); color: #fff; }
    .modal-actions #paymentSubmitBtn { background: #22c55e; }
    .modal-actions #paymentSubmitBtn:hover { background: #16a34a; }
    .calculator-display { width:100%; padding:14px 16px; border:1px solid var(--mp-border); border-radius:12px; background:var(--mp-bg); color:var(--mp-ink); font-size:28px; font-weight:700; text-align:right; box-sizing:border-box; }
    .calculator-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-top:14px; }
    .calculator-key { min-height:48px; border:1px solid var(--mp-border); border-radius:10px; background:var(--mp-surface); color:var(--mp-ink); font-size:18px; font-weight:700; cursor:pointer; }
    .calculator-key:hover { background:var(--mp-bg); }
    .calculator-key.operator { color:var(--mp-primary); background:var(--mp-bg); }
    .calculator-key.equals { background:var(--mp-primary); color:#fff; border-color:var(--mp-primary); }

    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }

    @media (max-width: 1100px) {
      .workspace { grid-template-columns: 160px 1fr 420px; gap: 14px; padding: 14px; }
      .products { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
    }
    @media (max-width: 900px) {
      .workspace { grid-template-columns: 1fr; grid-template-rows: auto 1fr auto; overflow-y: auto; }
      .sidebar { flex-direction: row; overflow-x: auto; }
      .category-list { display: flex; gap: 8px; max-height: none; overflow-y: visible; }
      .category-list li { white-space: nowrap; }
      .cart { max-height: 500px; }
    }

    /* Payment plan card */
    .bnpl-card {
      background: var(--mp-surface);
      border: 1px solid var(--mp-border);
      border-radius: 16px;
      padding: 20px;
      margin-top: 16px;
    }
    .bnpl-title {
      font-size: 15px;
      font-weight: 700;
      margin: 0 0 18px 0;
      color: var(--mp-ink);
      letter-spacing: -0.2px;
    }
    .bnpl-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
    }
    .bnpl-field {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .bnpl-field label {
      font-size: 11px;
      font-weight: 700;
      color: var(--mp-muted);
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }
    .bnpl-input,
    .bnpl-select {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid var(--mp-border);
      border-radius: 10px;
      font-size: 14px;
      color: var(--mp-ink);
      background: var(--mp-surface);
      outline: none;
      transition: border-color 0.15s, box-shadow 0.15s;
    }
    .bnpl-input:focus,
    .bnpl-select:focus {
      border-color: var(--mp-primary);
      box-shadow: 0 0 0 3px rgba(0,87,255,0.10);
    }
    .bnpl-select {
      cursor: pointer;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%236B7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      padding-right: 34px;
    }
    .bnpl-input[readonly],
    .bnpl-computed {
      background: var(--mp-bg);
      font-weight: 700;
      color: var(--mp-ink);
    }
    @media (max-width: 600px) {
      .bnpl-grid { grid-template-columns: 1fr; }
    }

    /* Reusable desktop custom select */
    .mp-dselect { position: relative; width: 100%; }
    select.mp-dselect-native { display: none !important; }
    .mp-dselect__trigger {
      width: 100%;
      padding: 10px 32px 10px 12px;
      border: 1px solid var(--mp-border);
      border-radius: 10px;
      font-size: 13px;
      font-weight: 500;
      background: var(--mp-surface);
      color: var(--mp-ink);
      cursor: pointer;
      display: flex;
      align-items: center;
      min-height: 40px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      position: relative;
    }
    .mp-dselect__trigger::after {
      content: '';
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      width: 10px;
      height: 6px;
      background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%236B7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: center;
      pointer-events: none;
    }
    .mp-dselect__trigger.placeholder { color: var(--mp-muted); }
    .mp-dselect__list {
      display: none;
      position: absolute;
      top: calc(100% + 4px);
      left: 0;
      min-width: 100%;
      width: max-content;
      max-height: 220px;
      overflow-y: auto;
      border: 1px solid var(--mp-border);
      border-radius: 10px;
      background: var(--mp-surface);
      z-index: 1000;
      box-shadow: var(--mp-shadow);
    }
    .mp-dselect.open .mp-dselect__list { display: block; }
    .mp-dselect__option {
      padding: 10px 14px;
      cursor: pointer;
      border-bottom: 1px solid var(--mp-border);
      font-size: 13px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .mp-dselect__option:last-child { border-bottom: none; }
    .mp-dselect__option:hover { background: var(--mp-bg); }
    .mp-dselect__option.active { background: rgba(0,87,255,0.08); color: var(--mp-primary); font-weight: 700; }

    .payment-method-row { border-radius: 12px !important; border: 1px solid var(--mp-border) !important; background: var(--mp-surface) !important; padding: 12px !important; }
    .payment-amount,
    .payment-ref { width: 100%; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 13px; outline: none; box-sizing: border-box; }
    .payment-amount:focus,
    .payment-ref:focus { border-color: var(--mp-primary); box-shadow: 0 0 0 3px rgba(0,87,255,0.10); }

    /* Compact POS layout for 1366x768 and similar POS terminals */
    @media (min-width: 901px) and (max-height: 900px) {
      .app-header { padding: 8px 16px; gap: 12px; }
      .brand h1 { font-size: 16px; }
      .brand .sub { font-size: 11px; }
      .intelligence { padding: 6px 10px; border-radius: 10px; }
      .marquee-item { margin-right: 32px; font-size: 12px; }
      .header-actions { gap: 8px; }
      .header-btn { padding: 8px 10px; font-size: 13px; }
      .header-btn:not(.primary) span { display: none; }
      .user-chip { padding: 4px 10px 4px 4px; font-size: 13px; }
      .user-avatar { width: 28px; height: 28px; font-size: 12px; }
      .workspace { grid-template-columns: 200px 1fr 360px; gap: 12px; padding: 12px; }
      .sidebar { gap: 10px; overflow-y: auto; min-height: 0; }
      .panel { border-radius: 12px; min-height: 0; }
      .panel-title { padding: 10px 10px 0; font-size: 13px; }
      .category-list { padding: 6px; max-height: 240px; overflow-y: auto; }
      .category-list li { padding: 9px 10px; font-size: 14px; line-height: 1.35; }
      .insight-list { gap: 8px; padding: 0 10px 10px; max-height: 140px; overflow-y: auto; }
      .insight-top { font-size: 12px; }
      .insight-top .name { white-space: normal; overflow: visible; text-overflow: clip; padding: 0 4px; font-size: 12px; line-height: 1.3; }
      .insight-top .rank, .insight-top .sold { font-size: 11px; }
      .insight-bar { height: 4px; }
      .target-amount { font-size: 20px; }
      #posClock { font-size: 20px !important; }
      .catalog { border-radius: 12px; }
      .catalog-header { padding: 12px; }
      .search-bar { padding: 8px 12px; border-radius: 10px; }
      .products { padding: 12px; gap: 12px; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
      .product-card { padding: 12px; border-radius: 12px; }
      .product-thumb { width: 56px; height: 56px; border-radius: 12px; margin-bottom: 8px; }
      .product-name { font-size: 12px; min-height: 2.2em; }
      .product-price { font-size: 13px; margin: 6px 0 10px; }
      .product-card .add-btn { width: 32px; height: 32px; border-radius: 8px; }
      .cart { border-radius: 12px; overflow-y: auto; }
      .cart-header { padding: 6px; }
      .cart-header h2 { font-size: 13px; }
      .price-type { margin-top: 4px; }
      .option-chips button { padding: 6px 6px; font-size: 11px; }
      .customer-select { margin-top: 4px; }
      #customerNameDisplay { font-size: 12px !important; padding: 2px 0 !important; }
      .customer-search { padding: 6px 8px; font-size: 12px; }
      .cart-items { padding: 6px 12px; min-height: 240px; }
      .cart-item { padding: 8px 0; }
      .cart-item .item-name { font-size: 12px; }
      .cart-item .item-meta { font-size: 11px; }
      .cart-item .item-total { font-size: 12px; }
      .qty-control { border-radius: 8px; padding: 2px; }
      .qty-control button { width: 24px; height: 24px; font-size: 15px; }
      .qty-control span { font-size: 13px; }
      .cart-summary { padding: 6px; }
      .summary-row { font-size: 12px; margin-bottom: 4px; }
      .summary-row.total { font-size: 16px; margin-top: 4px; padding-top: 4px; }
      .discount-control { margin: 4px 0; }
      .discount-control input { padding: 6px 8px; font-size: 12px; }
      .discount-control .header-btn { padding: 6px 8px; font-size: 12px; }
      .coupon-control { margin-top: 4px; }
      .coupon-control input { padding: 6px 8px; font-size: 12px; }
      .coupon-control .header-btn { padding: 6px 8px; font-size: 12px; }
      #couponInfo { margin-top: 4px; padding: 4px 8px; font-size: 11px; }
      #redeemBtn { margin-top: 4px; padding: 7px 4px; font-size: 12px; }
      #clockNotice { margin: 4px 0; font-size: 11px; }
      .payment-methods { margin: 4px 0; gap: 4px; }
      .method-chip { padding: 5px 8px; font-size: 11px; }
      .pay-btn { padding: 10px; font-size: 14px; }
      .pay-actions { margin-top: 4px; gap: 4px; }
      .pay-action { padding: 7px 4px; font-size: 11px; }
      .cart-actions { margin: 4px 0; }
      .copyright { padding: 8px 16px; font-size: 11px; }
    }

    /* Restaurant POS is a service counter, not a generic retail checkout.
       Keep the existing transaction markup and behaviour, but give the food
       workflow its own warm surface, aubergine structure, restrained terracotta
       action language and image-led
       product cards. */
    <?php if($mp_is_restaurant): ?>
    body.restaurant-pos {
      --mp-primary: #E85D2F;
      --mp-primary-dark: #C94721;
      --mp-bg: #F7F6F2;
      --mp-surface: #FFFFFF;
      --mp-text: #3A3733;
      --mp-muted: #77736D;
      --mp-border: #E9E4DD;
      --mp-success: #2F9A67;
      --mp-danger: #D6534F;
      --mp-warning: #D68A32;
      --mp-ink: #262522;
      background: #F7F6F2;
    }
    body.restaurant-pos .app-header { padding: 12px 22px; background: #fff; border-bottom-color: #E9E4DD; box-shadow: 0 1px 0 rgba(84,48,31,.06); }
    body.restaurant-pos .brand h1 { color: #262522; font-weight: 800; letter-spacing: -.03em; }
    body.restaurant-pos .brand .sub { color: #E85D2F; font-weight: 700; }
    body.restaurant-pos .intelligence { background: #FFFBF8; border-color: #E9E4DD; }
    body.restaurant-pos .workspace { grid-template-columns: 218px minmax(0,1fr) 416px; gap: 16px; padding: 18px; background: #F7F6F2; min-height:0; }
    body.restaurant-pos .sidebar { min-height:0; overflow-y:auto; padding-right:2px; scrollbar-width:thin; }
    body.restaurant-pos .panel, body.restaurant-pos .catalog, body.restaurant-pos .cart { border-color: #E9E4DD; border-radius: 18px; box-shadow: 0 8px 24px rgba(84,48,31,.06); }
    body.restaurant-pos .panel-title { color: #262522; letter-spacing: -.01em; }
    body.restaurant-pos .category-list li.active { background: #E85D2F; box-shadow: 0 6px 14px rgba(232,93,47,.18); }
    body.restaurant-pos .category-list li:hover { background: #FFF0E9; color: #C94721; }
    body.restaurant-pos .catalog-header { padding: 15px 16px; border-bottom-color: #E9E4DD; background: #fff; }
    body.restaurant-pos .search-bar { background: #FFFBF8; border-color: #E9E4DD; border-radius: 12px; }
    body.restaurant-pos .search-bar:focus-within { border-color: #E85D2F; box-shadow: 0 0 0 3px rgba(232,93,47,.12); }
    body.restaurant-pos .view-toggle { background: #F7F6F2; border-color: #E9E4DD; }
    body.restaurant-pos .view-toggle button.active { color: #E85D2F; }
    body.restaurant-pos .restaurant-category-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; padding:17px 18px 0; }
    body.restaurant-pos .restaurant-category-title { font-size:18px; line-height:1.2; font-weight:800; letter-spacing:-.02em; color:#262522; }
    body.restaurant-pos .restaurant-category-sub { margin-top:4px; font-size:11.5px; color:#77736D; }
    body.restaurant-pos .restaurant-category-hint { font-size:11px; color:#A09A93; white-space:nowrap; }
    body.restaurant-pos .restaurant-category-list { display:flex; gap:10px; max-height:none; overflow-x:auto; overflow-y:hidden; padding:14px 18px 16px; border-bottom:1px solid #E9E4DD; scrollbar-width:thin; }
    body.restaurant-pos .restaurant-category-list li { position:relative; flex:0 0 140px; min-height:126px; display:flex; flex-direction:column; align-items:stretch; justify-content:flex-start; gap:10px; padding:10px; border:1px solid #EEE8E1; border-radius:14px; background:#fff; color:#514B45; text-align:center; box-shadow:0 2px 7px rgba(84,48,31,.04); }
    body.restaurant-pos .restaurant-category-list li:hover { background:#FFF8F4; border-color:#F1B39B; color:#C94721; }
    body.restaurant-pos .restaurant-category-list li.active { background:#FFF0E9; border-color:#E85D2F; color:#C94721; box-shadow:0 5px 13px rgba(232,93,47,.12); }
    body.restaurant-pos .restaurant-category-list .category-visual { width:100%; height:76px; display:flex; align-items:center; justify-content:center; overflow:hidden; border-radius:11px; background:#FFF0E9; }
    body.restaurant-pos .restaurant-category-list .category-image { width:100%; height:100%; display:block; object-fit:cover; }
    body.restaurant-pos .restaurant-category-list .category-image-fallback { width:40px; height:40px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; background:#FCE5DC; color:#E85D2F; font-size:20px; }
    body.restaurant-pos .restaurant-category-list .category-name { width:100%; min-width:0; max-width:none; padding:0 2px; font-size:13px; font-weight:800; line-height:1.25; white-space:normal; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
    body.restaurant-pos .restaurant-table-picker { margin-top:10px; padding:11px 12px; border:1px solid #E9E4DD; border-radius:12px; background:#FFFBF8; }
    body.restaurant-pos .restaurant-table-label { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:7px; color:#514B45; font-size:12px; font-weight:800; }
    body.restaurant-pos .restaurant-table-label i { margin-right:5px; color:#E85D2F; }
    body.restaurant-pos .restaurant-table-label small { color:#A09A93; font-size:10.5px; font-weight:600; }
    body.restaurant-pos #tableSelect { display:block; width:100%; padding:9px 10px; border:1px solid #E9E4DD; border-radius:9px; background:#fff; color:#262522; font-size:12.5px; font-weight:600; }
    body.restaurant-pos #shippingSelect:disabled { opacity:.72; cursor:not-allowed; background:#F7F3EF!important; color:#77736D!important; }
    body.restaurant-pos #shippingPicker.shipping-locked { padding:9px 10px; border:1px solid #E9E4DD; border-radius:10px; background:#FFFBF8; }
    body.restaurant-pos .products { padding: 16px; gap: 14px; }
    body.restaurant-pos .product-card { align-items: stretch; padding: 11px; border-radius: 16px; border-color: #EEE8E1; text-align: left; }
    body.restaurant-pos .product-card:hover { border-color: #E85D2F; box-shadow: 0 10px 22px rgba(232,93,47,.12); transform: translateY(-2px); }
    body.restaurant-pos .product-thumb { width: 100%; height: 126px; margin-bottom: 10px; border-radius: 13px; background: #FFF0E9; color: #E85D2F; }
    body.restaurant-pos .product-thumb img { border-radius: 13px !important; }
    body.restaurant-pos .restaurant-product-fallback { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; background:linear-gradient(145deg,#FFF5EE 0%,#FCE5DC 100%); color:#E85D2F; }
    body.restaurant-pos .restaurant-image-fallback { position:absolute; inset:0; width:auto; height:auto; margin:0; border-radius:13px; }
    body.restaurant-pos .restaurant-product-fallback-icon { width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; border-radius:14px; background:rgba(255,255,255,.74); color:#E85D2F; font-size:21px; box-shadow:0 5px 12px rgba(84,48,31,.06); }
    body.restaurant-pos .restaurant-product-fallback-label { max-width:88%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#C94721; font-size:10px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    body.restaurant-pos .product-row .restaurant-product-fallback { width:40px; height:40px; gap:0; border-radius:10px; }
    body.restaurant-pos .product-row .restaurant-product-fallback-icon { width:auto; height:auto; background:none; box-shadow:none; font-size:16px; }
    body.restaurant-pos .product-row .restaurant-product-fallback-label { display:none; }
    body.restaurant-pos .restaurant-cashier-panel { padding:14px; border:1px solid #E9E4DD; border-radius:18px; background:#fff; box-shadow:0 8px 24px rgba(84,48,31,.06); }
    body.restaurant-pos .restaurant-cashier-head { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; margin-bottom:11px; }
    body.restaurant-pos .restaurant-cashier-kicker { color:#E85D2F; font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
    body.restaurant-pos .restaurant-cashier-title { margin-top:3px; color:#262522; font-size:14px; font-weight:800; }
    body.restaurant-pos .restaurant-cashier-live { display:inline-flex; align-items:center; gap:5px; padding:4px 7px; border-radius:999px; background:#EFF8F2; color:#2F9A67; font-size:10px; font-weight:800; }
    body.restaurant-pos .restaurant-cashier-live i { width:6px; height:6px; border-radius:50%; background:#2F9A67; }
    body.restaurant-pos .restaurant-cashier-action { width:100%; display:flex; align-items:center; gap:9px; min-height:45px; padding:8px 9px; border:1px solid #EEE8E1; border-radius:11px; background:#FFFBF8; color:#514B45; text-align:left; text-decoration:none; cursor:pointer; transition:background .15s ease,border-color .15s ease,transform .15s ease; }
    body.restaurant-pos .restaurant-cashier-action + .restaurant-cashier-action { margin-top:7px; }
    body.restaurant-pos .restaurant-cashier-action:hover { border-color:#F1B39B; background:#FFF0E9; color:#C94721; transform:translateY(-1px); }
    body.restaurant-pos .restaurant-cashier-action.primary { border-color:#F1B39B; background:#FFF0E9; color:#C94721; }
    body.restaurant-pos .restaurant-cashier-action > span:first-child { width:27px; height:27px; display:inline-flex; align-items:center; justify-content:center; flex:0 0 27px; border-radius:8px; background:#FCE5DC; color:#E85D2F; }
    body.restaurant-pos .restaurant-cashier-action b, body.restaurant-pos .restaurant-cashier-action small { display:block; }
    body.restaurant-pos .restaurant-cashier-action b { font-size:11.5px; font-weight:800; line-height:1.2; }
    body.restaurant-pos .restaurant-cashier-action small { margin-top:2px; color:#A09A93; font-size:10px; line-height:1.2; }
    body.restaurant-pos .restaurant-cashier-action em { margin-left:auto; min-width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; background:#E85D2F; color:#fff; font-size:10px; font-style:normal; font-weight:800; }
    body.restaurant-pos .product-name { color: #262522; font-weight: 800; text-align: left; }
    body.restaurant-pos .product-price { margin: 7px 0 10px; color: #E85D2F; font-weight: 800; }
    body.restaurant-pos .product-card .add-btn { width: 100%; height: 31px; border-radius: 8px; background: #FCE5DC; color: #C94721; }
    body.restaurant-pos .product-card .add-btn:hover { background: #E85D2F; color: #fff; }
    body.restaurant-pos .product-card .add-btn svg { display: none; }
    body.restaurant-pos .product-card .add-btn::after { content: 'Add to dish'; font-size: 11px; font-weight: 800; }
    body.restaurant-pos .cart { border-top: 3px solid #E85D2F; }
    body.restaurant-pos .cart-header { padding: 16px; border-bottom-color: #E9E4DD; }
    body.restaurant-pos .cart-header h2 { color: #262522; font-size: 17px; font-weight: 800; }
    body.restaurant-pos .option-chips button.active, body.restaurant-pos .method-chip.active { background: #E85D2F; border-color: #E85D2F; }
    body.restaurant-pos .cart-summary { background: #FFFBF8; border-top-color: #E9E4DD; }
    body.restaurant-pos .pay-btn { background: #E85D2F; border-radius: 11px; box-shadow: 0 7px 15px rgba(232,93,47,.18); }
    body.restaurant-pos .pay-btn:hover { background: #C94721; }
    body.restaurant-pos .header-btn.primary { background: #E85D2F; border-color: #E85D2F; }
    body.restaurant-pos .header-btn.primary:hover { background: #C94721; }
    body.restaurant-pos .copyright { background: #fff; border-top-color: #E9E4DD; }

    /* Calm restaurant palette: aubergine carries navigation and selection;
       terracotta is reserved for money/action emphasis. */
    body.restaurant-pos {
      --mp-primary: #5E4D66;
      --mp-primary-dark: #4E3F55;
      --mp-primary-08: rgba(94,77,102,.08);
      --mp-primary-10: rgba(94,77,102,.1);
      --mp-primary-15: rgba(94,77,102,.15);
      --mp-primary-20: rgba(94,77,102,.2);
      --mp-pay: #C66B4D;
      --mp-pay-dark: #A94F3A;
      --restaurant-action: #5E4D66;
      --restaurant-action-dark: #4E3F55;
      --restaurant-action-soft: #F0EAF2;
      --restaurant-accent: #C66B4D;
      --restaurant-accent-soft: #F8ECE7;
    }
    body.restaurant-pos .brand .sub { color: var(--restaurant-accent); }
    body.restaurant-pos .category-list li.active { background: var(--restaurant-action); box-shadow: 0 6px 14px rgba(94,77,102,.18); }
    body.restaurant-pos .category-list li:hover { background: var(--restaurant-action-soft); color: var(--restaurant-action-dark); }
    body.restaurant-pos .search-bar:focus-within { border-color: var(--restaurant-action); box-shadow: 0 0 0 3px rgba(94,77,102,.12); }
    body.restaurant-pos .view-toggle button.active { color: var(--restaurant-action); }
    body.restaurant-pos .restaurant-category-list li:hover { background: #F8F5F9; border-color: #CBBED0; color: var(--restaurant-action-dark); }
    body.restaurant-pos .restaurant-category-list li.active { background: var(--restaurant-action-soft); border-color: var(--restaurant-action); color: var(--restaurant-action-dark); box-shadow: 0 5px 13px rgba(94,77,102,.12); }
    body.restaurant-pos .restaurant-category-list .category-visual { background: #F6F1F7; }
    body.restaurant-pos .restaurant-category-list .category-image-fallback { background: #EAE2ED; color: var(--restaurant-action); }
    body.restaurant-pos .restaurant-table-label i { color: var(--restaurant-action); }
    body.restaurant-pos .product-card:hover { border-color: #CBBED0; box-shadow: 0 10px 22px rgba(94,77,102,.12); }
    body.restaurant-pos .product-thumb { background: #F6F1F7; color: var(--restaurant-action); }
    body.restaurant-pos .restaurant-product-fallback { background: linear-gradient(145deg,#FBF8F8 0%,#F0EAF2 100%); color: var(--restaurant-action); }
    body.restaurant-pos .restaurant-product-fallback-icon { color: var(--restaurant-action); }
    body.restaurant-pos .restaurant-product-fallback-label { color: var(--restaurant-action-dark); }
    body.restaurant-pos .restaurant-cashier-kicker { color: var(--restaurant-accent); }
    body.restaurant-pos .restaurant-cashier-action:hover { border-color: #E5C3B7; background: var(--restaurant-accent-soft); color: #A94F3A; }
    body.restaurant-pos .restaurant-cashier-action.primary { border-color: #E5C3B7; background: var(--restaurant-accent-soft); color: #A94F3A; }
    body.restaurant-pos .restaurant-cashier-action > span:first-child { background: var(--restaurant-accent-soft); color: var(--restaurant-accent); }
    body.restaurant-pos .restaurant-cashier-action em { background: var(--restaurant-action); }
    body.restaurant-pos .product-price { color: var(--restaurant-action); }
    body.restaurant-pos .product-card .add-btn { background: var(--restaurant-action-soft); color: var(--restaurant-action-dark); }
    body.restaurant-pos .product-card .add-btn:hover { background: var(--restaurant-action); color: #fff; }
    body.restaurant-pos .cart { border-top-color: var(--restaurant-action); }
    body.restaurant-pos .option-chips button.active, body.restaurant-pos .method-chip.active { background: var(--restaurant-action); border-color: var(--restaurant-action); }
    body.restaurant-pos .pay-btn { background: var(--restaurant-accent); box-shadow: 0 7px 15px rgba(198,107,77,.18); }
    body.restaurant-pos .pay-btn:hover { background: #A94F3A; }
    body.restaurant-pos .header-btn.primary { background: var(--restaurant-action); border-color: var(--restaurant-action); }
    body.restaurant-pos .header-btn.primary:hover { background: var(--restaurant-action-dark); }
    <?php endif; ?>
  </style>
</head>
<body data-pos-build="4.0.9.195" class="<?= $mp_is_pharmacy ? 'pharmacy-pos ' : ''; ?><?= $mp_is_restaurant ? 'restaurant-pos' : ''; ?>">
  <div id="posStartupError" role="alert" hidden style="position:fixed;top:8px;left:8px;right:8px;z-index:10000;padding:14px;background:#fff1f2;color:#9f1239;border:1px solid #fda4af;border-radius:8px;">
    POS could not finish loading. Reload this page. If this continues, contact your administrator.
    <button type="button" onclick="window.location.reload()" style="margin-left:12px;">Reload POS</button>
  </div>
  <script>
    window.mpPosBootReady = false;
    window.addEventListener('error', function (event) {
      if (!window.mpPosBootReady && event.message) document.getElementById('posStartupError').hidden = false;
    });
    // Also covers truncated HTML where PHP never reaches the main script.
    window.setTimeout(function () {
      if (!window.mpPosBootReady) document.getElementById('posStartupError').hidden = false;
    }, 8000);
  </script>

  <header class="app-header">
    <?php if($mp_is_clinic): ?>
    <a href="<?= base_url('dashboard') ?>" class="mp-back" title="Back to the clinic"><i class="fa fa-chevron-left"></i> Clinic</a>
    <?php endif; ?>
    <a href="<?= base_url() ?>" class="brand" style="text-decoration: none; color: inherit; cursor: pointer;">
      <h1><?= htmlspecialchars(get_store_name() ?: 'Metro Mart') ?></h1>
      <div class="sub"><?= $mp_is_pharmacy ? 'Pharmacy dispensing desk' : ($mp_is_restaurant ? 'Restaurant order desk' : 'Point of Sale'); ?></div>
    </a>
    <?php if(!empty($warehouse_options) && count($warehouse_options) > 1): ?>
    <label for="posOutletSelector" style="display:flex;align-items:center;gap:7px;margin:0 12px;font-size:12px;color:#475569;white-space:nowrap;">
      Outlet
      <select id="posOutletSelector" class="form-control input-sm" style="min-width:150px;border-radius:8px;" onchange="if(this.value){window.location.href='<?= htmlspecialchars(base_url('pos?warehouse_id='), ENT_QUOTES, 'UTF-8') ?>'+encodeURIComponent(this.value);}">
        <?php foreach($warehouse_options as $outlet): ?>
          <option value="<?= (int)$outlet['id'] ?>" <?= (int)$outlet['id'] === (int)$warehouse_id ? 'selected' : '' ?>><?= htmlspecialchars($outlet['warehouse_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <?php endif; ?>
    <div class="intelligence">
      <div class="intelligence-label">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 18h6M10 22h4M12 2v4M4.93 4.93l2.83 2.83M19.07 4.93l-2.83 2.83M6 12H2M22 12h-4M12 7a5 5 0 0 0-5 5c0 2.5 2 4 2 5h6c0-1 2-2.5 2-5a5 5 0 0 0-5-5z"/></svg>
      </div>
      <div class="marquee">
        <div class="marquee-track" id="intelligenceTrack">
          <!-- Dynamically populated with top selling products -->
        </div>
      </div>
    </div>
    <div class="header-actions">
      <button class="header-btn" onclick="openCalculator()" title="Calculator">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="8" y2="10"/><line x1="12" y1="10" x2="12" y2="10"/><line x1="16" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="8" y2="14"/><line x1="12" y1="14" x2="12" y2="14"/><line x1="16" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="8" y2="18"/><line x1="12" y1="18" x2="12" y2="18"/><line x1="16" y1="18" x2="16" y2="18"/></svg>
        <span>Calculator</span>
      </button>
      <a class="header-btn" href="<?= base_url('sales'); ?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
        <?= $mp_is_pharmacy ? 'Dispensing history' : 'Sales List'; ?>
      </a>
      <?php $CI =& get_instance(); if($CI->permissions('customers_add')): ?>
      <button class="header-btn" onclick="openAddCustomer()">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
      <?= $mp_is_pharmacy ? 'Add Patient' : 'Add Customer'; ?>
    </button>
    <?php endif; ?>
    <button class="header-btn" id="themeBtn" onclick="toggleTheme()" style="color: var(--mp-text);">
      <span id="themeIcon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></span>
      <span id="themeLabel" style="color: var(--mp-text);">Dark</span>
    </button>
    <?php if(empty($attendance_exempt)): ?>
    <button class="header-btn" id="clockBtn" onclick="toggleClock()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span id="clockBtnText">Clock In</span>
      </button>
    <?php endif; ?>
      <button class="header-btn primary" onclick="confirmResetCart()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <?= $mp_is_pharmacy ? 'New dispensing' : 'New Invoice'; ?>
      </button>
      <div class="user-menu">
        <div class="user-chip" onclick="toggleUserMenu()">
          <div class="user-avatar" style="<?= !empty($user_profile_picture) ? 'background: transparent; overflow: hidden;' : '' ?>">
            <?php if (!empty($user_profile_picture)): ?>
              <img src="<?= $user_profile_picture ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" alt="Profile" onerror="this.parentElement.innerHTML='<?= !empty($user_initial) ? $user_initial : 'U' ?>'">
            <?php else: ?>
              <?= !empty($user_initial) ? $user_initial : 'U' ?>
            <?php endif; ?>
          </div>
          <span><?= htmlspecialchars($display_name ?? 'User') ?></span>
        </div>
        <div class="user-dropdown" id="userDropdown">
          <button class="dropdown-item" onclick="window.location.href='<?= base_url() ?>'">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Home
          </button>
          <button class="dropdown-item" onclick="window.location.href='<?= base_url('users/edit/' . ($user_id ?? 0)) ?>'">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            My Profile
          </button>
          <div class="dropdown-divider"></div>
          <button class="dropdown-item" onclick="openChangePassword()">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Change Password
          </button>
          <button class="dropdown-item danger" onclick="logout()">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Log Out
          </button>
        </div>
      </div>
    </div>
  </header>

  <main class="workspace">
    <aside class="sidebar">
      <?php if(!$mp_is_restaurant): ?>
      <div class="panel">
        <div class="panel-title"><?= $mp_is_pharmacy ? 'Therapy areas' : 'Categories'; ?></div>
        <ul class="category-list" id="categoryList"></ul>
      </div>
      <?php endif; ?>
      <div class="panel">
        <div class="panel-title" style="padding-bottom:12px"><?= $mp_is_pharmacy ? 'Frequently dispensed' : 'Top Sellers This Week'; ?></div>
        <div class="insight-list" id="insightList"></div>
      </div>
      <div class="panel" style="padding:16px">
        <div class="panel-title" style="padding:0 0 10px"><?= $mp_is_pharmacy ? "Today's dispensing" : "Today's Sales Target"; ?></div>
        <?php
          $pos_initial_pct = ($daily_target > 0) ? min(100, round(($today_sales / $daily_target) * 100)) : 0;
          $pos_currency = get_store_details();
          $pos_cur_sym = $pos_currency && $pos_currency->currency_symbol ? $pos_currency->currency_symbol : '₦';
          $pos_cur_code = $pos_currency && $pos_currency->currency_code ? $pos_currency->currency_code : 'NGN';
          $pos_decimals = isset($pos_currency->decimals) ? (int)$pos_currency->decimals : 2;
        ?>
        <div class="target-amount" id="todaySalesText"><?= $pos_cur_sym . number_format((float)$today_sales, $pos_decimals, '.', ','); ?></div>
        <div class="target-meta"><span id="targetAmountLabel">of <?= $pos_cur_sym . number_format((float)$daily_target, $pos_decimals, '.', ','); ?></span><span id="targetPercent"><?= $pos_initial_pct; ?>%</span></div>
        <div class="progress-track">
          <div class="progress-fill" id="targetFill" style="width:<?= $pos_initial_pct; ?>%"></div>
        </div>
      </div>
      <div class="panel" style="padding:16px">
        <div class="panel-title" style="padding:0 0 8px"><?= $mp_is_pharmacy ? 'Dispensing record' : 'Invoice'; ?></div>
        <div style="font-size:24px;font-weight:700;color:var(--mp-primary)">#<?= $init_code ?><?= str_pad($count_id, 4, '0', STR_PAD_LEFT) ?></div>
        <div style="font-size:12px;color:var(--mp-muted);margin-bottom:12px">Next invoice number</div>
        <div id="posClock" style="font-size:22px;font-weight:700;color:var(--mp-ink);font-variant-numeric:tabular-nums">--:--:--</div>
        <div style="font-size:11px;color:var(--mp-muted)"><?= $mp_is_pharmacy ? 'Local time · dispensing desk' : 'Local Time'; ?></div>
      </div>
      <?php if($mp_is_restaurant): ?>
      <div class="restaurant-cashier-panel" aria-label="Cashier desk shortcuts">
        <div class="restaurant-cashier-head">
          <div>
            <div class="restaurant-cashier-kicker">Cashier desk</div>
            <div class="restaurant-cashier-title">Keep service moving</div>
          </div>
          <span class="restaurant-cashier-live"><i aria-hidden="true"></i> Live</span>
        </div>
        <button type="button" class="restaurant-cashier-action primary" onclick="confirmResetCart()">
          <span><i class="fa fa-plus" aria-hidden="true"></i></span>
          <span><b>Start a new order</b><small>Clear the current ticket</small></span>
        </button>
        <button type="button" class="restaurant-cashier-action" onclick="openHoldPopup()">
          <span><i class="fa fa-hand-paper-o" aria-hidden="true"></i></span>
          <span><b>Held tickets</b><small>Resume parked orders</small></span>
          <em><?= (int)($tot_count ?? 0); ?></em>
        </button>
        <button type="button" class="restaurant-cashier-action" onclick="openSalesListPopup()">
          <span><i class="fa fa-list-ul" aria-hidden="true"></i></span>
          <span><b>Sales history</b><small>Find a receipt or reprint</small></span>
        </button>
        <a class="restaurant-cashier-action" href="<?= base_url('operations/kitchen'); ?>">
          <span><i class="fa fa-cutlery" aria-hidden="true"></i></span>
          <span><b>Kitchen display</b><small>Check what is on the pass</small></span>
        </a>
      </div>
      <?php endif; ?>
    </aside>

    <section class="catalog">
      <div class="catalog-header">
        <?php if($mp_is_pharmacy): ?><div class="pharmacy-catalog-title"><strong>Medicine catalogue</strong><small>Search by name, SKU or barcode</small></div><?php endif; ?>
        <div class="search-bar">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" id="searchInput" placeholder="<?= $mp_is_pharmacy ? 'Scan barcode or search medicine...' : 'Scan barcode or search product...'; ?>">
        </div>
        <div class="view-toggle" role="group" aria-label="Product view">
          <button type="button" id="gridViewBtn" class="active" onclick="setProductView('grid')" title="Thumbnail view" aria-label="Thumbnail view">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          </button>
          <button type="button" id="listViewBtn" onclick="setProductView('list')" title="List view" aria-label="List view">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          </button>
        </div>
      </div>
      <?php if($mp_is_restaurant): ?>
      <div class="restaurant-category-head">
        <div>
          <div class="restaurant-category-title">Categories</div>
          <div class="restaurant-category-sub">Choose a section to build the order</div>
        </div>
        <span class="restaurant-category-hint">Swipe to see more</span>
      </div>
      <ul class="category-list restaurant-category-list" id="categoryList" aria-label="Menu categories"></ul>
      <?php endif; ?>
      <div class="products" id="productGrid"></div>
    </section>

    <aside class="cart">
      <div class="cart-header">
        <h2><?= $mp_is_pharmacy ? 'Dispensing queue' : 'Current Sale'; ?></h2>
        <?php if ($pos_retail_button || $pos_wholesale_button): ?>
        <div class="price-type">
          <span class="price-type-label"><?= $mp_is_pharmacy ? 'Price basis' : 'Price Type'; ?></span>
          <div class="option-chips">
            <?php if ($pos_retail_button): ?>
            <button id="retailBtn" class="<?= $default_price_type === 'retail' ? 'active' : ''; ?>" onclick="setPriceType('retail')"><?= $mp_is_pharmacy ? 'Standard' : 'Retail'; ?></button>
            <?php endif; ?>
            <?php if ($pos_wholesale_button): ?>
            <button id="wholesaleBtn" class="<?= $mp_is_pharmacy ? 'trade' : ''; ?> <?= $default_price_type === 'wholesale' ? 'active' : ''; ?>" onclick="setPriceType('wholesale')"><?= $mp_is_pharmacy ? 'Trade' : 'Wholesale'; ?></button>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
        <div class="customer-select">
          <div id="customerNameDisplay" class="customer-name-display" style="font-size:14px;font-weight:600;color:var(--mp-ink);padding:8px 0;"><?= $mp_is_pharmacy ? 'Walk-in Patient' : 'Walk-in Customer'; ?></div>
          <div id="customerBalanceInfo" style="display:none;font-size:12px;font-weight:500;padding:2px 0 6px;"></div>
          <input type="text" id="customerSearchInput" class="customer-search" placeholder="Search <?= $mp_is_pharmacy ? 'patient' : 'customer'; ?>..." autocomplete="off" oninput="filterCustomerDropdown()" onkeydown="handleCustomerSearchKey(event)" onfocus="showCustomerDropdown()" onblur="setTimeout(function(){ autoSelectCustomer(); hideCustomerDropdown(); }, 200)">
          <div id="customerDropdown" class="customer-dropdown" style="display:none">
            <?php if(!empty($customers) && is_array($customers)): ?>
              <?php foreach($customers as $c): ?>
                <?php $mp_customer_name = $c['customer_name'] ?? $c->customer_name; $mp_customer_avatar = $c['photo'] ?? ($c['customer_photo'] ?? ($c['profile_picture'] ?? '')); ?>
                <div class="customer-option" data-id="<?= $c['id'] ?? $c->id ?>" data-name="<?= htmlspecialchars($mp_customer_name) ?>" onmousedown="selectCustomer('<?= $c['id'] ?? $c->id ?>')">
                  <?php $avatar_url = $mp_customer_avatar; $avatar_name = $mp_customer_name; $avatar_size = 30; $avatar_class = 'customer-option-avatar'; $this->load->view('components/person_avatar'); ?>
                  <span class="customer-option-copy"><?= htmlspecialchars($mp_customer_name) ?><?= !empty($c['mobile']) ? ' (' . htmlspecialchars($c['mobile']) . ')' : '' ?></span>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="customer-option" data-id="" data-name="Walk-in Customer" onmousedown="selectCustomer('')">
                <?php $avatar_url = ''; $avatar_name = $mp_is_pharmacy ? 'Walk-in Patient' : 'Walk-in Customer'; $avatar_size = 30; $avatar_class = 'customer-option-avatar'; $this->load->view('components/person_avatar'); ?>
                <span class="customer-option-copy"><?= $mp_is_pharmacy ? 'Walk-in Patient' : 'Walk-in Customer'; ?></span>
              </div>
            <?php endif; ?>
          </div>
          <select id="customerSelect" onchange="handleCustomerChange()">
            <?php if(!empty($customers) && is_array($customers)): ?>
              <?php foreach($customers as $c): ?>
                <option value="<?= $c['id'] ?? $c->id ?>" <?= (!empty($walkin_customer_id) && (int)($c['id'] ?? $c->id) === (int)$walkin_customer_id) ? 'selected' : '' ?>><?= htmlspecialchars($c['customer_name'] ?? $c->customer_name) ?></option>
              <?php endforeach; ?>
            <?php else: ?>
              <option value="" selected>Walk-in Customer</option>
            <?php endif; ?>
          </select>
          <?php if($mp_is_restaurant && !empty($tables)): ?>
          <div class="restaurant-table-picker">
            <div class="restaurant-table-label"><span><i class="fa fa-cutlery" aria-hidden="true"></i> Table</span><small>Optional for dine-in</small></div>
            <select id="tableSelect" aria-label="Optional restaurant table" onchange="onTableChange()">
            <option value="0">No table / takeaway</option>
            <?php foreach($tables as $t): ?>
            <option value="<?= (int)$t->id; ?>"><?= htmlspecialchars($t->table_name); ?><?= !empty($t->zone) ? ' (' . htmlspecialchars($t->zone) . ')' : ''; ?> — <?= (int)$t->capacity; ?> seats</option>
            <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="cart-items" id="cartItems">
          <div class="cart-empty"><?= $mp_is_pharmacy ? 'Select a medicine to start a dispensing record.' : 'Tap a product to start selling.'; ?></div>
      </div>
      <?php if($mp_is_pharmacy): ?>
      <div class="pharmacy-consult-card" id="pharmacyConsultCard">
        <div class="pharmacy-consult-copy"><span class="pharmacy-consult-icon"><i class="fa fa-stethoscope"></i></span><div><strong>Pharmacist consultation</strong><small>Add assessment, dosage and counselling notes. Dosage will appear on the pharmacy invoice.</small></div></div>
        <button type="button" class="pharmacy-consult-btn" onclick="openPharmacyConsult()"><i class="fa fa-clipboard"></i> Add patient guidance</button>
        <div class="pharmacy-consult-summary" id="pharmacyConsultSummary" style="display:none"></div>
      </div>
      <?php endif; ?>
      <div class="cart-summary">
        <div class="summary-row"><span>Subtotal</span><span id="subtotal">₦0.00</span></div>
        <div class="summary-row" id="taxRow" style="display:none"><span id="taxLabel">Tax</span><span id="tax">₦0.00</span></div>
        <div class="summary-row" id="discountRow" style="display:none"><span>Discount</span><span id="discount">₦0.00</span></div>
        <div class="summary-row" id="shippingRow" style="display:none"><span id="shippingLabel">Shipping</span><span id="shipping">₦0.00</span></div>
        <div class="summary-row total"><span>Total</span><span id="grandTotal">₦0.00</span></div>
        <?php if(!empty($shipping_fees)): ?>
        <div class="discount-control" id="shippingPicker" style="margin-top:8px;">
          <select id="shippingSelect" onchange="onShippingChange()" style="flex:1; padding:10px 14px; border:1px solid var(--mp-border); border-radius:12px; font-size:14px; font-weight:500; background:var(--mp-surface); color:var(--mp-ink);">
            <option value="" data-fee="0" data-label="">No shipping</option>
            <?php foreach($shipping_fees as $sf): ?>
            <option value="<?= (int)$sf->id; ?>" data-fee="<?= htmlspecialchars($sf->fee); ?>" data-label="<?= htmlspecialchars($sf->label); ?>"><?= htmlspecialchars($sf->label); ?><?= !empty($sf->location) ? ' (' . htmlspecialchars($sf->location) . ')' : ''; ?> — <?= htmlspecialchars(store_number_format($sf->fee)); ?></option>
            <?php endforeach; ?>
          </select>
          <small id="shippingLockNote" style="display:none; flex:1 0 100%; color:var(--mp-muted); font-size:11px;">Table selected — shipping is disabled.</small>
        </div>
        <?php endif; ?>
        <div class="discount-control">
          <input type="number" id="discountInput" min="0" step="100" placeholder="Discount amount" disabled>
          <button class="header-btn" id="discountBtn" onclick="openApproval()">Apply Discount</button>
        </div>
        <div class="coupon-control" style="display:flex; gap:8px; margin-top:10px;">
          <input type="text" id="couponCodeInput" placeholder="Coupon code" style="flex:1; padding:10px 14px; border:1px solid var(--mp-border); border-radius:12px; font-size:14px; font-weight:500; background:var(--mp-surface); color:var(--mp-ink);" onkeyup="if(event.key==='Enter') applyCouponCode()">
          <button class="header-btn" id="couponBtn" onclick="applyCouponCode()" disabled>Apply</button>
        </div>
        <div id="couponInfo" style="display:none; font-size:12px; color:var(--mp-muted); margin-top:6px; padding:6px 10px; background:rgba(5,150,105,.06); border-radius:8px;">
          <span id="couponInfoText"></span>
          <a style="float:right; cursor:pointer; color:var(--mp-danger);" onclick="clearCouponCode()">Remove</a>
        </div>
        <button class="pay-action" id="redeemBtn" style="width: 100%; margin-top: 10px;" onclick="redeemPoints()" disabled>Redeem</button>
        <div class="clock-notice" id="clockNotice" style="display:none">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Clock in before accepting payment.
        </div>
        <div class="cart-actions" style="display: flex; gap: 8px; margin: 10px 0;">
          <button type="button" class="pay-action" style="flex: 1;" onclick="holdCurrentCart()" id="holdCartBtn">Hold</button>
          <button type="button" class="pay-action" style="flex: 1;" onclick="openHoldPopup()">Hold List</button>
        </div>
        <button class="pay-btn" id="payBtn" disabled onclick="openPaymentPopup('pay')">Pay</button>
        <div class="pay-actions">
          <button class="pay-action" id="splitBtn" onclick="openPaymentPopup('split')" disabled>Split Pay</button>
          <button class="pay-action" id="planBtn" onclick="openPaymentPopup('plan')" disabled>PayPlan</button>
        </div>
      </div>
    </aside>
  </main>

  <?php if($mp_is_pharmacy): ?>
  <div class="modal-backdrop pharmacy-consult-modal" id="pharmacyConsultModal">
    <div class="modal" style="max-width:560px;">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px;">
        <div><div style="font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#0f766e;">Pharmacist note</div><h3 style="margin:5px 0 0;">Capture patient guidance</h3><p style="margin:4px 0 0;color:var(--mp-muted);font-size:12px;">Use the microphone controls for hands-free entry.</p></div>
        <button type="button" class="btn-secondary" onclick="closePharmacyConsult()" style="padding:6px 10px;">×</button>
      </div>
      <div class="form-group"><label for="pharmacyAssessment">Short assessment</label><div style="display:flex;gap:8px;align-items:flex-start;"><textarea id="pharmacyAssessment" rows="3" placeholder="Symptoms, duration, relevant context..." style="flex:1;width:100%;padding:10px 12px;border:1px solid var(--mp-border);border-radius:10px;resize:vertical;"></textarea><button type="button" class="pharmacy-voice-btn" data-voice-target="pharmacyAssessment" aria-label="Dictate assessment" title="Dictate assessment"><i class="fa fa-microphone" aria-hidden="true"></i></button></div></div>
      <div class="form-group"><label for="pharmacyDosage">Dosage / directions</label><div style="display:flex;gap:8px;align-items:flex-start;"><textarea id="pharmacyDosage" rows="3" placeholder="Example: Take 1 tablet after food, twice daily for 5 days..." style="flex:1;width:100%;padding:10px 12px;border:1px solid var(--mp-border);border-radius:10px;resize:vertical;"></textarea><button type="button" class="pharmacy-voice-btn" data-voice-target="pharmacyDosage" aria-label="Dictate dosage" title="Dictate dosage"><i class="fa fa-microphone" aria-hidden="true"></i></button></div></div>
      <div class="form-group"><label for="pharmacyCounselling">Counselling / cautions</label><div style="display:flex;gap:8px;align-items:flex-start;"><textarea id="pharmacyCounselling" rows="2" placeholder="Food, driving, storage or follow-up advice..." style="flex:1;width:100%;padding:10px 12px;border:1px solid var(--mp-border);border-radius:10px;resize:vertical;"></textarea><button type="button" class="pharmacy-voice-btn" data-voice-target="pharmacyCounselling" aria-label="Dictate counselling" title="Dictate counselling"><i class="fa fa-microphone" aria-hidden="true"></i></button></div></div>
      <div class="form-group"><label for="pharmacyAllergies">Allergies / cautions noted</label><input id="pharmacyAllergies" type="text" placeholder="Optional" style="width:100%;padding:10px 12px;border:1px solid var(--mp-border);border-radius:10px;"></div>
      <div class="modal-actions"><button type="button" class="btn-secondary" onclick="closePharmacyConsult()">Cancel</button><button type="button" class="btn-primary" onclick="savePharmacyConsult()">Add to invoice</button></div>
    </div>
  </div>
  <?php endif; ?>

  <footer class="copyright">
    &copy; <?= date('Y'); ?> <?= htmlspecialchars(get_store_name() ?: 'MartPoint') ?>. Powered by MartPoint.
  </footer>

  <div class="toast-container" id="toastContainer"></div>

  <div class="modal-backdrop" id="approvalModal">
    <div class="modal">
      <div class="modal-icon">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <h3>Manager Approval</h3>
      <p>Discount or price changes require manager approval.</p>
      
      <!-- Step 1: Enter discount amount -->
      <div id="approvalStep1">
        <div class="form-group">
          <label for="approvalDiscountInput">Discount amount (₦)</label>
          <input type="number" id="approvalDiscountInput" min="0" step="100" placeholder="0.00">
        </div>
        <div class="modal-actions">
          <button class="btn-secondary" onclick="closeApproval()">Cancel</button>
          <button class="btn-primary" onclick="proceedToPin()">Next</button>
        </div>
      </div>
      
      <!-- Step 2: Enter PIN -->
      <div id="approvalStep2" style="display:none;">
        <div class="form-group">
          <label for="pinInput">Manager PIN</label>
          <input type="password" id="pinInput" maxlength="6" placeholder="Enter 4-6 digit PIN" autofocus>
        </div>
        <div style="font-size:13px; color:var(--mp-muted); margin-bottom:16px;">
          Discount: <strong id="approvalAmountDisplay">₦0.00</strong>
        </div>
        <div class="modal-actions">
          <button class="btn-secondary" onclick="backToAmount()">Back</button>
          <button class="btn-primary" onclick="approveDiscount()">Approve</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal-backdrop" id="addCustomerModal">
    <div class="modal">
      <h3>Add Customer</h3>
      <p>Create a new customer and use this sale.</p>
      <div class="form-group">
        <label for="customerName">Customer name</label>
        <input type="text" id="customerName" placeholder="e.g. Chukwudi Nnamdi">
      </div>
      <div class="form-group">
        <label for="customerPhone">Phone number</label>
        <input type="tel" id="customerPhone" placeholder="e.g. 08012345678">
      </div>
      <div class="modal-actions">
        <button class="btn-secondary" onclick="closeAddCustomer()">Cancel</button>
        <button class="btn-primary" onclick="saveCustomer()">Save Customer</button>
      </div>
    </div>
  </div>

  <div class="modal-backdrop" id="changePasswordModal">
    <div class="modal">
      <h3>Change Password</h3>
      <p>Update your cashier account password.</p>
      <div class="form-group">
        <label for="currentPin">Current PIN / Password</label>
        <input type="password" id="currentPin" placeholder="Enter current PIN">
      </div>
      <div class="form-group">
        <label for="newPin">New PIN / Password</label>
        <input type="password" id="newPin" placeholder="Enter new PIN">
      </div>
      <div class="form-group">
        <label for="confirmPin">Confirm New PIN</label>
        <input type="password" id="confirmPin" placeholder="Confirm new PIN">
      </div>
      <div class="modal-actions">
        <button class="btn-secondary" onclick="closeChangePassword()">Cancel</button>
        <button class="btn-primary" onclick="savePassword()">Update</button>
      </div>
    </div>
  </div>

  <div class="modal-backdrop" id="clockModal">
    <div class="modal" style="max-width: 520px;">
      <h3 id="clockTitle">Clock In</h3>
      <p id="clockStatusText">Position your face and click Capture to clock in.</p>
      <div style="position: relative; width: 100%; border-radius: 14px; overflow: hidden; background: #000; margin: 16px 0;">
        <video id="clockVideo" autoplay playsinline style="width: 100%; display: block; transform: scaleX(-1);"></video>
        <canvas id="clockCanvas" style="display: none;"></canvas>
      </div>
      <div style="text-align:center; margin: 4px 0 12px;">
        <button type="button" id="clockPinToggle" class="btn btn-link" onclick="showClockPinFallback()" style="font-size: 13px; color: var(--mp-primary); background: none; border: none; cursor: pointer; padding: 0;">No camera? Use password or PIN</button>
      </div>
      <div id="clockNoticeBox" style="display:none; padding: 10px; border-radius: 10px; background: var(--mp-bg); color: var(--mp-muted); font-size: 13px; margin-bottom: 12px;"></div>
      <div id="clockPinBox" style="display:none; padding: 12px; border-radius: 10px; background: var(--mp-bg); margin-bottom: 12px;">
        <div class="form-group" style="margin: 0;">
          <label for="clockPin" style="font-size: 13px; color: var(--mp-muted);">Account password or approval PIN</label>
          <input type="password" id="clockPin" class="form-control" placeholder="Enter password or PIN" style="margin-top: 6px; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 8px; font-size: 14px; width: 100%;">
        </div>
      </div>
      <div class="modal-actions">
        <button class="btn-secondary" onclick="closeClockModal()">Cancel</button>
        <button class="btn-primary" id="clockCaptureBtn" onclick="captureClockFace()">Capture</button>
        <button class="btn-primary" id="clockConfirmBtn" style="display:none;" onclick="confirmClockAction()">Confirm</button>
      </div>
    </div>
  </div>

  <div class="modal-backdrop" id="redeemModal">
    <div class="modal" style="max-width: 520px;">
      <h3>Redeem</h3>
      <p id="redeemCustomerName">Select a customer to see redeemable options.</p>
      <div id="redeemOptions" style="max-height: 360px; overflow-y: auto; margin: 16px 0;"></div>
      <div class="modal-actions">
        <button class="btn-secondary" onclick="closeRedeemModal()">Cancel</button>
      </div>
    </div>
  </div>

  <div class="modal-backdrop" id="holdModal">
    <div class="modal" style="max-width: 420px;">
      <h3>Hold Sale</h3>
      <p>Save this cart to hold for later.</p>
      <div class="form-group" style="margin: 16px 0;">
        <label for="holdReferenceInput">Reference (optional)</label>
        <input type="text" id="holdReferenceInput" class="customer-search" placeholder="Enter hold reference number" onkeydown="if(event.key==='Enter') submitHold()">
      </div>
      <div class="modal-actions">
        <button class="btn-secondary" onclick="closeHoldModal()">Cancel</button>
        <button class="btn-primary" id="holdSubmitBtn" onclick="submitHold()">Hold</button>
      </div>
    </div>
  </div>

  <div class="modal-backdrop" id="confirmModal">
    <div class="modal" style="max-width: 420px;">
      <h3 id="confirmTitle">Confirm</h3>
      <p id="confirmMessage">Are you sure?</p>
      <div class="modal-actions">
        <button class="btn-secondary" onclick="closeConfirmModal()">Cancel</button>
        <button class="btn-primary" id="confirmActionBtn" onclick="runConfirmAction()">Confirm</button>
      </div>
    </div>
  </div>

  <div class="modal-backdrop" id="postSaleModal">
    <div class="modal" style="max-width: 600px; max-height: 85vh; display: flex; flex-direction: column;">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <h3 style="margin: 0;">Receipt</h3>
        <button onclick="closePostSaleModal()" style="background: none; border: none; cursor: pointer; padding: 4px; color: var(--mp-muted);">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div style="flex: 1; overflow-y: auto; background: white; border-radius: 12px; padding: 20px; border: 1px solid var(--mp-border); display: flex; justify-content: center;" id="receiptContent">
        <!-- Receipt will be loaded here -->
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px;">
        <button class="btn-secondary" onclick="printAndDone()" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; border-radius: 12px; font-weight: 600;">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          Print & Done
        </button>
        <button class="btn-secondary" id="shareBtn" onclick="shareInvoice()" style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; border-radius: 12px; font-weight: 600;">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
          Share
        </button>
      </div>
    </div>
  </div>

  <div class="modal-backdrop" id="alertModal">
    <div class="modal" style="max-width: 420px;">
      <h3 id="alertTitle">Notice</h3>
      <p id="alertMessage">Something happened.</p>
      <div class="modal-actions">
        <button class="btn-primary" onclick="closeAlertModal()" style="margin-left: auto;">OK</button>
      </div>
    </div>
  </div>

  <div class="modal-backdrop" id="calculatorModal">
    <div class="modal" style="max-width:360px;">
      <h3>Calculator</h3>
      <p>Quick calculations for sales and retail work.</p>
      <input id="calculatorDisplay" class="calculator-display" type="text" value="0" readonly aria-label="Calculator display">
      <div class="calculator-grid">
        <button class="calculator-key operator" onclick="calculatorClear()">C</button><button class="calculator-key operator" onclick="calculatorBackspace()">⌫</button><button class="calculator-key operator" onclick="calculatorInput('%')">%</button><button class="calculator-key operator" onclick="calculatorInput('/')">÷</button>
        <button class="calculator-key" onclick="calculatorInput('7')">7</button><button class="calculator-key" onclick="calculatorInput('8')">8</button><button class="calculator-key" onclick="calculatorInput('9')">9</button><button class="calculator-key operator" onclick="calculatorInput('*')">×</button>
        <button class="calculator-key" onclick="calculatorInput('4')">4</button><button class="calculator-key" onclick="calculatorInput('5')">5</button><button class="calculator-key" onclick="calculatorInput('6')">6</button><button class="calculator-key operator" onclick="calculatorInput('-')">−</button>
        <button class="calculator-key" onclick="calculatorInput('1')">1</button><button class="calculator-key" onclick="calculatorInput('2')">2</button><button class="calculator-key" onclick="calculatorInput('3')">3</button><button class="calculator-key operator" onclick="calculatorInput('+')">+</button>
        <button class="calculator-key" onclick="calculatorInput('0')">0</button><button class="calculator-key" onclick="calculatorInput('.')">.</button><button class="calculator-key equals" style="grid-column:span 2;" onclick="calculatorEquals()">=</button>
      </div>
      <div class="modal-actions"><button class="btn-secondary" onclick="closeCalculator()">Close</button></div>
    </div>
  </div>

  <div class="modal-backdrop" id="unitPickerModal">
    <div class="modal" style="max-width: 420px;">
      <h3 id="unitPickerTitle">Select Unit</h3>
      <div id="unitPickerBody" style="display: flex; flex-direction: column; gap: 8px; margin: 16px 0;"></div>
      <div class="modal-actions">
        <button class="btn-secondary" onclick="closeUnitPickerModal()" style="margin-left: auto;">Cancel</button>
      </div>
    </div>
  </div>

  <div class="modal-backdrop" id="salesListPopup">
    <div class="modal" style="max-width: 720px; max-height: 80vh; display: flex; flex-direction: column;">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
        <h3 style="margin: 0;">Today's Sales</h3>
        <button onclick="window.closeSalesListPopup()" style="background: none; border: none; cursor: pointer; padding: 4px; color: var(--mp-muted);">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div style="flex: 1; overflow-y: auto; border: 1px solid var(--mp-border); border-radius: 12px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
          <thead style="background: var(--mp-bg); position: sticky; top: 0;">
            <tr>
              <th style="padding: 12px; text-align: left; font-weight: 600; border-bottom: 1px solid var(--mp-border);">Invoice</th>
              <th style="padding: 12px; text-align: left; font-weight: 600; border-bottom: 1px solid var(--mp-border);">Customer</th>
              <th style="padding: 12px; text-align: right; font-weight: 600; border-bottom: 1px solid var(--mp-border);">Amount</th>
              <th style="padding: 12px; text-align: center; font-weight: 600; border-bottom: 1px solid var(--mp-border);">Time</th>
            </tr>
          </thead>
          <tbody id="salesListBody">
            <tr><td colspan="4" style="padding: 24px; text-align: center; color: var(--mp-muted);">Loading sales...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="<?= rtrim($theme_link, '/') ?>/js/mp-offline-db.js?v=4"></script>
  <script>
    const products = <?= $pos_json($products ?? []) ?>;
    const baseUrl = <?= $pos_json(rtrim(base_url(), '/')) ?>;
    const POS_DECIMALS = <?= (int)decimals() ?>;
    const WAREHOUSE_ID = <?= $pos_json((string)$warehouse_id) ?>;
    const WALKIN_CUSTOMER_ID = <?= $pos_json((string)$walkin_customer_id) ?>;
    const csrfToken = <?= $pos_json($this->security->get_csrf_hash()) ?>;
    const stopSellingExpired = <?= (int)($stop_selling_expired ?? 1) ?>;
    const showSaleStockLabels = <?= !empty($show_sale_stock_labels) ? 'true' : 'false' ?>;
    const medicalNoteId = <?= (int)($medical_note_id ?? 0) ?>;
    const medicalNoteCustomerId = <?= ($medical_note_customer_id ?? 'null') ?>;
    const isPharmacyPos = <?= $mp_is_pharmacy ? 'true' : 'false' ?>;
    const isRestaurantPos = <?= $mp_is_restaurant ? 'true' : 'false' ?>;

    function pharmacyFieldValue(id) {
      const el = document.getElementById(id);
      return el ? el.value.trim() : '';
    }
    function buildPharmacySalesNote() {
      if (!isPharmacyPos) return '';
      const blocks = [];
      const assessment = pharmacyFieldValue('pharmacyAssessment');
      const dosage = pharmacyFieldValue('pharmacyDosage');
      const counselling = pharmacyFieldValue('pharmacyCounselling');
      const allergies = pharmacyFieldValue('pharmacyAllergies');
      if (assessment) blocks.push('Assessment: ' + assessment);
      if (dosage) blocks.push('Dosage / directions: ' + dosage);
      if (counselling) blocks.push('Counselling: ' + counselling);
      if (allergies) blocks.push('Allergies / cautions: ' + allergies);
      return blocks.join('\n');
    }
    window.openPharmacyConsult = function() {
      const modal = document.getElementById('pharmacyConsultModal');
      if (modal) modal.classList.add('active');
    };
    window.closePharmacyConsult = function() {
      const modal = document.getElementById('pharmacyConsultModal');
      if (modal) modal.classList.remove('active');
    };
    window.savePharmacyConsult = function() {
      const note = buildPharmacySalesNote();
      const summary = document.getElementById('pharmacyConsultSummary');
      if (summary) { summary.textContent = note || 'No guidance added yet.'; summary.style.display = 'block'; }
      closePharmacyConsult();
    };
    (function initPharmacyVoice() {
      if (!isPharmacyPos) return;
      const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      document.querySelectorAll('.pharmacy-voice-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          if (!Recognition) { openAlertModal('Voice unavailable', 'This browser does not support voice dictation. You can type the note instead.'); return; }
          if (btn._recognition) { btn._recognition.stop(); return; }
          const target = document.getElementById(btn.getAttribute('data-voice-target'));
          if (!target) return;
          const recognition = new Recognition();
          btn._recognition = recognition;
          recognition.lang = 'en-NG'; recognition.interimResults = false; recognition.continuous = false;
          btn.classList.add('listening'); btn.setAttribute('aria-label', 'Stop dictation'); btn.setAttribute('title', 'Stop dictation'); btn.innerHTML = '<i class="fa fa-stop" aria-hidden="true"></i>';
          recognition.onresult = function(event) { const text = event.results[0][0].transcript.trim(); target.value = (target.value ? target.value + ' ' : '') + text; };
          recognition.onerror = function() { showToast('Voice note could not be captured. You can type it instead.', 'warning'); };
          recognition.onend = function() { btn._recognition = null; btn.classList.remove('listening'); btn.setAttribute('aria-label', 'Dictate'); btn.setAttribute('title', 'Dictate'); btn.innerHTML = '<i class="fa fa-microphone" aria-hidden="true"></i>'; };
          recognition.start();
        });
      });
    })();

    const topSelling = <?= $pos_json($top_selling ?? []) ?>;

    const POINTS_VALUE = 500;
    const POINTS_COST = 200;
    const dailyTarget = <?= (float)($daily_target ?? 50000) ?>;
    let todaySales = <?= (float)($today_sales ?? 0) ?>;
    let selectedCategory = 'All';
    let priceType = <?= $pos_json($default_price_type) ?>;
    let searchQuery = '';
    let productView = (() => { try { return localStorage.getItem('posProductView') === 'list' ? 'list' : 'grid'; } catch (e) { return 'grid'; } })();
    let discountAmount = 0;
    let shippingFee = 0;
    let shippingFeeId = 0;
    let shippingFeeLabel = '';
    let couponCode = '';
    let couponValue = 0;
    let couponType = '';
    let couponDiscountAmt = 0;
    let couponAppliesTo = 'all';
    let couponCategoryId = 0;
    let couponBrandId = 0;
    let couponPromotionId = 0;
    let couponEligibleItemIds = [];
    let loyaltyPoints = 850;
    const attendanceExempt = <?= $pos_json(!empty($attendance_exempt)) ?>;
    const clockInRequired = <?= $pos_json(!empty($clock_in_required)) ?>;
    let clockedIn = attendanceExempt ? false : <?= $pos_json((bool)(!$needs_clock_in)) ?>;
    let clockedAt = <?= $pos_json($clock_in_time) ?>;
    let clockInTime = <?= $pos_json($clock_in_time) ?>;
    let clockImage = null;
    let clockPinMode = false;
    let clockStream = null;
    const cart = [];
    let cartSubtotal = 0;
    let cartTax = 0;
    const customers = <?= $pos_json($customers ?? []) ?>;
    const customerRedeemables = <?= $pos_json($customer_redeemables ?? (object)[]) ?>;
    const customerNameMap = <?= $pos_json(array_column($customers ?? [], 'customer_name', 'id')) ?>;
    const loyaltySettings = <?= $pos_json($loyalty_settings ?? (object)['redemption_rate' => 0]) ?>;
    let redeemedItems = [];
    let currentLoadedHoldId = null;
    let holdLoadedAt = null;
    const HOLD_TIMEOUT_MINUTES = 30; // Configurable via store settings

    function formatMoney(n) {
      const d = typeof window.POS_DECIMALS !== 'undefined' ? window.POS_DECIMALS : 2;
      const value = parseFloat(n || 0);
      const parts = value.toFixed(d).split('.');
      const whole = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      return '₦' + (d > 0 ? whole + '.' + parts[1] : whole);
    }

    function updateClock() {
      const now = new Date();
      const h = String(now.getHours()).padStart(2, '0');
      const m = String(now.getMinutes()).padStart(2, '0');
      const s = String(now.getSeconds()).padStart(2, '0');
      const el = document.getElementById('posClock');
      if (el) el.textContent = h + ':' + m + ':' + s;
    }

    function isNew(createdAt) {
      // createdAt field is not available from db_items
      // Disable "New" badge for now
      return false;
    }

    function getPrice(product, unit = null) {
      if (unit) {
        if (priceType === 'wholesale' && unit.wholesale_price > 0) return unit.wholesale_price;
        return unit.selling_price > 0 ? unit.selling_price : (unit.wholesale_price || 0);
      }
      if (product && product.selling_units && product.selling_units.length > 0) {
        const defaultUnit = product.selling_units.find(u => u.is_default) || product.selling_units[0];
        return getPrice(product, defaultUnit);
      }
      if (product) return priceType === 'wholesale' ? product.wholesale : product.price;
      return 0;
    }

    function getWeightPricing(item, qty) {
      const enabled = !!(item && item.weight_pricing_enabled);
      const base = parseFloat(item && item.weight_pricing_base_kg) || 0;
      const step = parseFloat(item && item.weight_pricing_increment_kg) || 0;
      const charge = parseFloat(item && item.weight_pricing_increment_charge) || 0;
      const q = parseFloat(qty) || 0;
      if (!enabled || q <= 0 || base <= 0 || step <= 0 || charge < 0) return { total: (parseFloat(item.unitPrice) || 0) * q, unit: parseFloat(item.unitPrice) || 0 };
      let total;
      const wholeUnits = Math.floor((q / base) + 0.00000001);
      const remainder = Math.max(0, q - (wholeUnits * base));
      const lines = (item.weight_lines !== undefined) ? Math.max(0, parseInt(item.weight_lines, 10) || 0) : (remainder > 0 ? Math.ceil(Math.round((remainder / step) * 100000000) / 100000000) : 0);
      const enteredWholeUnits = (item.weight_base_qty !== undefined) ? Math.max(0, parseFloat(item.weight_base_qty) || 0) : wholeUnits;
      total = (wholeUnits * (parseFloat(item.unitPrice) || 0)) + (lines * charge);
      if (item.weight_base_qty !== undefined) total = (enteredWholeUnits * (parseFloat(item.unitPrice) || 0)) + (lines * charge);
      return { total: total, unit: total / q };
    }

    function openUnitPicker(product, onSelect) {
      const title = document.getElementById('unitPickerTitle');
      const body = document.getElementById('unitPickerBody');
      if (!title || !body) return;
      title.textContent = product.name || 'Select Unit';
      body.innerHTML = '';
      (product.selling_units || []).forEach(unit => {
        const unitPrice = getPrice(product, unit);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-secondary';
        btn.style = 'width:100%; padding:14px; text-align:left; display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; border-radius:12px;';
        btn.innerHTML = '<span><strong>' + (unit.unit_shortcode || unit.unit_name || 'Unit') + '</strong> <small style="color:var(--mp-muted);">x ' + (unit.conversion_factor || 1) + '</small></span><span style="font-weight:600;">' + formatMoney(unitPrice) + '</span>';
        btn.onclick = () => { closeUnitPickerModal(); onSelect(unit); };
        body.appendChild(btn);
      });
      document.getElementById('unitPickerModal').classList.add('active');
    }

    function closeUnitPickerModal() {
      document.getElementById('unitPickerModal').classList.remove('active');
    }

    const toastIcons = {
      success: '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
      danger: '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
      warning: '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
    };

    function showToast(title, message, type) {
      const container = document.getElementById('toastContainer');
      const toast = document.createElement('div');
      toast.className = 'toast ' + (type || 'success');
      toast.innerHTML = '<div class="toast-icon">' + (toastIcons[type] || toastIcons.success) + '</div>' +
        '<div class="toast-content"><div class="toast-title">' + title + '</div><div class="toast-message">' + message + '</div></div>' +
        '<button class="toast-close" onclick="this.parentElement.remove()">×</button>';
      container.appendChild(toast);
      requestAnimationFrame(() => toast.classList.add('show'));
      setTimeout(() => {
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 300);
      }, 4500);
    }

    /* Plain-English translation of raw HTTP responses so cashiers never see
       status codes, HTML fragments or browser jargon. */
    function posHttpMessage(status, body, redirected, fallback) {
      body = body || '';
      if (status === 403 && /action you have requested is not allowed/i.test(body)) {
        return 'Your security check has expired. Please reload the page and try again.';
      }
      if (status === 403) {
        return 'The server blocked this request. Please reload the page and try again; if it keeps failing, contact your administrator.';
      }
      if (status === 404) {
        return 'That action was not found. Please reload the page and try again.';
      }
      if (status === 401 || redirected || (/name=["']?(user|email|pass|login)/i.test(body) && /<form/i.test(body))) {
        return 'Your session has expired. Please sign in again.';
      }
      if (status >= 500) {
        return 'The server hit a problem and could not finish. Please try again.';
      }
      return fallback || 'The server returned an unexpected response. Please try again.';
    }

    /* Turn a caught fetch()/JS error into a plain-English sentence. */
    function posErrorText(err, fallback) {
      const m = (err && err.message) ? String(err.message).trim() : '';
      const technical = /failed to fetch|load failed|networkerror|network request failed|typeerror|syntaxerror|unexpected token|unexpected end|not valid json|invalid (server )?response|invalid json|^(server )?error:?\s*\d+$|aborterror|operation was aborted|timed out/i;
      if (m && !technical.test(m)) return m;
      if (/failed to fetch|load failed|networkerror|network request failed|aborterror|operation was aborted|timed out/i.test(m)) {
        return 'Could not reach the server. Check your internet connection and try again.';
      }
      return fallback || 'Something went wrong. Please try again.';
    }

    /* fetch() for JSON endpoints — resolves to parsed JSON or throws a
       plain-English Error. */
    function posFetchJson(url, options) {
      options = options || {};
      options.headers = options.headers || {};
      options.headers['X-Requested-With'] = 'XMLHttpRequest';
      if (!('Accept' in options.headers)) options.headers['Accept'] = 'application/json';
      return fetch(url, options).then(r => r.text().then(body => {
        let res = null;
        try { res = JSON.parse(body); } catch (e) { res = null; }
        if (res && typeof res === 'object') return res;
        throw new Error(posHttpMessage(r.status, body, r.redirected));
      }), () => {
        throw new Error('Could not reach the server. Check your internet connection and try again.');
      });
    }

    /* fetch() for text/HTML endpoints — same plain-English errors. */
    function posFetchText(url, options) {
      options = options || {};
      options.headers = options.headers || {};
      options.headers['X-Requested-With'] = 'XMLHttpRequest';
      return fetch(url, options).then(r => r.text().then(body => {
        if (r.status >= 400 || (r.redirected && /login|signin|auth/i.test(r.url || ''))) {
          throw new Error(posHttpMessage(r.status, body, r.redirected));
        }
        return body;
      }), () => {
        throw new Error('Could not reach the server. Check your internet connection and try again.');
      });
    }

    function init() {
      renderCategories();
      renderInsights();
      renderIntelligenceBar();
      renderProducts();
      document.getElementById('gridViewBtn').classList.toggle('active', productView === 'grid');
      document.getElementById('listViewBtn').classList.toggle('active', productView === 'list');
      updateCart();
      updateTarget();
      updateClock();
      updateClockBtn();
      syncCustomerSearchFromSelect();
      checkClockStatus();
      setInterval(updateClock, 1000);

      document.getElementById('searchInput').addEventListener('input', (e) => {
        searchQuery = e.target.value.trim().toLowerCase();
        // Searching should look across the whole catalogue, not just the category that happens to be active
        if (searchQuery && selectedCategory !== 'All') {
          selectedCategory = 'All';
          renderCategories();
        }
        renderProducts();
      });
      document.getElementById('searchInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          addByBarcode(document.getElementById('searchInput').value);
        }
      });
    }

    function getCategories() {
      const map = {};
      products.forEach(p => {
        const name = p.category || 'Uncategorized';
        if (!map[name]) map[name] = { name: name, image: p.category_image || '' };
        if (!isRestaurantPos) map[name].count = (map[name].count || 0) + 1;
        if (!map[name].image && p.category_image) map[name].image = p.category_image;
      });
      const all = { name: 'All', image: '' };
      if (!isRestaurantPos) all.count = products.length;
      return [all, ...Object.values(map)];
    }

    function categoryIcon(name) {
      const key = String(name || '').toLowerCase();
      if (key === 'all') return 'fa-th-large';
      if (/dessert|cake|sweet|pastr/.test(key)) return 'fa-birthday-cake';
      if (/coffee|tea|drink|beverage/.test(key)) return 'fa-coffee';
      if (/juice|smooth|fruit/.test(key)) return 'fa-glass';
      if (/snack|starter|side|appet/.test(key)) return 'fa-cutlery';
      if (/rice|pasta|noodle|grain/.test(key)) return 'fa-spoon';
      if (/burger|sandwich|wrap/.test(key)) return 'fa-bars';
      if (/pizza/.test(key)) return 'fa-circle-o';
      return 'fa-cutlery';
    }

    function restaurantProductFallbackContents(p) {
      const category = escapeHtml(p && p.category ? p.category : 'Menu item');
      const icon = categoryIcon(p && p.category ? p.category : '');
      return `<span class="restaurant-product-fallback-icon"><i class="fa ${icon}" aria-hidden="true"></i></span><span class="restaurant-product-fallback-label">${category}</span>`;
    }

    function restaurantProductFallback(p, compact) {
      if (compact) {
        return `<div class="restaurant-product-fallback row-thumb">${restaurantProductFallbackContents(p)}</div>`;
      }
      return `<div class="product-thumb restaurant-product-fallback">${restaurantProductFallbackContents(p)}</div>`;
    }

    function categoryImageUrl(path) {
      const value = String(path || '').trim();
      if (!value) return '';
      if (/^(https?:|data:|\/)/i.test(value)) return value;
      return baseUrl + '/' + value.replace(/^\/+/, '');
    }

    function renderCategories() {
      const list = document.getElementById('categoryList');
      list.innerHTML = getCategories().map((c, i) => {
        const image = categoryImageUrl(c.image);
        const visual = image
          ? `<img class="category-image" src="${escapeHtml(image)}" alt="" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"><span class="category-image-fallback" style="display:none"><i class="fa ${categoryIcon(c.name)}" aria-hidden="true"></i></span>`
          : `<span class="category-image-fallback"><i class="fa ${categoryIcon(c.name)}" aria-hidden="true"></i></span>`;
        const restaurantVisual = isRestaurantPos ? `<span class="category-visual">${visual}</span>` : '';
        const retailCount = isRestaurantPos ? '' : `<span class="count">${c.count}</span>`;
        return `
        <li class="${c.name === selectedCategory ? 'active' : ''}" data-cat-index="${i}">
          ${restaurantVisual}
          <span class="category-name">${escapeHtml(c.name)}</span>
          ${retailCount}
        </li>
      `;}).join('');
      const cats = getCategories();
      list.querySelectorAll('li').forEach(li => {
        li.addEventListener('click', () => setCategory(cats[parseInt(li.dataset.catIndex, 10)].name));
      });
    }

    function escapeHtml(s) {
      return String(s == null ? '' : s).replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
    }

    function renderInsights() {
      const wrap = document.getElementById('insightList');
      if (!topSelling || topSelling.length === 0) {
        wrap.innerHTML = '<div style="padding:16px; text-align:center; color:var(--mp-muted); font-size:13px;">No sales data yet</div>';
        return;
      }
      const max = Math.max(...topSelling.map(t => t.sold));
      wrap.innerHTML = topSelling.map((t, i) => `
        <div class="insight-item">
          <div class="insight-top">
            <span class="rank">#${i + 1}</span>
            <span class="name">${escapeHtml(t.name)}</span>
            <span class="sold">${t.sold} ${isPharmacyPos ? 'dispensed' : 'sold'}</span>
          </div>
          <div class="insight-bar">
            <div class="fill" style="width:${(t.sold / max) * 100}%"></div>
          </div>
        </div>
      `).join('');
    }

    function renderIntelligenceBar() {
      const track = document.getElementById('intelligenceTrack');
      if (!topSelling || topSelling.length === 0) {
        track.innerHTML = '<span class="marquee-items"><span class="marquee-item" style="color:var(--mp-muted);">No sales data available yet</span></span>';
        return;
      }
      
      // Create marquee items from top selling products
      const items = topSelling.map((t, i) => `
        <span class="marquee-item trend">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
          ${escapeHtml(t.name)} — ${t.sold} ${isPharmacyPos ? 'dispensed' : 'sold'} this week
        </span>
      `).join('');
      
      // Duplicate for seamless scrolling
      track.innerHTML = `<span class="marquee-items">${items}</span><span class="marquee-items">${items}</span>`;
    }

    function setCategory(name) {
      selectedCategory = name;
      renderCategories();
      renderProducts();
    }

    function setPriceType(type) {
      priceType = type;
      const retailBtn = document.getElementById('retailBtn');
      const wholesaleBtn = document.getElementById('wholesaleBtn');
      if (retailBtn) retailBtn.className = type === 'retail' ? 'active' : '';
      if (wholesaleBtn) wholesaleBtn.className = type === 'wholesale' ? 'active' : '';
      cart.forEach(item => { item.unitPrice = getPrice(item, item.unit); });
      updateCart();
      renderProducts();
      showToast(isPharmacyPos ? 'Price basis changed' : 'Price type changed', 'Switched to ' + (isPharmacyPos ? (type === 'wholesale' ? 'trade' : 'standard') : type) + ' prices', 'success');
    }

    function getFilteredProducts() {
      const q = searchQuery;
      return products.filter(p => {
        const matchesCategory = selectedCategory === 'All' || (p.category || 'Uncategorized') === selectedCategory;
        const matchesSearch = !q ||
          (p.name && p.name.toLowerCase().includes(q)) ||
          (p.sku && p.sku.toLowerCase().includes(q)) ||
          (p.item_code && p.item_code.toLowerCase().includes(q)) ||
          (p.selling_units || []).some(u => u.barcode && u.barcode.toLowerCase().includes(q));
        return matchesCategory && matchesSearch;
      });
    }

    function renderProducts() {
      const grid = document.getElementById('productGrid');
      const filtered = getFilteredProducts();

      grid.classList.toggle('list-mode', productView === 'list');
      if (!filtered.length) {
        grid.innerHTML = '<div style="grid-column:1/-1;padding:24px;color:var(--mp-muted);">' +
          <?= $pos_json(!empty($catalogue_load_failed) ? 'The product catalogue could not load. Contact your administrator to check the update and database.' : 'No products match. Clear the search or choose another category.') ?> + '</div>';
        return;
      }

      if (productView === 'list') {
        grid.innerHTML = filtered.map(p => {
          const displayPrice = getPrice(p);
          const stockChip = !showSaleStockLabels ? '' : (p.outOfStock
            ? '<span class="stock-chip out">Out of stock</span>'
            : (p.alert_qty > 0 && p.stock <= p.alert_qty
              ? `<span class="stock-chip low">Low · ${p.stock}${p.unit ? ' ' + p.unit : ''}</span>`
              : `<span class="stock-chip in">${p.stock}${p.unit ? ' ' + p.unit : ''} in stock</span>`));
          const rowThumb = p.image
            ? `<div class="row-thumb"><img src="${baseUrl}/${p.image}" alt="" onerror="this.remove();"></div>`
            : (isPharmacyPos
              ? '<div class="row-thumb pharmacy-medicine-thumb"><i class="fa fa-medkit" aria-hidden="true"></i></div>'
              : (isRestaurantPos
                ? restaurantProductFallback(p, true)
                : `<div class="row-thumb"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg></div>`));
          const meta = [p.category, p.unit, p.sku].filter(Boolean).join(' · ');
          const saleTag = showSaleStockLabels && priceType === 'retail' && p.onSale ? '<span class="pos-sale-badge">Sale</span>' : '';
          const oldPrice = showSaleStockLabels && priceType === 'retail' && p.onSale ? '<span class="pos-old-price">' + formatMoney(Number(p.original_price) || 0) + '</span>' : '';
          return `
          <div class="product-row${p.outOfStock ? ' out' : ''}" onclick="${p.outOfStock ? '' : 'addToCart(' + p.id + ')'}">
            ${rowThumb}
            <div class="row-info">
              <div class="row-name">${escapeHtml(p.name)}</div>
              <div class="row-meta">${escapeHtml(meta)}</div>
            </div>
            ${stockChip}
            <div class="row-price">${saleTag}${formatMoney(displayPrice)}${oldPrice}</div>
            <button class="add-btn" ${p.outOfStock ? 'disabled' : ''} onclick="event.stopPropagation(); addToCart(${p.id})">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
          </div>
        `;}).join('');
        return;
      }

      grid.innerHTML = filtered.map(p => {
        const displayPrice = getPrice(p);
        const newTag = isNew(p.createdAt) ? '<div class="new-tag">New</div>' : '';
        const saleTag = showSaleStockLabels && priceType === 'retail' && p.onSale ? '<div class="sale-tag">Sale</div>' : '';
        const oldPrice = showSaleStockLabels && priceType === 'retail' && p.onSale ? '<span class="pos-old-price">' + formatMoney(Number(p.original_price) || 0) + '</span>' : '';
        const wholesaleTag = priceType === 'wholesale' ? '<div class="price-tag">' + (isPharmacyPos ? 'Trade' : 'WS') + '</div>' : '';
        const outOfStockOverlay = showSaleStockLabels && p.outOfStock ? '<div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);border-radius:14px;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-weight:700;font-size:12px;">OUT OF STOCK</span></div>' : '';
        const thumb = p.image
          ? (isRestaurantPos
            ? `<div class="product-thumb" style="padding:0;position:relative;"><img src="${baseUrl}/${p.image}" style="width:100%;height:100%;object-fit:cover;border-radius:14px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"><span class="restaurant-product-fallback restaurant-image-fallback" style="display:none;">${restaurantProductFallbackContents(p)}</span>${outOfStockOverlay}</div>`
            : `<div class="product-thumb" style="padding:0;position:relative;"><img src="${baseUrl}/${p.image}" style="width:100%;height:100%;object-fit:cover;border-radius:14px;" onerror="this.remove();">${outOfStockOverlay}</div>`)
          : (isPharmacyPos
            ? '<div class="product-thumb pharmacy-medicine-thumb"><i class="fa fa-medkit" aria-hidden="true"></i></div>'
            : (isRestaurantPos
              ? restaurantProductFallback(p, false)
              : `<div class="product-thumb"><svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg></div>`));
        const cardClass = p.outOfStock ? 'product-card' : 'product-card';
        const cardStyle = p.outOfStock ? 'opacity: 0.6; cursor: not-allowed;' : '';
        return `
        <div class="${cardClass}" style="${cardStyle}" onclick="${p.outOfStock ? '' : 'addToCart(' + p.id + ')'}">
          ${saleTag}
          ${newTag}
          ${wholesaleTag}
          ${thumb}
          <div class="product-name">${escapeHtml(p.name)}</div>
          <div class="product-price">${formatMoney(displayPrice)}${oldPrice}</div>
          <button class="add-btn" ${p.outOfStock ? 'disabled' : ''} onclick="event.stopPropagation(); addToCart(${p.id})">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
        </div>
      `;}).join('');
    }

    function setProductView(mode) {
      productView = mode === 'list' ? 'list' : 'grid';
      try { localStorage.setItem('posProductView', productView); } catch (e) {}
      document.getElementById('gridViewBtn').classList.toggle('active', productView === 'grid');
      document.getElementById('listViewBtn').classList.toggle('active', productView === 'list');
      renderProducts();
    }

    function addToCart(id, selectedUnit = null) {
      const product = products.find(p => p.id === id);
      if (!product) return;
      if (product.outOfStock) {
        showToast('Out of stock', product.name + ' is not available', 'warning');
        return;
      }
      if (stopSellingExpired && product.expired) {
        showToast('Expired item', product.name + ' has expired' + (product.expire_date ? ' (' + product.expire_date + ')' : '') + ' and cannot be sold.', 'warning');
        return;
      }

      // Show unit picker if product has multiple selling units and no unit selected yet
      if (!selectedUnit && product.selling_units && product.selling_units.length > 1) {
        openUnitPicker(product, (unit) => addToCart(id, unit));
        return;
      }
      if (!selectedUnit && product.selling_units && product.selling_units.length === 1) {
        selectedUnit = product.selling_units[0];
      }

      const unitId = selectedUnit ? (selectedUnit.unit_id || '') : '';
      const unitName = selectedUnit ? (selectedUnit.unit_shortcode || selectedUnit.unit_name || '') : (product.unit || '');
      const conversionFactor = selectedUnit ? (parseFloat(selectedUnit.conversion_factor) || 1) : 1;

      const existing = cart.find(c => c.id === id && c.unit_id === unitId);
      const nextBaseQty = ((existing ? existing.qty : 0) + 1) * conversionFactor;
      if (nextBaseQty > product.stock) {
        showToast('Not enough stock', product.name + ': 1 ' + (unitName || 'unit') + ' uses ' + conversionFactor + ' stock units — only ' + product.stock + ' available', 'warning');
        return;
      }

      const unitPrice = getPrice(product, selectedUnit);
      const item = existing;
      if (item) {
        if (item.weight_pricing_enabled) {
          item.weight_base_qty = (parseFloat(item.weight_base_qty) || 0) + 1;
          const baseKg = parseFloat(item.weight_pricing_base_kg) || 1;
          const lineStep = parseFloat(item.weight_pricing_increment_kg) || 0.05;
          item.qty = (item.weight_base_qty * baseKg) + ((parseInt(item.weight_lines, 10) || 0) * lineStep);
        } else {
          item.qty++;
        }
      } else {
        cart.push({
          ...product,
          unit: selectedUnit,
          unit_id: unitId,
          unit_name: unitName,
          conversion_factor: conversionFactor,
          unitPrice: unitPrice,
          qty: 1,
          weight_base_qty: product.weight_pricing_enabled ? 1 : undefined,
          weight_lines: product.weight_pricing_enabled ? 0 : undefined,
          tax_value: product.tax_value,
          tax_id: product.tax_id,
          tax_type: product.tax_type
        });
      }
      showToast('Added to cart', product.name, 'success');
      updateCart();
    }

    function addByBarcode(q) {
      q = (q || '').trim();
      if (!q) return;
      const qLower = q.toLowerCase();
      let product = products.find(p => {
        if (p.sku && p.sku.toLowerCase() === qLower) return true;
        if (p.item_code && p.item_code.toLowerCase() === qLower) return true;
        return (p.selling_units || []).some(u => u.barcode && u.barcode.toLowerCase() === qLower);
      });
      // No exact barcode/SKU hit: if the typed search narrowed the grid to one product, add that one
      if (!product) {
        const visible = getFilteredProducts().filter(p => !p.outOfStock);
        if (visible.length === 1) product = visible[0];
      }
      if (!product) {
        const count = getFilteredProducts().length;
        showToast(count > 1 ? 'Refine your search' : 'Item not found', count > 1 ? count + ' products match "' + q + '" — pick one from the grid' : q, 'warning');
        return;
      }
      if (stopSellingExpired && product.expired) {
        showToast('Expired item', product.name + ' has expired' + (product.expire_date ? ' (' + product.expire_date + ')' : '') + ' and cannot be sold.', 'warning');
        return;
      }
      // If a selling-unit barcode was scanned, use that unit directly
      let selectedUnit = (product.selling_units || []).find(u => u.barcode && u.barcode.toLowerCase() === qLower);
      if (!selectedUnit && product.selling_units && product.selling_units.length > 0) {
        selectedUnit = product.selling_units.find(u => u.is_default) || product.selling_units[0];
      }
      addToCart(product.id, selectedUnit);
      const input = document.getElementById('searchInput');
      if (input) input.value = '';
      searchQuery = '';
      renderProducts();
    }

    function changeQty(index, delta) {
      const item = cart[index];
      if (!item) return;
      const product = products.find(p => p.id === item.id);
      const conversion = parseFloat(item.conversion_factor || 1);
      const weightStep = item.weight_pricing_enabled ? (parseFloat(item.weight_pricing_increment_kg) || 0.05) : 1;
      const nextQty = item.weight_pricing_enabled ? ((parseFloat(item.weight_base_qty || 0) + delta) * (parseFloat(item.weight_pricing_base_kg) || 1)) + ((parseInt(item.weight_lines, 10) || 0) * weightStep) : item.qty + (delta * weightStep);
      if (product && product.stock < nextQty * conversion) {
        showToast('Not enough stock', product.name + ': 1 ' + (item.unit_name || 'unit') + ' uses ' + conversion + ' stock units — only ' + product.stock + ' available', 'warning');
        return;
      }
      if (item.weight_pricing_enabled) item.weight_base_qty = Math.max(0, (parseFloat(item.weight_base_qty || 0) + delta));
      item.qty = nextQty;
      if (item.qty <= 0) {
        cart.splice(index, 1);
      }
      updateCart();
    }

    function setWeightQty(index, value) {
      const item = cart[index];
      if (!item) return;
      const qty = parseFloat(value);
      const product = products.find(p => p.id === item.id);
      const conversion = parseFloat(item.conversion_factor || 1);
      if (!isFinite(qty) || qty <= 0) { cart.splice(index, 1); updateCart(); return; }
      if (product && product.stock < qty * conversion) {
        showToast('Not enough stock', product.name + ': only ' + product.stock + ' available', 'warning');
        updateCart(); return;
      }
      item.qty = qty;
      updateCart();
    }

    function setWeightParts(index, part, value) {
      const item = cart[index];
      if (!item) return;
      if (part === 'kg') item.weight_base_qty = Math.max(0, parseFloat(value) || 0);
      if (part === 'lines') item.weight_lines = Math.max(0, parseInt(value, 10) || 0);
      const baseKg = parseFloat(item.weight_pricing_base_kg) || 1;
      const step = parseFloat(item.weight_pricing_increment_kg) || 0.05;
      item.qty = (item.weight_base_qty * baseKg) + (item.weight_lines * step);
      const product = products.find(p => p.id === item.id);
      if (product && product.stock < item.qty * (parseFloat(item.conversion_factor || 1))) {
        showToast('Not enough stock', product.name + ': only ' + product.stock + ' available', 'warning');
        updateCart(); return;
      }
      updateCart();
    }

    function changeWeightPart(index, part, delta) {
      const item = cart[index];
      if (!item) return;
      const current = part === 'kg' ? (parseFloat(item.weight_base_qty) || 0) : (parseInt(item.weight_lines, 10) || 0);
      setWeightParts(index, part, current + delta);
    }

    function removeItem(index) {
      if (index > -1) cart.splice(index, 1);
      updateCart();
    }

    function confirmResetCart() {
      if (cart.length === 0) {
        resetCart();
        return;
      }
      openConfirmModal('Start new invoice?', 'Current cart will be cleared.', () => {
        resetCart();
      });
    }

    function resetCart() {
      // A new invoice gets a fresh retry key. Keep the current key alive until
      // the post-sale receipt is dismissed so an accidental second payment
      // click resolves to the original sale instead of creating another one.
      window.__mpRestaurantOrderKey = null;
      // Auto-wipe hold if it was loaded but not processed
      if (currentLoadedHoldId && holdLoadedAt) {
        const timeElapsed = Date.now() - holdLoadedAt;
        const timeoutMs = HOLD_TIMEOUT_MINUTES * 60 * 1000;
        // If hold was loaded within the timeout window, auto-delete it
        if (timeElapsed < timeoutMs) {
          fetch(baseUrl + '/pos/hold_invoice_delete/' + currentLoadedHoldId)
            .catch(err => console.error('Auto-wipe hold error:', err));
        }
      }
      currentLoadedHoldId = null;
      holdLoadedAt = null;
      
      cart.length = 0;
      discountAmount = 0;
      shippingFee = 0;
      shippingFeeId = 0;
      shippingFeeLabel = '';
      const shipSel = document.getElementById('shippingSelect');
      if (shipSel) shipSel.value = '';
      const tableSelect = document.getElementById('tableSelect');
      if (tableSelect) tableSelect.value = '0';
      syncTableShippingState();
      document.getElementById('discountInput').value = '';
      document.getElementById('discountInput').disabled = true;
      clearCouponCode();
      priceType = <?= $pos_json($default_price_type) ?>;
      const retailBtn = document.getElementById('retailBtn');
      const wholesaleBtn = document.getElementById('wholesaleBtn');
      if (retailBtn) retailBtn.className = priceType === 'retail' ? 'active' : '';
      if (wholesaleBtn) wholesaleBtn.className = priceType === 'wholesale' ? 'active' : '';
      updateCart();
      showToast(isPharmacyPos ? 'New dispensing record' : 'New invoice', isPharmacyPos ? 'Queue cleared for the next patient' : 'Cart cleared for the next customer', 'success');
    }

    function updateClockBtn() {
      const btn = document.getElementById('clockBtn');
      const text = document.getElementById('clockBtnText');
      const dot = document.getElementById('clockDot');
      if (!btn || !text) return;
      if (dot) dot.remove();
      if (clockedIn && clockedAt) {
        btn.classList.add('success');
        text.textContent = 'Clocked In ' + clockedAt;
        btn.insertAdjacentHTML('afterbegin', '<span class="status-dot" id="clockDot"></span>');
      } else {
        btn.classList.remove('success');
        text.textContent = 'Clock In';
      }
    }

    function toggleClock() {
      const modal = document.getElementById('clockModal');
      const title = document.getElementById('clockTitle');
      const statusText = document.getElementById('clockStatusText');
      const confirmBtn = document.getElementById('clockConfirmBtn');
      const captureBtn = document.getElementById('clockCaptureBtn');
      const notice = document.getElementById('clockNoticeBox');
      const video = document.getElementById('clockVideo');
      title.textContent = clockedIn ? 'Clock Out' : 'Clock In';
      statusText.textContent = clockedIn ? 'Position your face and click Capture to clock out.' : 'Position your face and click Capture to clock in.';
      confirmBtn.style.display = 'none';
      confirmBtn.disabled = false;
      captureBtn.style.display = '';
      notice.style.display = 'none';
      notice.textContent = '';
      document.getElementById('clockPinBox').style.display = 'none';
      document.getElementById('clockPinToggle').style.display = '';
      document.getElementById('clockPin').value = '';
      video.style.display = 'block';
      clockImage = null;
      clockPinMode = false;
      closeAllPopups();
      modal.classList.add('active');
      startClockCamera();
    }

    function startClockCamera() {
      const video = document.getElementById('clockVideo');
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showToast('No camera', 'No camera detected. Use the PIN fallback.', 'warning');
        showClockPinFallback();
        return;
      }
      navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
        .then(stream => {
          clockStream = stream;
          video.srcObject = stream;
        })
        .catch(err => {
          const name = err && err.name;
          const msg = name === 'NotAllowedError' ? 'Camera access was blocked. Please allow camera access in your browser, or use the PIN below.'
            : name === 'NotFoundError' ? 'No camera was found on this device. Use the PIN below instead.'
            : 'Could not access the camera. Use the PIN below instead.';
          showToast('Camera error', msg, 'warning');
          showClockPinFallback();
        });
    }

    function stopClockCamera() {
      if (clockStream) {
        clockStream.getTracks().forEach(track => track.stop());
        clockStream = null;
      }
      const video = document.getElementById('clockVideo');
      video.srcObject = null;
    }

    function showClockPinFallback() {
      const video = document.getElementById('clockVideo');
      stopClockCamera();
      video.style.display = 'none';
      document.getElementById('clockPinBox').style.display = 'block';
      document.getElementById('clockPinToggle').style.display = 'none';
      document.getElementById('clockCaptureBtn').style.display = 'none';
      document.getElementById('clockConfirmBtn').style.display = '';
      document.getElementById('clockConfirmBtn').disabled = false;
      document.getElementById('clockPin').value = '';
      document.getElementById('clockPin').focus();
      document.getElementById('clockStatusText').textContent = clockedIn ? 'Enter your account password or PIN to clock out.' : 'Enter your account password or PIN to clock in.';
      clockImage = null;
      clockPinMode = true;
    }

    function closeClockModal() {
      document.getElementById('clockModal').classList.remove('active');
      stopClockCamera();
      clockImage = null;
    }

    function captureClockFace() {
      const video = document.getElementById('clockVideo');
      const canvas = document.getElementById('clockCanvas');
      const confirmBtn = document.getElementById('clockConfirmBtn');
      const captureBtn = document.getElementById('clockCaptureBtn');
      const statusText = document.getElementById('clockStatusText');
      const notice = document.getElementById('clockNoticeBox');
      if (!video.videoWidth) {
        showToast('Camera not ready', 'Wait for the camera to start', 'danger');
        return;
      }
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      const ctx = canvas.getContext('2d');
      ctx.translate(canvas.width, 0);
      ctx.scale(-1, 1);
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      clockImage = canvas.toDataURL('image/png');
      stopClockCamera();
      notice.style.display = 'block';
      notice.textContent = 'Face captured. Click Confirm to proceed.';
      captureBtn.style.display = 'none';
      confirmBtn.style.display = '';
      statusText.textContent = clockedIn ? 'Ready to clock out.' : 'Ready to clock in.';
    }

    function confirmClockAction() {
      const pin = clockPinMode ? document.getElementById('clockPin').value.trim() : '';
      if (!clockImage && !pin) {
        showToast('No confirmation', 'Capture your face or enter your PIN to proceed', 'danger');
        return;
      }
      if (clockPinMode && pin.length < 4) {
        showToast('PIN too short', 'Enter at least 4 characters', 'warning');
        return;
      }
      const notice = document.getElementById('clockNoticeBox');
      const confirmBtn = document.getElementById('clockConfirmBtn');
      notice.textContent = 'Getting location...';
      confirmBtn.disabled = true;

      const doPost = (lat, lng) => {
        // Send as form-encoded to match jQuery $.post() behavior
        const params = new URLSearchParams();
        if (clockImage) params.append('face_image', clockImage);
        if (pin) params.append('pin', pin);
        if (lat != null) params.append('lat', lat);
        if (lng != null) params.append('lng', lng);
        params.append('csrf_test_name', csrfToken);
        
        const url = clockedIn ? baseUrl + '/attendance/clock_out' : baseUrl + '/attendance/clock_in';
        const wasClockedIn = clockedIn;
        
        posFetchJson(url, {
          method: 'POST',
          body: params,
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
          }
        })
          .then(res => {
            confirmBtn.disabled = false;
              if (res.status === 'success') {
                // Immediately flip local UI — don't wait for async status check
                if (wasClockedIn) {
                  clockedIn = false;
                  clockedAt = '';
                  clockInTime = '';
                  showToast('Clocked out', res.message || 'Shift ended', 'warning');
                } else {
                  clockedIn = true;
                  const now = new Date();
                  clockedAt = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                  clockInTime = res.clock_in || clockedAt;
                  showToast('Clocked in', res.message || 'Shift started at ' + clockedAt, 'success');
                }
                updateClockBtn();
                updateCart();
                closeClockModal();
                // Verify with server in background
                setTimeout(checkClockStatus, 500);
              } else {
                notice.textContent = res.message || 'The clock action failed. Please try again.';
                showToast('Clock failed', res.message || 'The clock action failed. Please try again.', 'danger');
              }
          })
          .catch(err => {
            confirmBtn.disabled = false;
            const msg = posErrorText(err, 'The clock action failed. Please try again.');
            notice.textContent = msg;
            showToast('Clock failed', msg, 'danger');
          });
      };
      
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          pos => doPost(pos.coords.latitude, pos.coords.longitude),
          () => doPost(null, null),
          { timeout: 5000 }
        );
      } else {
        doPost(null, null);
      }
    }

    // Check clock status from server (matches old desktop POS behavior)
    function checkClockStatus() {
      fetch(baseUrl + '/attendance/status_ajax', {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
      })
        .then(r => r.text())
        .then(text => {
          try {
            const res = JSON.parse(text);
            clockedIn = !!res.clocked_in;
            if (res.clock_in_time) {
              clockedAt = res.clock_in_time.substring(0, 5);
              clockInTime = clockedAt;
            } else {
              clockedAt = '';
              clockInTime = '';
            }
            updateClockBtn();
            updateCart();
          } catch (e) {
            console.error('Clock status parse error:', e);
          }
        })
        .catch(err => console.error('Clock status error:', err));
    }

    function openApproval() {
      document.getElementById('approvalModal').classList.add('active');
      document.getElementById('approvalStep1').style.display = 'block';
      document.getElementById('approvalStep2').style.display = 'none';
      document.getElementById('pinInput').value = '';
      document.getElementById('approvalDiscountInput').value = discountAmount || '';
      document.getElementById('approvalDiscountInput').focus();
    }

    function closeApproval() {
      document.getElementById('approvalModal').classList.remove('active');
      document.getElementById('approvalStep1').style.display = 'block';
      document.getElementById('approvalStep2').style.display = 'none';
    }

    function proceedToPin() {
      const amount = parseFloat(document.getElementById('approvalDiscountInput').value) || 0;
      if (amount <= 0) {
        showToast('Invalid amount', 'Enter a discount amount greater than 0', 'warning');
        return;
      }
      const maxDiscount = cartSubtotal + cartTax;
      if (amount > maxDiscount) {
        showToast('Amount too high', 'Discount cannot exceed ₦' + formatMoney(maxDiscount), 'warning');
        return;
      }
      document.getElementById('approvalAmountDisplay').textContent = formatMoney(amount);
      document.getElementById('approvalStep1').style.display = 'none';
      document.getElementById('approvalStep2').style.display = 'block';
      document.getElementById('pinInput').focus();
    }

    function backToAmount() {
      document.getElementById('approvalStep1').style.display = 'block';
      document.getElementById('approvalStep2').style.display = 'none';
      document.getElementById('approvalDiscountInput').focus();
    }

    function approveDiscount() {
      const pin = document.getElementById('pinInput').value.trim();
      const amount = parseFloat(document.getElementById('approvalDiscountInput').value) || 0;
      if (pin.length < 4) {
        showToast('Approval failed', 'Enter a valid manager PIN (4-6 digits)', 'danger');
        return;
      }
      discountAmount = amount;
      document.getElementById('discountInput').value = amount;
      document.getElementById('discountInput').disabled = false;
      closeApproval();
      updateCart();
      showToast('Discount approved', formatMoney(amount) + ' applied', 'success');
    }

    function applyDiscountFromInput() {
      discountAmount = Math.max(0, parseFloat(document.getElementById('discountInput').value) || 0);
      updateCart();
    }

    document.getElementById('discountInput').addEventListener('change', applyDiscountFromInput);

    function updateCart() {
      const itemsContainer = document.getElementById('cartItems');
      if (cart.length === 0) {
        itemsContainer.innerHTML = '<div class="cart-empty">' + (isPharmacyPos ? 'Select a medicine to start a dispensing record.' : 'Tap a product to start selling.') + '</div>';
      } else {
        itemsContainer.innerHTML = cart.map((item, index) => {
          const taxValue = (item.tax_value || 0) / 100;
          const taxType = item.tax_type || 'Exclusive';
          const weightPricing = getWeightPricing(item, item.qty);
          let displayPrice = weightPricing.unit;
          let displayTotal = weightPricing.total;

          // For inclusive tax, show the price without tax
          if (taxType === 'Inclusive' && taxValue > 0) {
            // Remove VAT from the complete stepped-weight price. Removing it
            // from the base price alone makes the item row disagree with the
            // subtotal once additional weight charges are applied.
            displayPrice = weightPricing.unit / (1 + taxValue);
            displayTotal = weightPricing.total / (1 + taxValue);
          }

          const unitLabel = item.unit_name ? ' • ' + item.unit_name : '';

          return `
          <div class="cart-item${item.weight_pricing_enabled ? ' weight-cart-item' : ''}">
            <div class="item-info">
              <div class="item-name">${escapeHtml(item.name)}</div>
              <div class="item-meta">${formatMoney(displayPrice)} each • ${priceType === 'wholesale' ? (isPharmacyPos ? 'Trade' : 'Wholesale') : (isPharmacyPos ? 'Standard' : 'Retail')}${unitLabel}</div>
            </div>
            <div class="qty-control">
              ${item.weight_pricing_enabled ? '<span class="weight-entry"><span class="weight-part"><span class="weight-label">KG</span><input type="number" min="0" step="1" value="' + (item.weight_base_qty ?? 0) + '" onchange="setWeightParts(' + index + ', \'kg\', this.value)"></span><span class="weight-part"><span class="weight-label">Lines</span><input type="number" min="0" step="1" value="' + (item.weight_lines ?? 0) + '" onchange="setWeightParts(' + index + ', \'lines\', this.value)"></span><span class="weight-total">= ' + Number(item.qty || 0).toFixed(2) + ' kg</span></span>' : '<button onclick="changeQty(' + index + ', -1)">-</button><span>' + item.qty + '</span><button onclick="changeQty(' + index + ', 1)">+</button>'}
            </div>
            <div class="item-total">${formatMoney(displayTotal)}</div>
            <button class="remove-btn" onclick="removeItem(${index})">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        `;
        }).join('');
      }

      let subtotal = 0;
      let tax = 0;
      
      // Calculate subtotal and tax based on each item's tax type
      cart.forEach(i => {
        const lineTotal = getWeightPricing(i, i.qty).total;
        const taxValue = (i.tax_value || 0) / 100;
        const taxType = i.tax_type || 'Exclusive';
        
        if (taxType === 'Inclusive') {
          // For inclusive tax: price includes tax, so we need to extract it
          const taxAmount = lineTotal - (lineTotal / (1 + taxValue));
          const lineSubtotal = lineTotal - taxAmount;
          subtotal += lineSubtotal;
          tax += taxAmount;
        } else {
          // For exclusive tax: add tax on top of price
          subtotal += lineTotal;
          tax += lineTotal * taxValue;
        }
      });

      // Expose totals for other functions and hide tax row when there's no tax
      cartSubtotal = subtotal;
      cartTax = tax;

      // Manual shipping row
      const shippingRow = document.getElementById('shippingRow');
      if (shippingRow) {
        if (shippingFee > 0) {
          shippingRow.style.display = 'flex';
          document.getElementById('shippingLabel').textContent = 'Shipping' + (shippingFeeLabel ? ' (' + shippingFeeLabel + ')' : '');
          document.getElementById('shipping').textContent = formatMoney(shippingFee);
        } else {
          shippingRow.style.display = 'none';
        }
      }
      const taxRow = document.getElementById('taxRow');
      if (taxRow) {
        taxRow.style.display = (tax > 0) ? 'flex' : 'none';
        const taxLabel = document.getElementById('taxLabel');
        if (taxLabel && subtotal > 0) {
          const avgTaxRate = (tax / subtotal * 100).toFixed(2);
          taxLabel.textContent = (tax > 0) ? 'Tax (' + avgTaxRate + '%)' : 'Tax';
        }
      }
      
      discountAmount = Math.min(discountAmount, subtotal + tax);
      couponDiscountAmt = computeCouponDiscount(subtotal);
      const totalDiscount = discountAmount + couponDiscountAmt;
      const total = Math.max(0, subtotal + tax - totalDiscount + shippingFee);

      document.getElementById('subtotal').textContent = formatMoney(subtotal);
      document.getElementById('tax').textContent = formatMoney(tax);
      const discountRow = document.getElementById('discountRow');
      const discountEl = document.getElementById('discount');
      if (totalDiscount > 0) {
        discountRow.style.display = 'flex';
        discountEl.textContent = '-' + formatMoney(totalDiscount);
      } else {
        discountRow.style.display = 'none';
      }
      document.getElementById('grandTotal').textContent = formatMoney(total);
      const canPay = cart.length > 0 && (!clockInRequired || clockedIn);
      document.getElementById('payBtn').disabled = !canPay;
      document.getElementById('payBtn').textContent = cart.length === 0 ? 'Pay' : `Pay ${formatMoney(total)}`;
      document.getElementById('splitBtn').disabled = !canPay;
      document.getElementById('planBtn').disabled = !canPay;
      
      // Disable discount button and input until cart has items
      const discountBtn = document.getElementById('discountBtn');
      const discountInput = document.getElementById('discountInput');
      const hasItems = cart.length > 0;
      discountBtn.disabled = !hasItems;
      discountInput.disabled = !hasItems;
      discountBtn.style.opacity = hasItems ? '1' : '0.5';
      discountBtn.style.cursor = hasItems ? 'pointer' : 'not-allowed';

      // Enable coupon button when cart has items and a real customer is selected
      const couponBtn = document.getElementById('couponBtn');
      const couponEnabled = hasItems && (parseInt(document.getElementById('customerSelect').value) || 0) !== 0
        && (parseInt(document.getElementById('customerSelect').value) || 0) != WALKIN_CUSTOMER_ID;
      couponBtn.disabled = !couponEnabled;
      couponBtn.style.opacity = couponEnabled ? '1' : '0.5';
      couponBtn.style.cursor = couponEnabled ? 'pointer' : 'not-allowed';
      
      const customerId = document.getElementById('customerSelect').value;
      const isWalkin = (parseInt(customerId) || 0) == WALKIN_CUSTOMER_ID;
      const canRedeem = canPay && customerId && !isWalkin && hasRedeemables(customerId);
      const redeemBtn = document.getElementById('redeemBtn');
      redeemBtn.disabled = !canRedeem;
      redeemBtn.textContent = 'Redeem';
      document.getElementById('clockNotice').style.display = cart.length > 0 && clockInRequired && !clockedIn ? 'flex' : 'none';
    }

    function syncTableShippingState() {
      const tableSelect = document.getElementById('tableSelect');
      const shippingSelect = document.getElementById('shippingSelect');
      const picker = document.getElementById('shippingPicker');
      const lockNote = document.getElementById('shippingLockNote');
      const dineIn = !!tableSelect && (parseInt(tableSelect.value, 10) || 0) > 0;

      if (dineIn) {
        shippingFee = 0;
        shippingFeeId = 0;
        shippingFeeLabel = '';
        if (shippingSelect) {
          shippingSelect.value = '';
          shippingSelect.disabled = true;
          shippingSelect.title = 'Shipping is unavailable for a dine-in table.';
        }
        if (picker) picker.classList.add('shipping-locked');
        if (lockNote) lockNote.style.display = 'block';
      } else {
        if (shippingSelect) {
          shippingSelect.disabled = false;
          shippingSelect.title = '';
        }
        if (picker) picker.classList.remove('shipping-locked');
        if (lockNote) lockNote.style.display = 'none';
      }
    }

    function onTableChange() {
      syncTableShippingState();
      updateCart();
    }

    // Manual shipping picker — fee is verified server-side on save
    function onShippingChange() {
      const sel = document.getElementById('shippingSelect');
      const tableSelect = document.getElementById('tableSelect');
      if (tableSelect && (parseInt(tableSelect.value, 10) || 0) > 0) {
        syncTableShippingState();
        updateCart();
        return;
      }
      const opt = sel.options[sel.selectedIndex];
      shippingFeeId = parseInt(sel.value) || 0;
      shippingFee = parseFloat(opt ? opt.dataset.fee : 0) || 0;
      shippingFeeLabel = opt ? (opt.dataset.label || '') : '';
      updateCart();
    }

    function splitPay() {
      const totalText = document.getElementById('grandTotal').textContent;
      showToast('Split payment', 'Split ' + totalText + ' across two methods', 'success');
    }
    function payPlan() {
      const totalText = document.getElementById('grandTotal').textContent;
      showToast('Payment plan', 'Installment plan created for ' + totalText, 'success');
    }
    function hasRedeemables(customerId) {
      const opts = customerRedeemables[customerId];
      if (!opts) return false;
      if (opts.loyalty_points > 0) return true;
      if (opts.store_credit_balance > 0) return true;
      if (opts.gift_card_balance > 0) return true;
      if (opts.coupons && opts.coupons.length) return true;
      if (opts.gift_cards && opts.gift_cards.length) return true;
      if (opts.store_credit && opts.store_credit.length) return true;
      return false;
    }

    function closeRedeemModal() {
      document.getElementById('redeemModal').classList.remove('active');
    }

    function openRedeemModal() {
      const customerId = document.getElementById('customerSelect').value;
      const nameEl = document.getElementById('redeemCustomerName');
      const optsWrap = document.getElementById('redeemOptions');
      if (!customerId || !customerRedeemables[customerId]) {
        nameEl.textContent = 'Select a customer to see redeemable options.';
        optsWrap.innerHTML = '<div style="text-align:center;padding:24px;color:var(--mp-muted)">No customer selected</div>';
      } else {
        nameEl.textContent = 'Available for ' + (customerNameMap[customerId] || 'customer');
        optsWrap.innerHTML = buildRedeemOptions(customerId);
      }
      document.getElementById('redeemModal').classList.add('active');
    }

    function buildRedeemOptions(customerId) {
      const opts = customerRedeemables[customerId];
      const rate = parseFloat(loyaltySettings.redemption_rate) || 0;
      const maxDiscount = cartSubtotal + cartTax;
      let html = '';

      // Loyalty points
      if (opts.loyalty_points > 0 && rate > 0) {
        const discount = Math.min((opts.loyalty_points * rate) / 100, maxDiscount);
        html += `
          <div style="padding:12px; border:1px solid var(--mp-border); border-radius:12px; margin-bottom:10px;">
            <div style="font-weight:700; margin-bottom:6px;">Loyalty Points</div>
            <div style="font-size:13px; color:var(--mp-muted); margin-bottom:8px;">${opts.loyalty_points.toLocaleString()} points available</div>
            <button class="btn-primary" style="width:100%;" onclick="applyRedeem('loyalty', ${opts.loyalty_points}, ${discount}, 0)">Redeem all points → ${formatMoney(discount)} off</button>
          </div>`;
      }

      // Store credit
      if (opts.store_credit_balance > 0) {
        const amount = Math.min(opts.store_credit_balance, maxDiscount);
        html += `
          <div style="padding:12px; border:1px solid var(--mp-border); border-radius:12px; margin-bottom:10px;">
            <div style="font-weight:700; margin-bottom:6px;">Store Credit</div>
            <div style="font-size:13px; color:var(--mp-muted); margin-bottom:8px;">Balance: ${formatMoney(opts.store_credit_balance)}</div>
            <button class="btn-primary" style="width:100%;" onclick="applyRedeem('store_credit', ${opts.store_credit_balance}, ${amount}, 0)">Use ${formatMoney(amount)} store credit</button>
          </div>`;
      }

      // Gift cards
      if (opts.gift_cards && opts.gift_cards.length) {
        for (const g of opts.gift_cards) {
          const amount = Math.min(g.balance, maxDiscount);
          html += `
            <div style="padding:12px; border:1px solid var(--mp-border); border-radius:12px; margin-bottom:10px;">
              <div style="font-weight:700; margin-bottom:6px;">Gift Card</div>
              <div style="font-size:13px; color:var(--mp-muted); margin-bottom:8px;">Code: ${g.code} • Balance: ${formatMoney(g.balance)}</div>
              <button class="btn-primary" style="width:100%;" onclick="applyRedeem('gift_card', ${g.id}, ${amount}, 0)">Use ${formatMoney(amount)}</button>
            </div>`;
        }
      }

      // Coupons
      if (opts.coupons && opts.coupons.length) {
        for (const c of opts.coupons) {
          const couponValue = parseFloat(c.value) || 0;
          let discount = 0;
          if ((c.type || '').toLowerCase() === 'percentage') {
            discount = Math.min(cartSubtotal * couponValue / 100, maxDiscount);
          } else {
            discount = Math.min(couponValue, maxDiscount);
          }
          html += `
            <div style="padding:12px; border:1px solid var(--mp-border); border-radius:12px; margin-bottom:10px;">
              <div style="font-weight:700; margin-bottom:6px;">${c.coupon_name || 'Coupon'}</div>
              <div style="font-size:13px; color:var(--mp-muted); margin-bottom:8px;">Code: ${c.code} • ${c.type || 'Fixed'} discount</div>
              <button class="btn-primary" style="width:100%;" onclick="applyRedeem('coupon', ${c.id}, ${discount}, ${couponValue})">Apply ${formatMoney(discount)} discount</button>
            </div>`;
        }
      }

      if (!html) {
        html = '<div style="text-align:center;padding:24px;color:var(--mp-muted)">No redeemable options available</div>';
      }
      return html;
    }

    function applyRedeem(type, ref, amount, value) {
      const maxDiscount = cartSubtotal + cartTax;
      const available = Math.max(0, maxDiscount - discountAmount);
      if (amount <= 0 || available <= 0) {
        showToast('Nothing to redeem', 'Cart total is already covered', 'warning');
        return;
      }
      const use = Math.min(amount, available);
      discountAmount += use;
      redeemedItems.push({ type, ref, amount: use, original: value });
      document.getElementById('discountInput').value = discountAmount;
      updateCart();
      closeRedeemModal();
      showToast('Redeemed', formatMoney(use) + ' applied', 'success');
    }

    function redeemPoints() {
      openRedeemModal();
    }

    // ===== Coupon Code handling (desktop POS) =====
    function applyCouponCode() {
      const input = document.getElementById('couponCodeInput');
      const code = (input.value || '').trim();
      const customerId = parseInt(document.getElementById('customerSelect').value) || 0;
      if(!code) { clearCouponCode(); return; }
      if(!customerId || customerId == WALKIN_CUSTOMER_ID) {
        showToast('Select customer', 'Coupons and promotions require a real customer, not Walk-in.', 'warning');
        return;
      }
      const params = new URLSearchParams();
      params.append('invoice_type', 'sales');
      params.append('coupon_code', code);
      params.append('customer_id', customerId);
      params.append('cart_subtotal', cart.reduce((s, i) => s + getWeightPricing(i, i.qty).total, 0));
      params.append('csrf_test_name', csrfToken);
      posFetchJson(baseUrl + '/customer_coupon/get_coupon_details', {
        method: 'POST',
        body: params,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' }
      })
        .then(data => {
          if(data.expire_status === 'Valid') {
            couponCode = code;
            couponValue = parseFloat(data.coupon_value) || 0;
            couponType = data.coupon_type || '';
            couponAppliesTo = data.applies_to || 'all';
            couponCategoryId = parseInt(data.category_id) || 0;
            couponBrandId = parseInt(data.brand_id) || 0;
            couponPromotionId = parseInt(data.promotion_id) || 0;
            couponEligibleItemIds = data.eligible_item_ids || [];
            const info = document.getElementById('couponInfo');
            const infoText = document.getElementById('couponInfoText');
            info.style.display = 'block';
            let scopeLabel = '';
            if(couponAppliesTo === 'all') scopeLabel = ' (all items)';
            else if(couponAppliesTo === 'category') scopeLabel = ' (category items only)';
            else if(couponAppliesTo === 'brand') scopeLabel = ' (brand items only)';
            else if(couponAppliesTo === 'items') scopeLabel = ' (selected items only)';
            infoText.textContent = (data.occasion_name || 'Coupon') + ': ' + (couponType === 'Percentage' ? couponValue + '%' : formatMoney(couponValue)) + ' off' + scopeLabel;
            updateCart();
            showToast('Coupon applied', data.message, 'success');
          } else {
            clearCouponCode();
            showToast('Coupon invalid', data.message || 'Invalid coupon code', 'warning');
          }
        })
        .catch(err => {
          clearCouponCode();
          showToast('Coupon error', posErrorText(err, 'Could not validate the coupon. Please try again.'), 'danger');
        });
    }

    function clearCouponCode() {
      couponCode = '';
      couponValue = 0;
      couponType = '';
      couponDiscountAmt = 0;
      couponAppliesTo = 'all';
      couponCategoryId = 0;
      couponBrandId = 0;
      couponPromotionId = 0;
      couponEligibleItemIds = [];
      document.getElementById('couponCodeInput').value = '';
      document.getElementById('couponInfo').style.display = 'none';
      updateCart();
    }

    // Check if a cart item is eligible for the current coupon/promotion targeting
    function isItemCouponEligible(item) {
      if(couponAppliesTo === 'all') return true;
      if(couponAppliesTo === 'category') return parseInt(item.category_id) === couponCategoryId;
      if(couponAppliesTo === 'brand') return parseInt(item.brand_id) === couponBrandId;
      if(couponAppliesTo === 'items') return couponEligibleItemIds.indexOf(parseInt(item.id)) !== -1;
      return false;
    }

    function computeCouponDiscount(subtotal) {
      if(!couponType || couponValue <= 0) return 0;
      // Only discount the eligible portion of the cart
      let eligibleSubtotal = 0;
      if(couponAppliesTo === 'all') {
        eligibleSubtotal = subtotal;
      } else {
        cart.forEach(i => {
          if(isItemCouponEligible(i)) {
            eligibleSubtotal += getWeightPricing(i, i.qty).total;
          }
        });
      }
      if(eligibleSubtotal <= 0) return 0;
      if(couponType === 'Percentage') {
        return Math.min(eligibleSubtotal * couponValue / 100, eligibleSubtotal);
      }
      return Math.min(couponValue, eligibleSubtotal);
    }

    function showCustomerDropdown() {
      document.getElementById('customerDropdown').style.display = 'block';
      filterCustomerDropdown();
    }

    function hideCustomerDropdown() {
      document.getElementById('customerDropdown').style.display = 'none';
    }

    function filterCustomerDropdown() {
      const q = document.getElementById('customerSearchInput').value.toLowerCase();
      const opts = document.querySelectorAll('.customer-option');
      let visible = 0;
      opts.forEach(opt => {
        const name = (opt.dataset.name || '').toLowerCase();
        const text = opt.textContent.toLowerCase();
        const match = name.includes(q) || text.includes(q);
        opt.classList.toggle('hidden', !match);
        if (match) visible++;
      });
      document.getElementById('customerDropdown').style.display = visible ? 'block' : 'none';
    }

    function selectCustomer(id) {
      const opt = document.querySelector('.customer-option[data-id="' + id + '"]');
      if (!opt) return;
      document.getElementById('customerSelect').value = id;
      document.getElementById('customerSearchInput').value = '';
      document.getElementById('customerNameDisplay').textContent = opt.dataset.name || 'Walk-in Customer';
      hideCustomerDropdown();
      handleCustomerChange();
    }

    function handleCustomerChange() {
      const id = document.getElementById('customerSelect').value;
      const opt = document.querySelector('.customer-option[data-id="' + id + '"]');
      if (opt) document.getElementById('customerNameDisplay').textContent = opt.dataset.name || 'Walk-in Customer';
      // Show customer outstanding balance as info (not a block)
      const balEl = document.getElementById('customerBalanceInfo');
      const cust = (typeof customers !== 'undefined' && customers.length) ? customers.find(c => c.id == id) : null;
      if(cust && parseFloat(cust.sales_due) > 0){
        const due = parseFloat(cust.sales_due);
        const limit = parseFloat(cust.credit_limit || 0);
        let txt = 'Outstanding: ' + formatMoney(due);
        if(limit > 0 && due > limit){
          txt += ' (over credit limit of ' + formatMoney(limit) + ')';
          balEl.style.color = '#dc2626';
        } else if(limit > 0){
          txt += ' / Limit: ' + formatMoney(limit);
          balEl.style.color = '#d97706';
        } else {
          balEl.style.color = '#d97706';
        }
        balEl.textContent = txt;
        balEl.style.display = 'block';
      } else {
        balEl.style.display = 'none';
      }
      updateCart();
    }

    function syncCustomerSearchFromSelect() {
      const id = document.getElementById('customerSelect').value;
      const opt = document.querySelector('.customer-option[data-id="' + id + '"]');
      if (opt) document.getElementById('customerNameDisplay').textContent = opt.dataset.name || 'Walk-in Customer';
    }

    function autoSelectCustomer() {
      const input = document.getElementById('customerSearchInput');
      const q = input.value.trim().toLowerCase();
      if (!q) {
        hideCustomerDropdown();
        return;
      }
      const visible = Array.from(document.querySelectorAll('.customer-option')).filter(opt => !opt.classList.contains('hidden'));
      if (visible.length === 1) {
        selectCustomer(visible[0].dataset.id);
        return;
      }
      for (const opt of visible) {
        const name = (opt.dataset.name || '').toLowerCase().trim();
        if (name === q) {
          selectCustomer(opt.dataset.id);
          return;
        }
      }
    }

    function handleCustomerSearchKey(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const visible = Array.from(document.querySelectorAll('.customer-option')).filter(opt => !opt.classList.contains('hidden'));
        if (visible.length > 0) {
          selectCustomer(visible[0].dataset.id);
        }
      } else if (e.key === 'Escape') {
        hideCustomerDropdown();
      }
    }

    let confirmCallback = null;
    let currentSalesId = null;
    let currentWhatsappUrl = null;

    function closeAllPopups() {
      const holdPopup = document.getElementById('holdPopup');
      const salesListPopup = document.getElementById('salesListPopup');
      const paymentPopup = document.getElementById('paymentPopup');
      if (holdPopup) holdPopup.classList.remove('active');
      if (salesListPopup) salesListPopup.classList.remove('active');
      if (paymentPopup) paymentPopup.classList.remove('active');
    }

    function openPostSaleModal(salesId, whatsappUrl) {
      currentSalesId = salesId;
      currentWhatsappUrl = whatsappUrl || '';
      const shareBtn = document.getElementById('shareBtn');
      if (shareBtn) {
        shareBtn.style.display = currentWhatsappUrl ? 'flex' : 'none';
      }
      closeAllPopups();
      document.getElementById('postSaleModal').classList.add('active');
      
      // Load receipt inline
      const receiptContent = document.getElementById('receiptContent');
      receiptContent.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--mp-muted);">Loading receipt...</div>';
      
      fetch(baseUrl + '/sales/get_receipt_html/' + salesId, {
        method: 'GET',
        headers: { 'Accept': 'text/html' }
      })
        .then(r => {
          if (!r.ok) throw new Error(posHttpMessage(r.status, '', r.redirected, 'Could not load the receipt.'));
          return r.text();
        })
        .then(html => {
          receiptContent.innerHTML = html;
        })
        .catch(err => {
          receiptContent.innerHTML = '';
          const errBox = document.createElement('div');
          errBox.style.cssText = 'text-align: center; padding: 40px; color: var(--mp-danger);';
          errBox.textContent = posErrorText(err, 'Could not load the receipt. Please try again.');
          receiptContent.appendChild(errBox);
        });
    }

    function closePostSaleModal() {
      document.getElementById('postSaleModal').classList.remove('active');
      // Clear hold tracking on successful sale
      currentLoadedHoldId = null;
      holdLoadedAt = null;
      resetCart();
      setTimeout(() => window.location.reload(), 600);
    }

    function shareInvoice() {
      if (!currentWhatsappUrl) return;
      window.open(currentWhatsappUrl, '_blank');
    }

    function printAndDone() {
      if (!currentSalesId) {
        showToast('Error', 'No receipt to print', 'danger');
        return;
      }
      
      // Open the original print page which has proper POS receipt formatting
      window.open(baseUrl + '/sales/print_invoice_pos/' + currentSalesId, '_blank');
      
      // Close modal after opening print window
      setTimeout(() => {
        closePostSaleModal();
      }, 500);
    }

    function openAlertModal(title, message) {
      document.getElementById('alertTitle').textContent = title;
      document.getElementById('alertMessage').textContent = message;
      closeAllPopups();
      document.getElementById('alertModal').classList.add('active');
    }

    let calculatorExpression = '';
    function openCalculator() {
      calculatorExpression = '';
      document.getElementById('calculatorDisplay').value = '0';
      document.getElementById('calculatorModal').classList.add('active');
    }
    function closeCalculator() { document.getElementById('calculatorModal').classList.remove('active'); }
    function calculatorInput(value) {
      calculatorExpression += value;
      document.getElementById('calculatorDisplay').value = calculatorExpression || '0';
    }
    function calculatorClear() {
      calculatorExpression = '';
      document.getElementById('calculatorDisplay').value = '0';
    }
    function calculatorBackspace() {
      calculatorExpression = calculatorExpression.slice(0, -1);
      document.getElementById('calculatorDisplay').value = calculatorExpression || '0';
    }
    function calculatorEquals() {
      if (!calculatorExpression) return;
      if (!/^[0-9+\-*/%.() ]+$/.test(calculatorExpression)) return;
      try {
        const result = Function('"use strict"; return (' + calculatorExpression + ')')();
        if (!Number.isFinite(result)) throw new Error('Invalid result');
        calculatorExpression = String(result);
        document.getElementById('calculatorDisplay').value = calculatorExpression;
      } catch (e) {
        calculatorExpression = '';
        document.getElementById('calculatorDisplay').value = 'Error';
      }
    }

    function closeAlertModal() {
      document.getElementById('alertModal').classList.remove('active');
    }

    function openConfirmModal(title, message, onConfirm) {
      document.getElementById('confirmTitle').textContent = title;
      document.getElementById('confirmMessage').textContent = message;
      confirmCallback = onConfirm;
      closeAllPopups();
      document.getElementById('confirmModal').classList.add('active');
    }

    function closeConfirmModal() {
      document.getElementById('confirmModal').classList.remove('active');
      confirmCallback = null;
    }

    function runConfirmAction() {
      if (typeof confirmCallback === 'function') confirmCallback();
      closeConfirmModal();
    }

    function openHoldModal() {
      if (cart.length === 0) {
        openAlertModal('Empty cart', 'Add items before holding the sale.');
        return;
      }
      document.getElementById('holdReferenceInput').value = '';
      closeAllPopups();
      document.getElementById('holdModal').classList.add('active');
      setTimeout(() => document.getElementById('holdReferenceInput').focus(), 50);
    }

    function closeHoldModal() {
      document.getElementById('holdModal').classList.remove('active');
    }

    function holdCurrentCart() {
      openHoldModal();
    }

    function submitHold() {
      const reference = document.getElementById('holdReferenceInput').value.trim();
      const warehouseId = WAREHOUSE_ID ? parseInt(WAREHOUSE_ID) : 0;
      if (!warehouseId) {
        openAlertModal('Hold failed', 'No branch found for this store. Please set up a branch first.');
        return;
      }
      const paymentNote = (document.getElementById('sales_note') && document.getElementById('sales_note').value || '').trim();
      const pharmacyNote = buildPharmacySalesNote();
      const payload = {
        warehouse_id: warehouseId,
        customer_id: parseInt(document.getElementById('customerSelect').value) || 0,
        table_id: document.getElementById('tableSelect') ? (parseInt(document.getElementById('tableSelect').value) || 0) : 0,
        cart: cart.map(item => ({
          id: item.id,
          qty: item.qty,
          weight_base_qty: item.weight_base_qty,
          weight_lines: item.weight_lines,
          price: getWeightPricing(item, item.qty).unit,
          base_price: item.unitPrice,
          tax_value: item.tax_value || 0,
          tax_type: item.tax_type || 'Exclusive',
          price_type: priceType,
          tax_id: item.tax_id || 0
        })),
        discount: discountAmount || 0,
        shipping: shippingFeeId ? { id: shippingFeeId } : null,
        sales_note: [pharmacyNote, paymentNote].filter(Boolean).join('\n\n'),
        pharmacy_assessment: pharmacyNote,
        reference_id: reference,
        csrf_test_name: csrfToken
      };

      const submitBtn = document.getElementById('holdSubmitBtn');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Holding...';

      posFetchJson(baseUrl + '/mobile/hold', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(res => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Hold';
        if (res.status === 'success') {
          closeHoldModal();
          const itemCount = cart.length;
          const msg = itemCount + ' item' + (itemCount !== 1 ? 's' : '') + ' held successfully';
          showToast('Sale Held', msg, 'success');
          resetCart();
          setTimeout(() => window.location.reload(), 1000);
        } else {
          openAlertModal('Hold failed', res.message || 'Could not hold the sale. Make sure products are in stock and a branch is set.');
        }
      })
      .catch(err => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Hold';
        openAlertModal('Hold failed', posErrorText(err, 'Could not hold the sale. Please try again.'));
      });
    }

    function loadHold(holdId) {
      if (!holdId) return;
      const doLoad = () => {
        posFetchJson(baseUrl + '/mobile/get_hold/' + holdId, {
          method: 'GET',
          headers: { 'Accept': 'application/json' }
        })
          .then(hold => {
            try {
              if (!hold || hold.status === 'error') {
                openAlertModal('Hold not found', hold?.message || 'Could not load this hold.');
                return;
              }
              if (!hold.items || hold.items.length === 0) {
                openAlertModal('Hold is empty', 'This hold has no items.');
                return;
              }
              cart.length = 0;
              const customerSelect = document.getElementById('customerSelect');
              if (customerSelect && hold.customer_id) {
                customerSelect.value = hold.customer_id;
                syncCustomerSearchFromSelect();
              }
              if (hold.price_type === 'wholesale' || hold.price_type === 'retail') {
                priceType = hold.price_type;
                setPriceType(priceType);
              }
              const tableSelect = document.getElementById('tableSelect');
              if (tableSelect) tableSelect.value = String(parseInt(hold.table_id, 10) || 0);
              syncTableShippingState();
              discountAmount = parseFloat(hold.discount) || 0;
              document.getElementById('discountInput').value = discountAmount;
              // Restore held shipping selection
              if (hold.shipping && hold.shipping.id && (!tableSelect || (parseInt(tableSelect.value, 10) || 0) === 0)) {
                const shipSel = document.getElementById('shippingSelect');
                if (shipSel && shipSel.querySelector('option[value="' + hold.shipping.id + '"]')) {
                  shipSel.value = hold.shipping.id;
                  onShippingChange();
                }
              }
              hold.items.forEach(it => {
                const product = products.find(p => p.id === it.id) || {};
                cart.push({
                  ...product,
                  id: it.id,
                  name: it.name,
                  unitPrice: it.price,
                  qty: it.qty,
                  tax_value: it.tax_value,
                  tax_type: it.tax_type,
                  tax_id: it.tax_id
                });
              });
              // Remove the hold from the table immediately
              const row = document.querySelector(`tr[data-hold-id="${holdId}"]`);
              if (row) row.remove();
              
              closeHoldPopup();
              updateCart();
              renderProducts();
              // Track loaded hold for auto-wipe if not processed
              currentLoadedHoldId = holdId;
              holdLoadedAt = Date.now();
              showToast('Hold loaded', hold.items.length + ' item(s) restored', 'success');
            } catch (e) {
              console.error('Hold response error:', e);
              openAlertModal('Hold error', 'The hold could not be loaded because the server sent an unexpected response. Please try again.');
            }
          })
          .catch(err => {
            openAlertModal('Could not load hold', posErrorText(err, 'The hold could not be loaded. Please try again.'));
          });
      };
      if (cart.length > 0) {
        openConfirmModal('Load hold?', 'Loading a hold will replace the current cart. Continue?', doLoad);
      } else {
        doLoad();
      }
    }

    function deleteHold(holdId) {
      if (!holdId) return;
      openConfirmModal('Delete hold?', 'This held invoice will be removed permanently.', () => {
        posFetchText(baseUrl + '/pos/hold_invoice_delete/' + holdId, {
          method: 'GET',
          headers: { 'Accept': 'text/plain' }
        })
          .then(res => {
            const trimmed = res.trim().toLowerCase();
            if (trimmed === 'success') {
              showToast('Hold deleted', 'Invoice removed from holds', 'success');
              // Remove the hold from the table immediately
              const row = document.querySelector(`tr[data-hold-id="${holdId}"]`);
              if (row) row.remove();
              // Refresh the hold list
              openHoldPopup();
            } else {
              const msg = (res && res.indexOf('<') === -1 && res.length < 200) ? res.trim() : '';
              openAlertModal('Delete failed', msg || 'Could not delete the hold. Please try again.');
            }
          })
          .catch(err => {
            openAlertModal('Delete failed', posErrorText(err, 'Could not delete the hold. Please try again.'));
          });
      });
    }

    function updateTarget() {
      const pct = Math.min(100, Math.round((todaySales / dailyTarget) * 100));
      document.getElementById('todaySalesText').textContent = formatMoney(todaySales);
      document.getElementById('targetAmountLabel').textContent = 'of ' + formatMoney(dailyTarget);
      document.getElementById('targetPercent').textContent = pct + '%';
      document.getElementById('targetFill').style.width = pct + '%';
    }

    function toggleUserMenu() {
      document.getElementById('userDropdown').classList.toggle('show');
    }
    function closeUserMenu() {
      document.getElementById('userDropdown').classList.remove('show');
    }

    function openAddCustomer() {
      document.getElementById('addCustomerModal').classList.add('active');
      document.getElementById('customerName').value = '';
      document.getElementById('customerPhone').value = '';
      document.getElementById('customerName').focus();
    }
    function closeAddCustomer() {
      document.getElementById('addCustomerModal').classList.remove('active');
    }
    function saveCustomer() {
      const name = document.getElementById('customerName').value.trim();
      const phone = document.getElementById('customerPhone').value.trim();
      if (!name) {
        showToast('Missing name', 'Enter the customer name', 'danger');
        return;
      }
      
      const saveBtn = document.querySelector('#addCustomerModal button[onclick="saveCustomer()"]');
      saveBtn.disabled = true;
      saveBtn.textContent = 'Saving...';
      
      const formData = new FormData();
      formData.append('customer_name', name);
      formData.append('mobile', phone);
      formData.append('csrf_test_name', csrfToken);
      
      posFetchJson(baseUrl + '/customers/add_customer_ajax', {
        method: 'POST',
        body: formData
      })
      .then(res => {
          if (res.status === 'success' && res.customer_id) {
            // Update customer selection
            const label = phone ? name + ' (' + phone + ')' : name;
            const select = document.getElementById('customerSelect');
            const option = document.createElement('option');
            option.value = res.customer_id;
            option.textContent = label;
            option.selected = true;
            select.appendChild(option);
            document.getElementById('customerSearchInput').value = '';
            document.getElementById('customerNameDisplay').textContent = label;
            closeAddCustomer();
            showToast('Customer added', label, 'success');
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Customer';
          } else {
            showToast('Failed to add customer', res.message || 'Please try again', 'danger');
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Customer';
          }
      })
      .catch(err => {
        showToast('Save failed', posErrorText(err, 'Could not save the customer. Please check the details and try again.'), 'danger');
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Customer';
      });
    }

    function openChangePassword() {
      closeUserMenu();
      document.getElementById('changePasswordModal').classList.add('active');
      document.getElementById('currentPin').value = '';
      document.getElementById('newPin').value = '';
      document.getElementById('confirmPin').value = '';
      document.getElementById('currentPin').focus();
    }
    function closeChangePassword() {
      document.getElementById('changePasswordModal').classList.remove('active');
    }
    function savePassword() {
      const current = document.getElementById('currentPin').value.trim();
      const next = document.getElementById('newPin').value.trim();
      const confirm = document.getElementById('confirmPin').value.trim();
      if (current.length < 4) {
        showToast('Invalid current PIN', 'Enter your current PIN', 'danger');
        return;
      }
      if (next.length < 4) {
        showToast('PIN too short', 'New PIN must be at least 4 digits', 'danger');
        return;
      }
      if (next !== confirm) {
        showToast('PIN mismatch', 'New PIN and confirmation do not match', 'danger');
        return;
      }
      closeChangePassword();
      showToast('Password updated', 'Your PIN has been changed', 'success');
    }

    function logout() {
      closeUserMenu();
      // Check if cashier is clocked in
      if (clockedIn) {
        // Show modern alert asking to clock out first
        const modal = document.getElementById('confirmModal');
        document.getElementById('confirmTitle').textContent = 'Clock Out Required';
        document.getElementById('confirmMessage').textContent = 'You are currently clocked in. Please clock out before logging out.';
        const confirmBtn = modal.querySelector('.modal-actions button:last-child');
        const cancelBtn = modal.querySelector('.modal-actions button:first-child');
        confirmBtn.textContent = 'Clock Out';
        cancelBtn.textContent = 'Cancel';
        confirmBtn.onclick = () => {
          closeConfirmModal();
          toggleClock();
        };
        cancelBtn.onclick = closeConfirmModal;
        closeAllPopups();
        modal.classList.add('active');
      } else {
        // Show logout confirmation with modern modal
        const modal = document.getElementById('confirmModal');
        document.getElementById('confirmTitle').textContent = 'Log Out';
        document.getElementById('confirmMessage').textContent = 'Are you sure you want to log out?';
        const confirmBtn = modal.querySelector('.modal-actions button:last-child');
        const cancelBtn = modal.querySelector('.modal-actions button:first-child');
        confirmBtn.textContent = 'Log Out';
        cancelBtn.textContent = 'Cancel';
        confirmBtn.onclick = () => {
          window.location.href = '<?= base_url('logout') ?>';
        };
        cancelBtn.onclick = closeConfirmModal;
        closeAllPopups();
        modal.classList.add('active');
      }
    }

    document.addEventListener('click', (e) => {
      if (!e.target.closest('.user-menu')) closeUserMenu();
    });

    const moonIcon = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
    const sunIcon = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>';

    function updateThemeButton() {
      const isDark = document.body.classList.contains('dark');
      document.getElementById('themeIcon').innerHTML = isDark ? sunIcon : moonIcon;
      document.getElementById('themeLabel').textContent = isDark ? 'Light' : 'Dark';
    }
    function toggleTheme() {
      document.body.classList.toggle('dark');
      const mode = document.body.classList.contains('dark') ? 'dark' : 'light';
      try { localStorage.setItem('posTheme', mode); } catch (e) {}
      updateThemeButton();
      showToast('Theme changed', mode === 'dark' ? 'Switched to dark mode' : 'Switched to light mode', 'success');
    }
    function initTheme() {
      const saved = (() => { try { return localStorage.getItem('posTheme'); } catch (e) { return null; } })();
      if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.body.classList.add('dark');
      }
      updateThemeButton();
    }

    initTheme();
    init();
    window.mpPosBootReady = true;
    document.getElementById('posStartupError').hidden = true;
  </script>
  <?php $this->load->view('pos/components/hold_popup'); ?>
  <?php $this->load->view('pos/components/sales_list_popup'); ?>
  <?php $this->load->view('pos/components/payment_popup', ['pos_json' => $pos_json]); ?>
  <script>
  (function() {
    function findHeaderBtn(text) {
      const btns = document.querySelectorAll('.header-btn');
      for (let i = 0; i < btns.length; i++) {
        if (btns[i].textContent.trim().includes(text)) return btns[i];
      }
      return null;
    }
    const holdBtn = findHeaderBtn('Hold List');
    const salesBtn = findHeaderBtn('Sales List');
    if (holdBtn) {
      holdBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.openHoldPopup();
      });
    }
    if (salesBtn) {
      salesBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.openSalesListPopup();
      });
    }
    window.closeHoldPopup = function() { document.getElementById('holdPopup').classList.remove('active'); };
    window.closeSalesListPopup = function() { document.getElementById('salesListPopup').classList.remove('active'); };
    
    window.openHoldPopup = function() {
      closeAllPopups();
      document.getElementById('holdPopup').classList.add('active');
      // Refresh hold list from server
      posFetchJson(baseUrl + '/pos/get_holds_ajax', {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
      })
        .then(data => {
          const tbody = document.querySelector('#holdPopup table tbody');
          if (!tbody) return;
          if (!data.holds || data.holds.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="padding: 24px; text-align: center; color: var(--mp-muted);">No recent holds</td></tr>';
          } else {
            tbody.innerHTML = data.holds.map(h => `
              <tr style="border-bottom: 1px solid var(--mp-border);" data-hold-id="${h.id}">
                <td style="padding: 10px;">${h.reference_id || '-'}</td>
                <td style="padding: 10px;">${h.customer_name || 'Walk-in'}</td>
                <td style="padding: 10px; font-weight: 600;">₦${h.grand_total_formatted}</td>
                <td style="padding: 10px;">${h.sales_date}</td>
                <td style="padding: 10px; white-space: nowrap; text-align: right;">
                  <button class="btn-primary" style="padding:6px 12px; font-size:12px;" onclick="loadHold(${h.id})">Load</button>
                  <button class="btn-secondary" style="padding:6px 12px; font-size:12px;" onclick="deleteHold(${h.id})">Delete</button>
                </td>
              </tr>
            `).join('');
          }
        })
        .catch(err => {
          const tbody = document.querySelector('#holdPopup table tbody');
          if (tbody) {
            tbody.innerHTML = '';
            const row = tbody.insertRow();
            const cell = row.insertCell();
            cell.colSpan = 5;
            cell.style.cssText = 'padding: 24px; text-align: center; color: var(--mp-danger);';
            cell.textContent = posErrorText(err, 'Failed to load holds. Please try again.');
          }
        });
    };

    window.openSalesListPopup = function() {
      closeAllPopups();
      document.getElementById('salesListPopup').classList.add('active');
      const tbody = document.getElementById('salesListBody');
      tbody.innerHTML = '<tr><td colspan="4" style="padding: 24px; text-align: center; color: var(--mp-muted);">Loading sales...</td></tr>';
      
      // Fetch today's sales
      posFetchJson(baseUrl + '/pos/get_todays_sales_ajax', {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
      })
        .then(data => {
          if (!data.sales || data.sales.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="padding: 24px; text-align: center; color: var(--mp-muted);">No sales today</td></tr>';
          } else {
            tbody.innerHTML = data.sales.map(s => `
              <tr style="border-bottom: 1px solid var(--mp-border);">
                <td style="padding: 12px; font-weight: 600;">${s.sales_code || '-'}</td>
                <td style="padding: 12px;">${s.customer_name || 'Walk-in'}</td>
                <td style="padding: 12px; text-align: right; font-weight: 600;">₦${s.grand_total_formatted}</td>
                <td style="padding: 12px; text-align: center; color: var(--mp-muted); font-size: 12px;">${s.time || '-'}</td>
              </tr>
            `).join('');
          }
        })
        .catch(err => {
          tbody.innerHTML = '';
          const row = tbody.insertRow();
          const cell = row.insertCell();
          cell.colSpan = 4;
          cell.style.cssText = 'padding: 24px; text-align: center; color: var(--mp-danger);';
          cell.textContent = posErrorText(err, 'Failed to load sales. Please try again.');
        });
    };

    function closeAllDesktopSelects() {
      document.querySelectorAll('.mp-dselect.open').forEach(w => w.classList.remove('open'));
    }
    document.addEventListener('click', closeAllDesktopSelects);

    function buildDesktopSelect(sel) {
      if (sel.dataset.mpBuilt) return;
      sel.dataset.mpBuilt = '1';
      sel.classList.add('mp-dselect-native');
      const wrap = document.createElement('div');
      wrap.className = 'mp-dselect';
      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel);
      const trigger = document.createElement('div');
      trigger.className = 'mp-dselect__trigger';
      wrap.appendChild(trigger);
      const list = document.createElement('div');
      list.className = 'mp-dselect__list';
      wrap.appendChild(list);

      function updateTrigger() {
        const opt = sel.options[sel.selectedIndex];
        trigger.textContent = opt ? opt.textContent : '';
        trigger.classList.toggle('placeholder', !sel.value);
      }
      function render() {
        list.innerHTML = '';
        Array.from(sel.options).forEach((opt, idx) => {
          const div = document.createElement('div');
          div.className = 'mp-dselect__option';
          div.textContent = opt.textContent;
          if (sel.selectedIndex === idx) div.classList.add('active');
          div.addEventListener('click', e => {
            e.stopPropagation();
            sel.selectedIndex = idx;
            sel.dispatchEvent(new Event('change', {bubbles: true}));
            closeAllDesktopSelects();
            render();
          });
          list.appendChild(div);
        });
        updateTrigger();
      }
      trigger.addEventListener('click', e => {
        e.stopPropagation();
        const isOpen = wrap.classList.contains('open');
        closeAllDesktopSelects();
        if (!isOpen) wrap.classList.add('open');
      });
      sel.addEventListener('mpupdate', () => { render(); });
      render();
    }

    window.addPaymentRow = function(method, amount, account) {
      const rows = document.getElementById('paymentRows');
      const div = document.createElement('div');
      div.className = 'payment-method-row';
      div.style.cssText = 'display: grid; grid-template-columns: 140px 1fr 1fr 180px 40px; gap: 10px; align-items: center;';
      div.innerHTML = `
        <select class="payment-method">
          ${window.paymentModeOptions}
        </select>
        <div class="form-group" style="margin: 0;">
          <input type="number" class="payment-amount" placeholder="0.00" min="0" step="0.01" oninput="updatePaymentBalance()">
        </div>
        <div class="form-group" style="margin: 0;">
          <input type="text" class="payment-ref" placeholder="Reference">
        </div>
        <select class="payment-account">
          <option value="">- Account -</option>
          ${window.accountOptions}
        </select>
        <button class="btn-secondary" type="button" onclick="this.closest('.payment-method-row').remove(); updatePaymentBalance();" style="padding: 8px; font-size: 12px;">×</button>
      `;
      rows.appendChild(div);
      const methodSel = div.querySelector('.payment-method');
      buildDesktopSelect(methodSel);
      const accountSel = div.querySelector('.payment-account');
      buildDesktopSelect(accountSel);
      if (method) { methodSel.value = method; methodSel.dispatchEvent(new Event('mpupdate')); }
      if (account) { accountSel.value = account; accountSel.dispatchEvent(new Event('mpupdate')); }
      const amtInput = div.querySelector('.payment-amount');
      if (amount !== undefined && amount !== null) {
        amtInput.value = amount.toFixed(POS_DECIMALS);
      }
      methodSel.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const ref = div.querySelector('.payment-ref');
        const refRequired = opt && opt.dataset.requiresReference === '1';
        ref.placeholder = refRequired ? 'Reference required' : 'Reference';
        ref.style.display = 'block';
      });
      updatePaymentBalance();
    };

    window.openPaymentPopup = function(mode) {
      const grandTotalText = document.getElementById('grandTotal').textContent;
      const due = parseFloat(grandTotalText.replace(/[₦,]/g, '')) || 0;
      document.getElementById('paymentAmountDue').textContent = grandTotalText;
      document.getElementById('paymentPopupMode').value = mode || 'pay';
      document.getElementById('paymentPopupTitle').textContent =
        mode === 'plan' ? 'Payment Plan' : (mode === 'pay' ? 'Payment' : 'Split Payment');
      document.getElementById('paymentRows').innerHTML = '';
      document.getElementById('addPaymentRowBtn').style.display = (mode === 'split' || mode === 'plan') ? '' : 'none';
      document.getElementById('bnplSection').style.display = mode === 'plan' ? 'block' : 'none';
      document.querySelectorAll('#bnplSection select').forEach(buildDesktopSelect);
      if (mode === 'pay') {
        window.addPaymentRow(window.defaultPaymentMode, due, window.cashAccountId);
      } else {
        window.addPaymentRow(window.defaultPaymentMode, 0, window.cashAccountId);
      }
      if (mode === 'plan') {
        document.getElementById('bnplDownPct').value = 30;
        document.getElementById('bnplCount').value = 3;
        document.getElementById('bnplFrequency').value = 'biweekly';
        document.getElementById('bnplLateFee').value = 0;
        const nextWeek = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000);
        document.getElementById('bnplFirstDue').value = nextWeek.toISOString().split('T')[0];
      }
      closeAllPopups();
      document.getElementById('paymentPopup').classList.add('active');
      updatePaymentBalance();
    };

    window.closePaymentPopup = function() {
      document.getElementById('paymentPopup').classList.remove('active');
    };

    window.updatePaymentBalance = function() {
      const dueText = document.getElementById('paymentAmountDue').textContent;
      const due = parseFloat(dueText.replace(/[₦,]/g, '')) || 0;
      const mode = document.getElementById('paymentPopupMode').value;
      let paid = 0;
      document.querySelectorAll('#paymentRows .payment-amount').forEach(input => {
        paid += parseFloat(input.value) || 0;
      });
      let balance = due - paid;
      if (mode === 'plan') {
        const pct = parseFloat(document.getElementById('bnplDownPct').value) || 0;
        const down = Math.min(due * pct / 100, due);
        const count = parseInt(document.getElementById('bnplCount').value) || 1;
        const remaining = Math.max(0, due - down);
        const each = count > 0 ? remaining / count : 0;
        document.getElementById('bnplDownAmt').value = formatMoney(down);
        document.getElementById('bnplEachAmt').value = formatMoney(each);
        const firstRow = document.querySelector('#paymentRows .payment-method-row');
        const firstAmount = firstRow ? firstRow.querySelector('.payment-amount') : null;
        if (firstAmount && (!firstAmount.value || parseFloat(firstAmount.value) === 0)) {
          firstAmount.value = down.toFixed(POS_DECIMALS);
          paid = 0;
          document.querySelectorAll('#paymentRows .payment-amount').forEach(input => {
            paid += parseFloat(input.value) || 0;
          });
        }
        balance = due - paid;
      }
      document.getElementById('paymentPaid').textContent = formatMoney(paid);
      document.getElementById('paymentBalance').textContent = formatMoney(balance);
      const submit = document.getElementById('paymentSubmitBtn');
      submit.textContent = mode === 'plan' ? 'Create Plan' : 'Pay';
      submit.disabled = (mode === 'pay' || mode === 'split') && Math.abs(balance) > 0.01;
    };

    function getPaymentRows() {
      const rows = [];
      document.querySelectorAll('#paymentRows .payment-method-row').forEach(row => {
        const method = row.querySelector('.payment-method').value;
        const amount = parseFloat(row.querySelector('.payment-amount').value) || 0;
        const ref = row.querySelector('.payment-ref').value.trim();
        const account = row.querySelector('.payment-account').value;
        if (method && amount > 0) {
          rows.push({
            payment_type: method,
            amount: amount,
            payment_reference: ref,
            account_id: account
          });
        }
      });
      return rows;
    }

    function buildSalePayload(action, paid, paymentRows) {
      const dueText = document.getElementById('paymentAmountDue').textContent;
      const due = parseFloat(dueText.replace(/[₦,]/g, '')) || 0;
      const cartItems = cart.map(item => ({
        id: item.id,
        qty: item.qty,
        weight_base_qty: item.weight_base_qty,
        weight_lines: item.weight_lines,
        price: getWeightPricing(item, item.qty).unit,
        base_price: item.unitPrice,
        tax_value: item.tax_value || 0,
        tax_type: item.tax_type || 'Exclusive',
        price_type: priceType,
        tax_id: item.tax_id || 0,
        unit_id: item.unit_id || '',
        unit_name: item.unit_name || '',
        conversion_factor: item.conversion_factor || 1
      }));
      const payload = {
        command: 'save',
        action: action,
        customer_id: parseInt(document.getElementById('customerSelect').value) || 0,
        table_id: document.getElementById('tableSelect') ? (parseInt(document.getElementById('tableSelect').value) || 0) : 0,
        warehouse_id: WAREHOUSE_ID ? parseInt(WAREHOUSE_ID) : 0,
        cart: cartItems,
        discount: discountAmount || 0,
        shipping: shippingFeeId ? { id: shippingFeeId } : null,
        sales_note: '',
        sales_status: 'Final',
        coupon_code: couponCode || '',
        coupon_discount_amt: couponDiscountAmt || 0,
        amount_paid: paid,
        payment_type: paymentRows[0] ? paymentRows[0].payment_type : window.defaultPaymentMode,
        account_id: paymentRows[0] ? paymentRows[0].account_id : '',
        payment_rows: paymentRows,
        restaurant_order_key: window.__mpRestaurantOrderKey || (window.__mpRestaurantOrderKey = 'pos-' + Date.now() + '-' + Math.random().toString(36).slice(2)),
        medical_note_id: medicalNoteId,
        csrf_test_name: csrfToken
      };
      if (action === 'plan') {
        const pct = parseFloat(document.getElementById('bnplDownPct').value) || 0;
        const down = Math.min(due * pct / 100, due);
        const balance = Math.max(0, due - down);
        const count = parseInt(document.getElementById('bnplCount').value) || 1;
        const each = count > 0 ? balance / count : 0;
        payload.amount_paid = paymentRows[0] ? paymentRows[0].amount : 0;
        payload.payment_type = paymentRows[0] ? paymentRows[0].payment_type : window.defaultPaymentMode;
        payload.account_id = paymentRows[0] ? paymentRows[0].account_id : '';
        payload.bnpl = {
          count: count,
          each_amt: each,
          frequency: document.getElementById('bnplFrequency').value,
          first_due: document.getElementById('bnplFirstDue').value,
          late_fee: parseFloat(document.getElementById('bnplLateFee').value) || 0
        };
      } else if (action === 'split') {
        payload.payment_type = '';
        payload.account_id = '';
      }
      return payload;
    }

    window.processPayment = function() {
      const dueText = document.getElementById('paymentAmountDue').textContent;
      const due = parseFloat(dueText.replace(/[₦,]/g, '')) || 0;
      const mode = document.getElementById('paymentPopupMode').value;
      const paymentRows = getPaymentRows();
      const totalPaid = paymentRows.reduce((s, r) => s + r.amount, 0);
      if ((mode === 'pay' || mode === 'split') && Math.abs(due - totalPaid) > 0.01) {
        openAlertModal('Insufficient payment', 'Total paid must equal the amount due.');
        return;
      }
      if (mode === 'plan' && totalPaid > due) {
        openAlertModal('Invalid down payment', 'Down payment cannot be more than the total.');
        return;
      }
      const payload = buildSalePayload(mode, totalPaid, paymentRows);
      const submitBtn = document.getElementById('paymentSubmitBtn');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Saving...';
      posFetchJson(baseUrl + '/mobile/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(res => {
        submitBtn.disabled = false;
        submitBtn.textContent = mode === 'plan' ? 'Create Plan' : 'Pay';
        if (res.status === 'success') {
          closePaymentPopup();
          const salesId = res.sales_id;
          const msg = res.message || (isPharmacyPos ? 'Dispensing record saved' : 'Sale saved');
          showToast('Sale Completed', msg, 'success');

          // Show post-sale actions if sales_id is available
          if (salesId) {
            setTimeout(() => {
              openPostSaleModal(salesId, res.whatsapp_url);
            }, 500);
          } else {
            resetCart();
            setTimeout(() => window.location.reload(), 800);
          }
        } else {
          openAlertModal('Payment failed', res.message || 'Could not save the sale. Please check the details and try again.');
        }
      })
      .catch(err => {
        submitBtn.disabled = false;
        submitBtn.textContent = mode === 'plan' ? 'Create Plan' : 'Pay';
        const networkError = !navigator.onLine || /Could not reach the server|network|failed to fetch|load failed/i.test(posErrorText(err, ''));
        if (networkError && window.MPOfflineDB) {
          const queued = Object.assign({}, payload, { idempotencyKey: payload.restaurant_order_key });
          MPOfflineDB.queueSale({
            url: baseUrl + '/mobile/save',
            payload: queued,
            transport: 'json',
            idempotencyKey: queued.idempotencyKey,
            receiptPayload: queued,
            status: 'pending'
          }).then(queueId => {
            closePaymentPopup();
            showToast('Sale queued offline', 'Saved on this device and will sync when the connection returns. Queue #' + queueId, 'warning');
            resetCart();
          }).catch(() => openAlertModal('Payment failed', 'The connection failed and this sale could not be saved locally. Keep the page open and try again.'));
          return;
        }
        openAlertModal('Payment failed', posErrorText(err, 'Could not save the sale. Please check the details and try again.'));
      });
    };

    async function syncQueuedDesktopSales() {
      if (!navigator.onLine || !window.MPOfflineDB) return;
      const queued = await MPOfflineDB.getPendingSales();
      for (const sale of queued) {
        if (!sale.payload || sale.transport !== 'json') continue;
        await MPOfflineDB.updateSaleStatus(sale.queueId, 'syncing').catch(() => {});
        try {
          const res = await posFetchJson(sale.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(sale.payload)
          });
          if (res.status === 'success') {
            await MPOfflineDB.removeQueuedSale(sale.queueId);
            showToast('Offline sale synced', 'The queued sale was posted exactly once.', 'success');
          } else {
            await MPOfflineDB.updateSaleStatus(sale.queueId, 'failed', res.message || 'Server rejected sale');
          }
        } catch (err) {
          await MPOfflineDB.updateSaleStatus(sale.queueId, 'failed', posErrorText(err, 'Sync failed'));
        }
      }
    }
    window.addEventListener('online', () => setTimeout(syncQueuedDesktopSales, 500));
    setTimeout(syncQueuedDesktopSales, 800);

    // Pharmacy: pre-select the patient from the medical note
    if (medicalNoteCustomerId) {
      setTimeout(() => { selectCustomer(String(medicalNoteCustomerId)); }, 100);
    }
  })();

  </script>
<?php $this->load->view('idle_lock'); ?>
</body>
</html>
