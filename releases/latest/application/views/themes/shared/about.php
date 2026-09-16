<?php
$slug = $settings->store_slug ?? '';
$logo = $logo_url ?? null;
$about = nl2br(htmlspecialchars($about_content ?? ''));
?>
<style>
  .mp-about-section { max-width:800px; margin:0 auto; padding:80px 24px 96px; text-align:center; }
  .mp-about-logo { max-height:64px; max-width:200px; margin:0 auto 32px; }
  .mp-about-title { font-size:32px; font-weight:700; margin-bottom:16px; color:var(--mp-dark); }
  .mp-about-subtitle { font-size:15px; color:var(--mp-gray); margin-bottom:40px; }
  .mp-about-content { font-size:17px; line-height:1.8; color:var(--mp-dark); text-align:left; }
  .mp-about-content p { margin-bottom:16px; }
  .mp-about-actions { margin-top:40px; }
  .mp-about-btn { display:inline-block; padding:12px 28px; background:var(--mp-primary); color:#fff; border-radius:var(--mp-radius); font-weight:600; }
  @media(max-width:767px){
    .mp-about-section { padding:48px 16px 64px; }
    .mp-about-title { font-size:24px; }
    .mp-about-content { font-size:15px; }
  }
</style>

<section class="mp-about-section">
  <?php if($logo): ?>
    <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>" class="mp-about-logo">
  <?php endif; ?>
  <h1 class="mp-about-title">About <?= htmlspecialchars($store->store_name ?? 'Us'); ?></h1>
  <div class="mp-about-subtitle"><?= htmlspecialchars($settings->store_subheadline ?? ''); ?></div>
  <div class="mp-about-content"><?= $about; ?></div>
  <div class="mp-about-actions">
    <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mp-about-btn">Shop Now</a>
  </div>
</section>
