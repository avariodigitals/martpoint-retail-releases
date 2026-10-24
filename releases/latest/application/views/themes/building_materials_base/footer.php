<?php
  require_once APPPATH . 'helpers/storefront_footer_helper.php';
  $bmFooterLogo = mp_storefront_footer_logo_url($settings ?? null, $logo_url ?? '');
$showMpPoweredBy = mp_storefront_show_powered_by($settings ?? null);
  $bmFootBrand = trim((string)($bm_brand ?? ($store->store_name ?? '')));
  $bmFootMark = $bm_skin === 'vector' ? '/' : '.';
  $bmFootPhone = trim((string)($settings->store_phone ?? ''));
  $bmFootEmail = trim((string)($settings->store_email ?? ''));
  $bmFootAddress = trim((string)($settings->store_address ?? ''));
  $bmFootYear = date('Y');
  $bmFootSocial = [];
  foreach(['facebook','instagram','x','youtube','tiktok','linkedin'] as $bmNetwork){
      $bmUrl = trim((string)($social_links[$bmNetwork] ?? ''));
      if($bmUrl !== '' && preg_match('~^https?://~i', $bmUrl)) $bmFootSocial[$bmNetwork] = $bmUrl;
  }
?>
<footer class="bm-footer">
  <div class="footer-main">
    <div>
      <p class="eyebrow inverse"><?= htmlspecialchars($bm_values['footer_eyebrow'] ?? 'BUILD WHAT COMES NEXT', ENT_QUOTES, 'UTF-8'); ?></p>
      <?= mp_building_heading($bm_values['footer_title'] ?? '', 'h2'); ?>
    </div>
    <div>
      <p><?= nl2br(htmlspecialchars($bm_values['footer_support'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
      <?php if(!empty($site_content['contact_enabled'])): ?>
      <a class="footer-cta" href="<?= $bm_contact_url; ?>">
        <span class="footer-cta-label"><?= htmlspecialchars($bm_values['footer_cta'] ?? 'Discuss a project', ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="footer-cta-arrow" aria-hidden="true">↗</span>
      </a>
      <?php endif; ?>
      <?php if($bmFootPhone !== '' || $bmFootEmail !== '' || $bmFootAddress !== ''): ?>
      <address class="footer-contact">
        <?php if($bmFootAddress !== ''): ?><span><?= nl2br(htmlspecialchars($bmFootAddress, ENT_QUOTES, 'UTF-8')); ?></span><?php endif; ?>
        <?php if($bmFootPhone !== ''): ?><a href="tel:<?= htmlspecialchars(preg_replace('/[^+0-9]/', '', $bmFootPhone), ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($bmFootPhone, ENT_QUOTES, 'UTF-8'); ?></a><?php endif; ?>
        <?php if($bmFootEmail !== ''): ?><a href="mailto:<?= htmlspecialchars($bmFootEmail, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($bmFootEmail, ENT_QUOTES, 'UTF-8'); ?></a><?php endif; ?>
      </address>
      <?php endif; ?>
      <?php if($bmFootSocial): ?>
      <ul class="footer-social" aria-label="Social links">
        <?php foreach($bmFootSocial as $bmNetwork => $bmUrl): ?>
        <li><a href="<?= htmlspecialchars($bmUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars(ucfirst($bmNetwork === 'x' ? 'X' : $bmNetwork), ENT_QUOTES, 'UTF-8'); ?></a></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>
  <div class="footer-bottom">
    <?php if($bmFooterLogo): ?><img src="<?= htmlspecialchars($bmFooterLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($bmFootBrand, ENT_QUOTES, 'UTF-8'); ?> footer logo" style="display:block;width:auto;height:auto;max-width:180px;max-height:54px;object-fit:contain;margin:0 0 14px;background:#fff;padding:6px 10px;border-radius:6px"><?php endif; ?>
    <strong><?= htmlspecialchars(mb_strtoupper($bmFootBrand), ENT_QUOTES, 'UTF-8'); ?><span class="mark"><?= $bmFootMark; ?></span></strong>
    <nav class="footer-links" aria-label="Footer navigation">
      <a href="<?= $bm_products_url; ?>">Systems</a>
      <a href="<?= $bm_applications_url; ?>">Applications</a>
      <a href="<?= $bm_projects_url; ?>">Projects</a>
      <a href="<?= $bm_services_url; ?>">Services</a>
      <a href="<?= $bm_insights_url; ?>">Insights</a>
      <a href="<?= $bm_about_url; ?>">About</a>
      <?php if(!empty($site_content['contact_enabled'])): ?><a href="<?= $bm_contact_url; ?>">Enquire</a><?php endif; ?>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/privacy'); ?>">Privacy</a>
    </nav>
    <span>&copy; <?= $bmFootYear; ?> <?= htmlspecialchars($bmFootBrand, ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.<?php if ($showMpPoweredBy): ?> · Powered by MartPoint<?php endif; ?></span>
  </div>
</footer>
