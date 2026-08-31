<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — More</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 100%; margin: 0; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 16px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    button.back { background: transparent; border: none; padding: 0; font: inherit; cursor: pointer; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0;}
    .greeting { font-size: 13px; color: var(--mp-muted); margin: -4px 0 16px; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    [hidden] { display: none !important; }
    .menu-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .menu-card { width: 100%; background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; padding: 16px 10px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; text-align: center; min-height: 110px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.04); -webkit-tap-highlight-color: transparent; transition: transform .15s ease, box-shadow .15s ease; }
    .menu-card:active { transform: scale(0.96); }
    .group-bubble { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .group-bubble.blue { background: #EFF6FF; color: #2563EB; }
    .group-bubble.primary { background: #DBEAFE; color: #0057FF; }
    .group-bubble.green { background: #D1FAE5; color: #059669; }
    .group-bubble.orange { background: #FFEDD5; color: #EA580C; }
    .group-bubble.red { background: #FEF2F2; color: #DC2626; }
    .group-bubble.purple { background: #F3E8FF; color: #7C3AED; }
    .group-bubble.teal { background: #CCFBF1; color: #0F766E; }
    .group-bubble.yellow { background: #FFFBEB; color: #D97706; }
    .menu-card .label { font-size: 14px; font-weight: 600; color: var(--mp-ink); line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 100%; }
    .group-detail .menu-list { display: flex; flex-direction: column; background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .menu-item { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); text-decoration: none; color: var(--mp-ink); }
    .menu-item:last-child { border-bottom: none; }
    .menu-item .icon { width: 36px; height: 36px; border-radius: 10px; background: var(--mp-bg); color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .menu-item .icon.blue { background: #EFF6FF; color: #2563EB; }
    .menu-item .icon.primary { background: #DBEAFE; color: var(--mp-primary); }
    .menu-item .icon.green { background: #D1FAE5; color: #059669; }
    .menu-item .icon.orange { background: #FFEDD5; color: #EA580C; }
    .menu-item .icon.red { background: #FEF2F2; color: #DC2626; }
    .menu-item .icon.purple { background: #F3E8FF; color: #7C3AED; }
    .menu-item .icon.teal { background: #CCFBF1; color: #0F766E; }
    .menu-item .icon.yellow { background: #FFFBEB; color: #D97706; }
    .menu-item .text { flex: 1; min-width: 0; }
    .menu-item .title { font-weight: 600; font-size: 15px; }
    .menu-item .desc { font-size: 13px; color: var(--mp-muted); margin-top: 2px; }
    .menu-item .arrow { color: var(--mp-muted); flex-shrink: 0; }
    .menu-item.logout { color: var(--mp-danger); }
    .menu-item.logout .icon { background: #FEF2F2; color: var(--mp-danger); }
    @media (min-width: 400px) { .menu-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 600px) { .screen { padding: 16px 24px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen" id="menu-grid">
      <div class="topbar">
        <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>More</h1>
        </div>
      </div>
      <div class="greeting">Hello, <?= $display_name; ?></div>

      <div class="menu-grid">
        <?php
          $group_icons = [
            'Overview' => 'fa-home',
            'Sales' => 'fa-shopping-cart',
            'Purchase' => 'fa-cart-arrow-down',
            'Inventory' => 'fa-cubes',
            'Online Store' => 'fa-globe',
            'Marketing' => 'fa-ticket',
            'Customers' => 'fa-address-book',
            'Suppliers' => 'fa-truck',
            'Finance' => 'fa-money',
            'Reports' => 'fa-pie-chart',
            'Team' => 'fa-users',
            'Operations' => 'fa-cogs',
            'Account' => 'fa-user-circle',
          ];
          $group_colors = [
            'Overview' => 'blue',
            'Sales' => 'primary',
            'Purchase' => 'green',
            'Inventory' => 'teal',
            'Online Store' => 'purple',
            'Marketing' => 'red',
            'Customers' => 'purple',
            'Suppliers' => 'yellow',
            'Finance' => 'green',
            'Reports' => 'orange',
            'Team' => 'yellow',
            'Operations' => 'primary',
            'Account' => 'blue',
          ];
        ?>
        <?php foreach($menu_groups as $group => $items): ?>
          <?php $group_slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($group))); ?>
          <button type="button" class="menu-card" data-group="<?= $group_slug; ?>" onclick="openGroup(this)">
            <div class="group-bubble <?= $group_colors[$group] ?? 'primary'; ?>"><i class="fa <?= $group_icons[$group] ?? 'fa-folder'; ?>"></i></div>
            <div class="label"><?= $group; ?></div>
          </button>
        <?php endforeach; ?>
      </div>
    </section>

    <?php foreach($menu_groups as $group => $items): ?>
      <?php $group_slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($group))); ?>
      <section class="screen group-detail" id="group-<?= $group_slug; ?>" hidden>
        <div class="topbar">
          <button type="button" class="back" aria-label="Back" onclick="showMenuGrid()"><i class="fa fa-chevron-left"></i></button>
          <div class="topbar-titles">
            <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
            <h1><?= $group; ?></h1>
          </div>
        </div>

        <div class="menu-list">
          <?php foreach($items as $item): ?>
            <a href="<?= base_url($item['url']); ?>" class="menu-item <?= $item['title'] === 'Log Out' ? 'logout' : ''; ?>" <?php if($item['title'] === 'Log Out'){ ?>onclick="return mpLogout(this, event);"<?php } ?>>
              <div class="icon <?= $item['color']; ?>"><i class="fa <?= $item['icon']; ?>"></i></div>
              <div class="text">
                <div class="title"><?= $item['title']; ?></div>
                <div class="desc"><?= $item['desc']; ?></div>
              </div>
              <div class="arrow"><i class="fa fa-chevron-right"></i></div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php if($this->session->flashdata('warning')): ?>
    <?php $flash_warning = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('warning')))); ?>
    <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_warning, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'warning'});</script>
  <?php endif; ?>
  <?php if($this->session->flashdata('success')): ?>
    <?php $flash_success = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('success')))); ?>
    <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_success, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'success'});</script>
  <?php endif; ?>
  <?php if($this->session->flashdata('failed')): ?>
    <?php $flash_failed = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('failed')))); ?>
    <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_failed, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'danger'});</script>
  <?php endif; ?>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    function openGroup(card) {
      var slug = card.getAttribute('data-group');
      document.getElementById('menu-grid').hidden = true;
      document.querySelectorAll('.group-detail').forEach(function(el){ el.hidden = true; });
      var panel = document.getElementById('group-' + slug);
      if(panel) { panel.hidden = false; }
      if(window.scrollTo) { window.scrollTo(0,0); }
    }
    function showMenuGrid() {
      document.getElementById('menu-grid').hidden = false;
      document.querySelectorAll('.group-detail').forEach(function(el){ el.hidden = true; });
      if(window.scrollTo) { window.scrollTo(0,0); }
    }
  </script>
</body>
</html>
