<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(get_store_name() ?: 'MartPoint') ?> — Point of Sale</title>
  <link rel="icon" href="<?= $favicon_url ?>">
  <link rel="apple-touch-icon" href="<?= $favicon_url ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --mp-primary: #0057FF;
      --mp-primary-dark: #0044CC;
      --mp-pay: #D97706;
      --mp-pay-dark: #B45309;
      --mp-bg: #F5F4F0;
      --mp-surface: #FFFFFF;
      --mp-text: #292524;
      --mp-muted: #78716C;
      --mp-border: #E7E5E4;
      --mp-success: #059669;
      --mp-danger: #DC2626;
      --mp-warning: #F59E0B;
      --mp-ink: #44403C;
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
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: var(--mp-bg);
      color: var(--mp-text);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

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
      overflow: hidden;
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
      padding: 10px 14px;
      font-size: 14px;
      cursor: pointer;
      border-bottom: 1px solid var(--mp-border);
    }
    .customer-option:last-child { border-bottom: none; }
    .customer-option:hover, .customer-option.active { background: var(--mp-primary); color: #fff; }
    .customer-option.hidden { display: none; }
    .customer-select select { display: none; }
    .cart-items {
      flex: 1;
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
      .category-list { display: flex; gap: 8px; }
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
    @media (min-width: 1101px) and (max-width: 1400px) and (max-height: 800px) {
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
  </style>
</head>
<body>

  <header class="app-header">
    <a href="<?= base_url() ?>" class="brand" style="text-decoration: none; color: inherit; cursor: pointer;">
      <h1><?= htmlspecialchars(get_store_name() ?: 'Metro Mart') ?></h1>
      <div class="sub">Point of Sale</div>
    </a>
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
      <a class="header-btn" href="#">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
        Sales List
      </a>
      <?php $CI =& get_instance(); if($CI->permissions('customers_add')): ?>
      <button class="header-btn" onclick="openAddCustomer()">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
      Add Customer
    </button>
    <?php endif; ?>
    <button class="header-btn" id="themeBtn" onclick="toggleTheme()" style="color: var(--mp-text);">
      <span id="themeIcon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></span>
      <span id="themeLabel" style="color: var(--mp-text);">Dark</span>
    </button>
    <button class="header-btn" id="clockBtn" onclick="toggleClock()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span id="clockBtnText">Clock In</span>
      </button>
      <button class="header-btn primary" onclick="confirmResetCart()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Invoice
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
      <div class="panel">
        <div class="panel-title">Categories</div>
        <ul class="category-list" id="categoryList"></ul>
      </div>
      <div class="panel">
        <div class="panel-title" style="padding-bottom:12px">Top Sellers This Week</div>
        <div class="insight-list" id="insightList"></div>
      </div>
      <div class="panel" style="padding:16px">
        <div class="panel-title" style="padding:0 0 10px">Today's Sales Target</div>
        <div class="target-amount" id="todaySalesText">₦18,400.00</div>
        <div class="target-meta"><span>of ₦50,000.00</span><span id="targetPercent">37%</span></div>
        <div class="progress-track">
          <div class="progress-fill" id="targetFill" style="width:37%"></div>
        </div>
      </div>
      <div class="panel" style="padding:16px">
        <div class="panel-title" style="padding:0 0 8px">Invoice</div>
        <div style="font-size:24px;font-weight:700;color:var(--mp-primary)">#<?= $init_code ?><?= str_pad($count_id, 4, '0', STR_PAD_LEFT) ?></div>
        <div style="font-size:12px;color:var(--mp-muted);margin-bottom:12px">Next invoice number</div>
        <div id="posClock" style="font-size:22px;font-weight:700;color:var(--mp-ink);font-variant-numeric:tabular-nums">--:--:--</div>
        <div style="font-size:11px;color:var(--mp-muted)">Local Time</div>
      </div>
    </aside>

    <section class="catalog">
      <div class="catalog-header">
        <div class="search-bar">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" id="searchInput" placeholder="Scan barcode or search product...">
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
      <div class="products" id="productGrid"></div>
    </section>

    <aside class="cart">
      <div class="cart-header">
        <h2>Current Sale</h2>
        <div class="price-type">
          <span class="price-type-label">Price Type</span>
          <div class="option-chips">
            <button id="retailBtn" class="active" onclick="setPriceType('retail')">Retail</button>
            <button id="wholesaleBtn" onclick="setPriceType('wholesale')">Wholesale</button>
          </div>
        </div>
        <div class="customer-select">
          <div id="customerNameDisplay" style="font-size:14px;font-weight:600;color:var(--mp-ink);padding:8px 0;">Walk-in Customer</div>
          <div id="customerBalanceInfo" style="display:none;font-size:12px;font-weight:500;padding:2px 0 6px;"></div>
          <input type="text" id="customerSearchInput" class="customer-search" placeholder="Search customer..." autocomplete="off" oninput="filterCustomerDropdown()" onkeydown="handleCustomerSearchKey(event)" onfocus="showCustomerDropdown()" onblur="setTimeout(function(){ autoSelectCustomer(); hideCustomerDropdown(); }, 200)">
          <div id="customerDropdown" class="customer-dropdown" style="display:none">
            <?php if(!empty($customers) && is_array($customers)): ?>
              <?php foreach($customers as $c): ?>
                <div class="customer-option" data-id="<?= $c['id'] ?? $c->id ?>" data-name="<?= htmlspecialchars($c['customer_name'] ?? $c->customer_name) ?>" onmousedown="selectCustomer('<?= $c['id'] ?? $c->id ?>')"><?= htmlspecialchars($c['customer_name'] ?? $c->customer_name) ?><?= !empty($c['mobile']) ? ' (' . htmlspecialchars($c['mobile']) . ')' : '' ?></div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="customer-option" data-id="" data-name="Walk-in Customer" onmousedown="selectCustomer('')">Walk-in Customer</div>
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
        </div>
      </div>
      <div class="cart-items" id="cartItems">
        <div class="cart-empty">Tap a product to start selling.</div>
      </div>
      <div class="cart-summary">
        <div class="summary-row"><span>Subtotal</span><span id="subtotal">₦0.00</span></div>
        <div class="summary-row" id="taxRow" style="display:none"><span id="taxLabel">Tax</span><span id="tax">₦0.00</span></div>
        <div class="summary-row" id="discountRow" style="display:none"><span>Discount</span><span id="discount">₦0.00</span></div>
        <div class="summary-row total"><span>Total</span><span id="grandTotal">₦0.00</span></div>
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

  <script>
    const products = <?= json_encode($products ?? []) ?>;
    const baseUrl = '<?= rtrim(base_url(), '/') ?>';
    const POS_DECIMALS = <?= (int)decimals() ?>;
    const WAREHOUSE_ID = '<?= $warehouse_id ?>';
    const WALKIN_CUSTOMER_ID = '<?= $walkin_customer_id ?>';
    const csrfToken = '<?= $this->security->get_csrf_hash() ?>';

    const topSelling = <?= json_encode($top_selling ?? []) ?>;

    const POINTS_VALUE = 500;
    const POINTS_COST = 200;
    const dailyTarget = <?= (float)($daily_target ?? 50000) ?>;
    let todaySales = <?= (float)($today_sales ?? 0) ?>;
    let selectedCategory = 'All';
    let priceType = 'retail';
    let searchQuery = '';
    let productView = localStorage.getItem('posProductView') || 'grid';
    let discountAmount = 0;
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
    let clockedIn = <?= json_encode((bool)(!$needs_clock_in)) ?>;
    let clockedAt = '<?= $clock_in_time ?>';
    let clockInTime = '<?= $clock_in_time ?>';
    let clockImage = null;
    let clockPinMode = false;
    let clockStream = null;
    const cart = [];
    let cartSubtotal = 0;
    let cartTax = 0;
    const customers = <?= json_encode($customers ?? []) ?>;
    const customerRedeemables = <?= json_encode($customer_redeemables ?? (object)[]) ?>;
    const customerNameMap = <?= json_encode(array_column($customers ?? [], 'customer_name', 'id')) ?>;
    const loyaltySettings = <?= json_encode($loyalty_settings ?? (object)['redemption_rate' => 0]) ?>;
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

    function getPrice(product) {
      return priceType === 'wholesale' ? product.wholesale : product.price;
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
        renderProducts();
      });
    }

    function getCategories() {
      const map = {};
      products.forEach(p => { map[p.category] = (map[p.category] || 0) + 1; });
      return [{ name: 'All', count: products.length }, ...Object.entries(map).map(([name, count]) => ({ name, count }))];
    }

    function renderCategories() {
      const list = document.getElementById('categoryList');
      list.innerHTML = getCategories().map(c => `
        <li class="${c.name === selectedCategory ? 'active' : ''}" onclick="setCategory('${c.name}')">
          <span>${c.name}</span>
          <span class="count">${c.count}</span>
        </li>
      `).join('');
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
            <span class="name">${t.name}</span>
            <span class="sold">${t.sold} sold</span>
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
          ${t.name} — ${t.sold} sold this week
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
      document.getElementById('retailBtn').className = type === 'retail' ? 'active' : '';
      document.getElementById('wholesaleBtn').className = type === 'wholesale' ? 'active' : '';
      cart.forEach(item => { item.unitPrice = getPrice(item); });
      updateCart();
      renderProducts();
      showToast('Price type changed', 'Switched to ' + type + ' prices', 'success');
    }

    function renderProducts() {
      const grid = document.getElementById('productGrid');
      const filtered = products.filter(p => {
        const matchesCategory = selectedCategory === 'All' || p.category === selectedCategory;
        const matchesSearch = p.name.toLowerCase().includes(searchQuery);
        return matchesCategory && matchesSearch;
      });

      grid.classList.toggle('list-mode', productView === 'list');

      if (productView === 'list') {
        grid.innerHTML = filtered.map(p => {
          const displayPrice = getPrice(p);
          const stockChip = p.outOfStock
            ? '<span class="stock-chip out">Out of stock</span>'
            : (p.alert_qty > 0 && p.stock <= p.alert_qty
              ? `<span class="stock-chip low">Low · ${p.stock}${p.unit ? ' ' + p.unit : ''}</span>`
              : `<span class="stock-chip in">${p.stock}${p.unit ? ' ' + p.unit : ''} in stock</span>`);
          const rowThumb = p.image
            ? `<div class="row-thumb"><img src="${baseUrl}/${p.image}" alt="" onerror="this.remove();"></div>`
            : `<div class="row-thumb"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg></div>`;
          const meta = [p.category, p.unit, p.sku].filter(Boolean).join(' · ');
          return `
          <div class="product-row${p.outOfStock ? ' out' : ''}" onclick="${p.outOfStock ? '' : 'addToCart(' + p.id + ')'}">
            ${rowThumb}
            <div class="row-info">
              <div class="row-name">${p.name}</div>
              <div class="row-meta">${meta}</div>
            </div>
            ${stockChip}
            <div class="row-price">${formatMoney(displayPrice)}</div>
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
        const wholesaleTag = priceType === 'wholesale' ? '<div class="price-tag">WS</div>' : '';
        const outOfStockOverlay = p.outOfStock ? '<div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);border-radius:14px;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-weight:700;font-size:12px;">OUT OF STOCK</span></div>' : '';
        const thumb = p.image ? `<div class="product-thumb" style="padding:0;position:relative;"><img src="${baseUrl}/${p.image}" style="width:100%;height:100%;object-fit:cover;border-radius:14px;" onerror="this.remove();">${outOfStockOverlay}</div>` : `<div class="product-thumb"><svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg></div>`;
        const cardClass = p.outOfStock ? 'product-card' : 'product-card';
        const cardStyle = p.outOfStock ? 'opacity: 0.6; cursor: not-allowed;' : '';
        return `
        <div class="${cardClass}" style="${cardStyle}" onclick="${p.outOfStock ? '' : 'addToCart(' + p.id + ')'}">
          ${newTag}
          ${wholesaleTag}
          ${thumb}
          <div class="product-name">${p.name}</div>
          <div class="product-price">${formatMoney(displayPrice)}</div>
          <button class="add-btn" ${p.outOfStock ? 'disabled' : ''} onclick="event.stopPropagation(); addToCart(${p.id})">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
        </div>
      `;}).join('');
    }

    function setProductView(mode) {
      productView = mode === 'list' ? 'list' : 'grid';
      localStorage.setItem('posProductView', productView);
      document.getElementById('gridViewBtn').classList.toggle('active', productView === 'grid');
      document.getElementById('listViewBtn').classList.toggle('active', productView === 'list');
      renderProducts();
    }

    function addToCart(id) {
      const product = products.find(p => p.id === id);
      if (!product) return;
      if (product.outOfStock) {
        showToast('Out of stock', product.name + ' is not available', 'warning');
        return;
      }
      const unitPrice = getPrice(product);
      const item = cart.find(c => c.id === id);
      if (item) {
        item.qty++;
      } else {
        cart.push({ ...product, unitPrice: unitPrice, qty: 1 });
      }
      showToast('Added to cart', product.name, 'success');
      updateCart();
    }

    function changeQty(id, delta) {
      const item = cart.find(c => c.id === id);
      if (!item) return;
      item.qty += delta;
      if (item.qty < 1) {
        const idx = cart.indexOf(item);
        cart.splice(idx, 1);
      }
      updateCart();
    }

    function removeItem(id) {
      const idx = cart.findIndex(c => c.id === id);
      if (idx > -1) cart.splice(idx, 1);
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
      document.getElementById('discountInput').value = '';
      document.getElementById('discountInput').disabled = true;
      clearCouponCode();
      priceType = 'retail';
      document.getElementById('retailBtn').className = 'active';
      document.getElementById('wholesaleBtn').className = '';
      updateCart();
      showToast('New invoice', 'Cart cleared for the next customer', 'success');
    }

    function updateClockBtn() {
      const btn = document.getElementById('clockBtn');
      const text = document.getElementById('clockBtnText');
      const dot = document.getElementById('clockDot');
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
          showToast('Camera error', 'Could not access camera: ' + (err.message || err), 'warning');
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
        
        fetch(url, { 
          method: 'POST', 
          body: params,
          headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
          }
        })
          .then(r => {
            if (!r.ok) throw new Error('Server error: ' + r.status);
            return r.text();
          })
          .then(text => {
            confirmBtn.disabled = false;
            try {
              const res = JSON.parse(text);
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
                notice.textContent = res.message || 'Clock action failed';
                showToast('Clock failed', res.message || 'Clock action failed', 'danger');
              }
            } catch (e) {
              console.error('Clock response parse error:', text, e);
              notice.textContent = 'Invalid server response. Please try again.';
              showToast('Clock error', 'Invalid server response', 'danger');
            }
          })
          .catch(err => {
            confirmBtn.disabled = false;
            console.error('Clock error:', err);
            notice.textContent = 'Network or server error. Please try again.';
            showToast('Clock error', 'Network or server error', 'danger');
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
        itemsContainer.innerHTML = '<div class="cart-empty">Tap a product to start selling.</div>';
      } else {
        itemsContainer.innerHTML = cart.map(item => {
          const taxValue = (item.tax_value || 0) / 100;
          const taxType = item.tax_type || 'Exclusive';
          let displayPrice = item.unitPrice;
          let displayTotal = item.unitPrice * item.qty;
          
          // For inclusive tax, show the price without tax
          if (taxType === 'Inclusive' && taxValue > 0) {
            displayPrice = item.unitPrice / (1 + taxValue);
            displayTotal = displayPrice * item.qty;
          }
          
          return `
          <div class="cart-item">
            <div class="item-info">
              <div class="item-name">${item.name}</div>
              <div class="item-meta">${formatMoney(displayPrice)} each • ${priceType === 'wholesale' ? 'Wholesale' : 'Retail'}</div>
            </div>
            <div class="qty-control">
              <button onclick="changeQty(${item.id}, -1)">-</button>
              <span>${item.qty}</span>
              <button onclick="changeQty(${item.id}, 1)">+</button>
            </div>
            <div class="item-total">${formatMoney(displayTotal)}</div>
            <button class="remove-btn" onclick="removeItem(${item.id})">
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
        const lineTotal = i.unitPrice * i.qty;
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
      const total = Math.max(0, subtotal + tax - totalDiscount);

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
      const canPay = cart.length > 0 && clockedIn;
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
      document.getElementById('clockNotice').style.display = cart.length > 0 && !clockedIn ? 'flex' : 'none';
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
      params.append('cart_subtotal', cart.reduce((s, i) => s + (i.unitPrice * i.qty), 0));
      params.append('csrf_test_name', csrfToken);
      fetch(baseUrl + '/customer_coupon/get_coupon_details', {
        method: 'POST',
        body: params,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' }
      })
        .then(r => { if(!r.ok) throw new Error('Server error: ' + r.status); return r.text(); })
        .then(text => {
          let data;
          try { data = JSON.parse(text); } catch(e) { throw new Error('Invalid response'); }
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
        .catch(() => {
          clearCouponCode();
          showToast('Error', 'Could not validate coupon. Please try again.', 'danger');
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
            eligibleSubtotal += i.unitPrice * i.qty;
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
          if (!r.ok) throw new Error('Failed to load receipt');
          return r.text();
        })
        .then(html => {
          receiptContent.innerHTML = html;
        })
        .catch(err => {
          console.error('Receipt load error:', err);
          receiptContent.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--mp-danger);">Failed to load receipt. Please try again.</div>';
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
        openAlertModal('Hold failed', 'No warehouse found for this store. Please set up a warehouse first.');
        return;
      }
      const payload = {
        warehouse_id: warehouseId,
        customer_id: parseInt(document.getElementById('customerSelect').value) || 0,
        cart: cart.map(item => ({
          id: item.id,
          qty: item.qty,
          price: item.unitPrice,
          tax_value: item.tax_value || 0,
          tax_type: item.tax_type || 'Exclusive',
          price_type: priceType,
          tax_id: item.tax_id || 0
        })),
        discount: discountAmount || 0,
        sales_note: '',
        reference_id: reference,
        csrf_test_name: csrfToken
      };

      const submitBtn = document.getElementById('holdSubmitBtn');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Holding...';

      fetch(baseUrl + '/mobile/hold', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.text();
      })
      .then(text => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Hold';
        try {
          const res = JSON.parse(text);
          if (res.status === 'success') {
            closeHoldModal();
            const itemCount = cart.length;
            const msg = itemCount + ' item' + (itemCount !== 1 ? 's' : '') + ' held successfully';
            showToast('Sale Held', msg, 'success');
            resetCart();
            setTimeout(() => window.location.reload(), 1000);
          } else {
            openAlertModal('Hold failed', res.message || 'Could not hold sale. Make sure products are in stock and a warehouse is set.');
          }
        } catch (e) {
          console.error('Hold response parse error:', text, e);
          openAlertModal('Hold failed', 'Invalid server response. Please try again.');
        }
      })
      .catch(err => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Hold';
        console.error('Hold error:', err);
        openAlertModal('Hold failed', 'Network or server error. Please try again.');
      });
    }

    function loadHold(holdId) {
      if (!holdId) return;
      const doLoad = () => {
        fetch(baseUrl + '/mobile/get_hold/' + holdId, {
          method: 'GET',
          headers: { 'Accept': 'application/json' }
        })
          .then(r => {
            if (!r.ok) throw new Error('Server error: ' + r.status);
            return r.text();
          })
          .then(text => {
            try {
              const hold = JSON.parse(text);
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
              discountAmount = parseFloat(hold.discount) || 0;
              document.getElementById('discountInput').value = discountAmount;
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
              console.error('Hold response parse error:', text, e);
              openAlertModal('Hold error', 'Invalid server response. Please try again.');
            }
          })
          .catch(err => {
            console.error('Load hold error:', err);
            openAlertModal('Network error', 'Could not load hold. Check connection and try again.');
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
        fetch(baseUrl + '/pos/hold_invoice_delete/' + holdId, {
          method: 'GET',
          headers: { 'Accept': 'text/plain' }
        })
          .then(r => {
            if (!r.ok) throw new Error('Server error: ' + r.status);
            return r.text();
          })
          .then(res => {
            const trimmed = res.trim().toLowerCase();
            console.log('Delete response:', trimmed);
            if (trimmed === 'success') {
              showToast('Hold deleted', 'Invoice removed from holds', 'success');
              // Remove the hold from the table immediately
              const row = document.querySelector(`tr[data-hold-id="${holdId}"]`);
              if (row) row.remove();
              // Refresh the hold list
              openHoldPopup();
            } else {
              openAlertModal('Delete failed', res || 'Could not delete hold.');
            }
          })
          .catch(err => {
            console.error('Delete hold error:', err);
            openAlertModal('Network error', 'Could not delete hold. Check connection and try again.');
          });
      });
    }

    function updateTarget() {
      const pct = Math.min(100, Math.round((todaySales / dailyTarget) * 100));
      document.getElementById('todaySalesText').textContent = formatMoney(todaySales);
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
      
      fetch(baseUrl + '/customers/add_customer_ajax', {
        method: 'POST',
        body: formData
      })
      .then(r => {
        if (!r.ok) throw new Error('Server error: ' + r.status);
        return r.text();
      })
      .then(text => {
        try {
          const res = JSON.parse(text);
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
        } catch (e) {
          console.error('Customer save error:', text, e);
          showToast('Error', 'Invalid server response', 'danger');
          saveBtn.disabled = false;
          saveBtn.textContent = 'Save Customer';
        }
      })
      .catch(err => {
        console.error('Customer save error:', err);
        showToast('Network error', 'Could not save customer. Please try again.', 'danger');
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
  </script>
  <?php $this->load->view('pos/components/hold_popup'); ?>
  <?php $this->load->view('pos/components/sales_list_popup'); ?>
  <?php $this->load->view('pos/components/payment_popup'); ?>
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
      fetch(baseUrl + '/pos/get_holds_ajax', {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
      })
        .then(r => {
          if (!r.ok) throw new Error('Server error: ' + r.status);
          return r.text();
        })
        .then(text => {
          let data;
          if (!text || !text.trim()) {
            console.error('Hold list parse error: empty response from server');
            throw new Error('Invalid server response');
          }
          try {
            data = JSON.parse(text);
          } catch (e) {
            console.error('Hold list parse error:', text, e);
            throw new Error('Invalid server response');
          }
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
          console.error('Failed to load holds:', err);
          const tbody = document.querySelector('#holdPopup table tbody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" style="padding: 24px; text-align: center; color: var(--mp-danger);">Failed to load holds. Please try again.</td></tr>';
          }
        });
    };

    window.openSalesListPopup = function() {
      closeAllPopups();
      document.getElementById('salesListPopup').classList.add('active');
      const tbody = document.getElementById('salesListBody');
      tbody.innerHTML = '<tr><td colspan="4" style="padding: 24px; text-align: center; color: var(--mp-muted);">Loading sales...</td></tr>';
      
      // Fetch today's sales
      fetch(baseUrl + '/pos/get_todays_sales_ajax', {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
      })
        .then(r => {
          if (!r.ok) throw new Error('Server error: ' + r.status);
          return r.text();
        })
        .then(text => {
          let data;
          try {
            data = JSON.parse(text);
          } catch (e) {
            console.error('Sales list parse error:', text, e);
            throw new Error('Invalid server response');
          }
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
          console.error('Failed to load sales:', err);
          tbody.innerHTML = '<tr><td colspan="4" style="padding: 24px; text-align: center; color: var(--mp-danger);">Failed to load sales. Please try again.</td></tr>';
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
        price: item.unitPrice,
        tax_value: item.tax_value || 0,
        tax_type: item.tax_type || 'Exclusive',
        price_type: priceType,
        tax_id: item.tax_id || 0
      }));
      const payload = {
        command: 'save',
        action: action,
        customer_id: parseInt(document.getElementById('customerSelect').value) || 0,
        warehouse_id: WAREHOUSE_ID ? parseInt(WAREHOUSE_ID) : 0,
        cart: cartItems,
        discount: discountAmount || 0,
        sales_note: '',
        sales_status: 'Final',
        coupon_code: couponCode || '',
        coupon_discount_amt: couponDiscountAmt || 0,
        amount_paid: paid,
        payment_type: paymentRows[0] ? paymentRows[0].payment_type : window.defaultPaymentMode,
        account_id: paymentRows[0] ? paymentRows[0].account_id : '',
        payment_rows: paymentRows,
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
      fetch(baseUrl + '/mobile/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(r => {
        if (!r.ok) throw new Error('Server error: ' + r.status);
        return r.text();
      })
      .then(text => {
        submitBtn.disabled = false;
        submitBtn.textContent = mode === 'plan' ? 'Create Plan' : 'Pay';
        try {
          const res = JSON.parse(text);
          if (res.status === 'success') {
            closePaymentPopup();
            const salesId = res.sales_id;
            const msg = res.message || 'Sale saved';
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
            openAlertModal('Payment failed', res.message || 'Could not save sale.');
          }
        } catch (e) {
          console.error('Payment response parse error:', text, e);
          openAlertModal('Payment error', 'Invalid server response. Please try again.');
        }
      })
      .catch(err => {
        submitBtn.disabled = false;
        submitBtn.textContent = mode === 'plan' ? 'Create Plan' : 'Pay';
        console.error('Payment error:', err);
        openAlertModal('Network error', 'Could not save sale. Please try again.');
      });
    };
  })();

  </script>
</body>
</html>
