<?php
  $bmBrand = trim((string)($bm_brand ?? ($store->store_name ?? 'Store')));
  $bmTagline = trim((string)($bm_values['brand_tagline'] ?? ''));
  $bmPath = trim(uri_string(), '/');
  $bmOn = function($suffix) use ($bmPath) {
      return $suffix === '' ? (bool)preg_match('~^store/[^/]+$~', $bmPath) : (bool)preg_match('~' . preg_quote($suffix, '~') . '$~', $bmPath);
  };
  $bmConfigured = [];
  if (!empty($storefront_navigation_rows['primary']) && is_array($storefront_navigation_rows['primary'])) {
      $bmConfigured = $storefront_navigation_rows['primary'];
  } elseif (!empty($storefront_nav) && is_array($storefront_nav)) {
      $bmConfigured = $storefront_nav;
  }
  $bmDefaultNav = [
      ['label' => 'Home', 'url' => $bm_base, 'active' => $bmOn('')],
      ['label' => 'Systems', 'url' => $bm_products_url, 'active' => $bmOn('/products') || $bmPath !== '' && preg_match('~/product/~', $bmPath)],
      ['label' => 'Applications', 'url' => $bm_applications_url, 'active' => $bmOn('/applications')],
      ['label' => 'Projects', 'url' => $bm_projects_url, 'active' => $bmOn('/projects')],
      ['label' => 'Services', 'url' => $bm_services_url, 'active' => $bmOn('/services') || preg_match('~/service/~', $bmPath)],
      ['label' => 'Insights', 'url' => $bm_insights_url, 'active' => $bmOn('/blog') || $bmOn('/insights')],
      ['label' => 'About', 'url' => $bm_about_url, 'active' => $bmOn('/about')],
      ['label' => 'Enquire', 'url' => $bm_contact_url, 'active' => $bmOn('/contact')],
  ];
?>
<header class="site-header">
  <a class="brand" href="<?= $bm_base; ?>">
    <?= htmlspecialchars(mb_strtoupper($bmBrand), ENT_QUOTES, 'UTF-8'); ?><span class="mark"><?= $bm_skin === 'vector' ? '/' : '.'; ?></span>
    <?php if($bmTagline !== ''): ?><small><?= htmlspecialchars($bmTagline, ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
  </a>
  <nav class="site-nav" aria-label="Main navigation" id="bm-nav">
    <?php if($bmConfigured): ?>
      <?php foreach($bmConfigured as $item): ?>
        <?php if(!empty($item['children'])): ?>
        <div class="nav-drop">
          <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>" aria-haspopup="true"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></a>
          <div class="nav-sub">
            <?php foreach($item['children'] as $child): ?><a href="<?= htmlspecialchars($child['url'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($child['label'], ENT_QUOTES, 'UTF-8'); ?></a><?php endforeach; ?>
          </div>
        </div>
        <?php else: ?>
        <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <?php foreach($bmDefaultNav as $item): ?>
      <a href="<?= $item['url']; ?>" <?= !empty($item['active']) ? 'class="active" aria-current="page"' : ''; ?>><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></a>
      <?php endforeach; ?>
    <?php endif; ?>
  </nav>
  <button class="mobile-toggle" type="button" aria-label="Toggle menu" aria-expanded="false" aria-controls="bm-nav">Menu</button>
  <?php if(!empty($site_content['contact_enabled'])): ?>
  <a class="header-cta" href="<?= $bm_contact_url; ?>"><?= htmlspecialchars($bm_values['header_cta_label'] ?? 'Discuss a project', ENT_QUOTES, 'UTF-8'); ?> <span aria-hidden="true">↗</span></a>
  <?php endif; ?>
</header>
