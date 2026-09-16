<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title><?= htmlspecialchars($seo_title ?? 'My Courses'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <?php $primary = $settings->primary_color ?? '#7C3AED'; $primaryDark = $settings->primary_dark_color ?? '#6D28D9'; ?>
  <style>
    :root { --primary:<?= $primary;?>; --primary-dark:<?= $primaryDark;?>; --success:#059669; --success-light:#D1FAE5; --warning:#F59E0B; --danger:#EF4444; --dark:#0F172A; --gray:#64748B; --light-gray:#F1F5F9; --border:#E2E8F0; --white:#fff; --radius:16px; --radius-sm:10px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#F8FAFC; color:var(--dark); -webkit-font-smoothing:antialiased; }
    a { text-decoration:none; color:inherit; }
    img { max-width:100%; display:block; }
    .cra-topbar { background:var(--white); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100; }
    .cra-topbar-inner { max-width:1200px; margin:0 auto; padding:14px 24px; display:flex; align-items:center; gap:16px; }
    .cra-back { font-size:22px; color:var(--dark); display:flex; align-items:center; }
    .cra-topbar-logo { font-weight:800; font-size:16px; flex:1; display:flex; align-items:center; gap:10px; }
    .cra-topbar-logo img { max-height:32px; max-width:120px; object-fit:contain; }
    .cra-logout { padding:10px 18px; border-radius:var(--radius-sm); border:1px solid var(--border); background:var(--white); color:var(--dark); font-weight:600; font-size:13px; transition:background .15s; }
    .cra-logout:hover { background:var(--light-gray); }
    .cra-layout { max-width:1200px; margin:0 auto; padding:32px 24px; display:grid; grid-template-columns:260px 1fr; gap:32px; }
    .cra-sidebar { display:flex; flex-direction:column; gap:14px; }
    .cra-sidebar-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius); padding:24px; text-align:center; }
    .cra-avatar { width:72px; height:72px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:800; margin:0 auto 14px; }
    .cra-name { font-size:18px; font-weight:800; margin-bottom:4px; }
    .cra-email { font-size:13px; color:var(--gray); word-break:break-word; }
    .cra-phone { font-size:13px; color:var(--gray); margin-top:4px; }
    .cra-menu { background:var(--white); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; }
    .cra-menu a { display:flex; align-items:center; gap:12px; padding:14px 18px; font-size:14px; font-weight:600; border-bottom:1px solid var(--light-gray); transition:background .15s; }
    .cra-menu a:last-child { border-bottom:none; }
    .cra-menu a:hover, .cra-menu a.active { background:#F8FAFF; color:var(--primary); }
    .cra-menu svg { width:20px; height:20px; color:var(--gray); }
    .cra-main { display:flex; flex-direction:column; gap:24px; }
    .cra-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius); padding:24px; }
    .cra-card-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .cra-card-title { font-size:18px; font-weight:800; display:flex; align-items:center; gap:10px; }
    .cra-card-title svg { width:20px; height:20px; color:var(--primary); }
    .cra-card-link { font-size:13px; font-weight:700; color:var(--primary); }
    .cra-course { display:flex; align-items:center; gap:16px; padding:18px 0; border-bottom:1px solid var(--light-gray); }
    .cra-course:last-child { border-bottom:none; padding-bottom:0; }
    .cra-course:first-child { padding-top:0; }
    .cra-course-icon { width:48px; height:48px; border-radius:12px; background:rgba(124,58,237,.1); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:20px; }
    .cra-course-info { flex:1; min-width:0; }
    .cra-course-name { font-size:15px; font-weight:800; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .cra-course-progress { height:8px; background:var(--light-gray); border-radius:999px; overflow:hidden; margin-top:6px; }
    .cra-course-progress > div { height:100%; background:var(--primary); border-radius:999px; }
    .cra-course-meta { font-size:12px; color:var(--gray); }
    .cra-course-btn { padding:10px 18px; border-radius:var(--radius-sm); background:var(--primary); color:#fff; font-weight:700; font-size:13px; }
    .cra-empty { padding:40px 24px; text-align:center; color:var(--gray); font-size:14px; background:var(--light-gray); border-radius:var(--radius-sm); }
    .cra-footer { text-align:center; font-size:12px; color:var(--gray); margin-top:20px; padding:20px 0; }
    @media(max-width:900px){
      .cra-layout { grid-template-columns:1fr; padding:24px 16px; }
      .cra-sidebar { display:none; }
      .cra-topbar-inner { padding:14px 16px; }
      .cra-course { align-items:flex-start; }
      .cra-course-btn { align-self:center; }
    }
  </style>
</head>
<body>

<div class="cra-topbar">
  <div class="cra-topbar-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/account'); ?>" class="cra-back">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </a>
    <div class="cra-topbar-logo">
      <?php if(!empty($settings->store_logo) && file_exists($settings->store_logo)): ?>
        <img src="<?= base_url($settings->store_logo); ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php else: ?>
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--primary);"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      <?php endif; ?>
      <?= htmlspecialchars($store->store_name ?? 'Store'); ?> Portal
    </div>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/account/logout'); ?>" class="cra-logout">Log Out</a>
  </div>
</div>

<div class="cra-layout">
  <aside class="cra-sidebar">
    <div class="cra-sidebar-card">
      <div class="cra-avatar"><?= !empty($customer->customer_name) ? htmlspecialchars(strtoupper(substr($customer->customer_name,0,1))) : 'U'; ?></div>
      <div class="cra-name"><?= htmlspecialchars($customer->customer_name ?? 'Customer'); ?></div>
      <?php if(!empty($customer->email)): ?><div class="cra-email"><?= htmlspecialchars($customer->email); ?></div><?php endif; ?>
      <div class="cra-phone"><?= htmlspecialchars($customer->mobile ?? ''); ?></div>
    </div>
    <nav class="cra-menu">
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/account'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> Dashboard</a>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/my_courses'); ?>" class="active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg> My Courses</a>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/account/orders'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> Orders</a>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/track'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Track Order</a>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg> Browse Store</a>
    </nav>
  </aside>

  <main class="cra-main">
    <div class="cra-card">
      <div class="cra-card-header">
        <div class="cra-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
          My Courses
        </div>
        <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="cra-card-link">Browse more</a>
      </div>

      <?php if(!empty($courses)): ?>
        <?php foreach($courses as $c): ?>
        <div class="cra-course">
          <div class="cra-course-icon">&#127891;</div>
          <div class="cra-course-info">
            <div class="cra-course-name"><?= htmlspecialchars($c['course']->title ?? ''); ?></div>
            <div class="cra-course-progress"><div style="width:<?= (int) $c['progress']; ?>%"></div></div>
            <div class="cra-course-meta"><?= (int) $c['progress']; ?>% complete</div>
          </div>
          <a href="<?= $c['url']; ?>" class="cra-course-btn">Continue</a>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="cra-empty">You are not enrolled in any courses yet.</div>
      <?php endif; ?>
    </div>

    <div class="cra-footer">
      &copy; <?= date('Y'); ?> <?= htmlspecialchars($store->store_name ?? 'Store'); ?>. All rights reserved.
    </div>
  </main>
</div>

</body>
</html>
