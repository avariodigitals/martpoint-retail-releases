<?php
$slug = $settings->store_slug ?? '';
?>
<style>
  .mp-branches { max-width:1000px; margin:0 auto; padding:72px 24px 88px; }
  .mp-branches-head { text-align:center; margin-bottom:40px; }
  .mp-branches-title { font-size:32px; font-weight:700; color:var(--mp-dark); margin:0 0 10px; }
  .mp-branches-sub { font-size:15px; color:var(--mp-gray); margin:0; }
  .mp-branches-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:20px; }
  .mp-branch-card { background:#fff; border:1px solid var(--mp-border,#e5e7eb); border-radius:var(--mp-radius,12px); padding:26px; transition:box-shadow .2s, transform .2s; }
  .mp-branch-card:hover { box-shadow:0 8px 24px rgba(0,0,0,.07); transform:translateY(-2px); }
  .mp-branch-name { font-size:18px; font-weight:700; color:var(--mp-dark); margin-bottom:6px; }
  .mp-branch-type { display:inline-block; font-size:11px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:var(--mp-primary); background:var(--mp-primary-light,#eef2ff); padding:4px 10px; border-radius:999px; margin-bottom:14px; }
  .mp-branch-detail { display:flex; align-items:center; gap:10px; font-size:14px; color:var(--mp-gray); margin-top:10px; }
  .mp-branch-detail svg { width:16px; height:16px; flex-shrink:0; stroke:currentColor; fill:none; stroke-width:2; }
  .mp-branch-detail a { color:var(--mp-dark); text-decoration:none; font-weight:500; }
  .mp-branch-detail a:hover { color:var(--mp-primary); }
  .mp-branch-btn { display:inline-flex; align-items:center; gap:8px; margin-top:18px; padding:10px 22px; background:var(--mp-primary); color:#fff; border-radius:var(--mp-radius,10px); font-size:13px; font-weight:600; text-decoration:none; }
  .mp-branches-empty { text-align:center; padding:60px 20px; color:var(--mp-gray); }
  .mp-branches-empty p { font-size:15px; margin-bottom:22px; }
  @media(max-width:767px){
    .mp-branches { padding:44px 16px 64px; }
    .mp-branches-title { font-size:24px; }
  }
</style>

<section class="mp-branches">
  <div class="mp-branches-head">
    <h1 class="mp-branches-title">Our Branches</h1>
    <p class="mp-branches-sub">Find the <?= htmlspecialchars($store->store_name ?? 'store'); ?> location nearest to you.</p>
  </div>

  <?php if(!empty($branches)): ?>
    <div class="mp-branches-grid">
      <?php foreach($branches as $b): ?>
      <div class="mp-branch-card">
        <div class="mp-branch-name"><?= htmlspecialchars($b->warehouse_name); ?></div>
        <?php if(!empty($b->branch_type)): ?>
          <span class="mp-branch-type"><?= htmlspecialchars($b->branch_type); ?></span>
        <?php endif; ?>
        <?php if(!empty($b->mobile)): ?>
          <div class="mp-branch-detail">
            <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $b->mobile); ?>"><?= htmlspecialchars($b->mobile); ?></a>
          </div>
        <?php endif; ?>
        <?php if(!empty($b->email)): ?>
          <div class="mp-branch-detail">
            <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <a href="mailto:<?= htmlspecialchars($b->email); ?>"><?= htmlspecialchars($b->email); ?></a>
          </div>
        <?php endif; ?>
        <?php if($settings->allow_services ?? false): ?>
          <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="mp-branch-btn">Book a Service</a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="mp-branches-empty">
      <p>No branch locations are listed at the moment.</p>
      <a href="<?= base_url('store/' . $slug); ?>" class="mp-branch-btn">Back to home</a>
    </div>
  <?php endif; ?>
</section>
