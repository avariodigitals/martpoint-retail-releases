<?php
/**
 * Parfum Core — homepage. Markup only; all CSS/JS lives in header.php.
 * Editorial magazine composition. Section order + visibility come from the
 * backend homepage builder; headings come from section_label / config_json.
 * Products already shown in an earlier rail are skipped so nothing repeats.
 */
include APPPATH . 'views/themes/parfum_core/_skin.php';

$slug  = $settings->store_slug ?? '';
$cur   = $store_currency ?? null;
$waNum = ($settings->allow_whatsapp ?? 1) ? preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '') : '';

$orderedSections = [];
if(!empty($homepage_sections)){
    $orderedSections = $homepage_sections;
    uasort($orderedSections, function($a, $b){ return ($a->display_order ?? 0) <=> ($b->display_order ?? 0); });
}

$heroSlides = [];
foreach((array)($hero_banners ?? []) as $hb){
    $img = '';
    if(!empty($hb->desktop_image) && file_exists($hb->desktop_image))     $img = mp_minified_image_url($hb->desktop_image, 1600);
    elseif(!empty($hb->mobile_image) && file_exists($hb->mobile_image))   $img = mp_minified_image_url($hb->mobile_image, 900);
    $heroSlides[] = [
        'img'    => $img,
        'kicker' => $hb->banner_subtitle ?? '',
        'title'  => $hb->banner_title ?? '',
        'btn_t'  => trim($hb->button_text ?? ''),
        'btn_u'  => trim($hb->button_url ?? ''),
    ];
}
if(empty($heroSlides)) $heroSlides = [['img' => '', 'kicker' => '', 'title' => '', 'btn_t' => '', 'btn_u' => '']];
$waHello    = rawurlencode('Hello ' . ($store->store_name ?? '') . ', I would like some fragrance advice.');

/* trust badges (rendered in their own section) */
$pfBadges = json_decode($settings->trust_badges_json ?? '', true);
if(!is_array($pfBadges)) $pfBadges = [];

/* Track product ids already rendered so later rails don't repeat them */
$pf_seen = [];
$pf_fresh = function($items) use (&$pf_seen){
    $out = [];
    foreach($items as $p){ if(isset($pf_seen[$p->id])) continue; $pf_seen[$p->id] = 1; $out[] = $p; }
    return $out;
};
/* Curated rails (admin-flagged items) render in full — mark ids seen so
   computed rails (best sellers, related) don't repeat them. */
$pf_mark = function($items) use (&$pf_seen){
    foreach($items as $p){ $pf_seen[$p->id] = 1; }
    return $items;
};

$pf_rail_i = 0;
$pf_rail_head = function($section, $fallback, $link = true) use ($slug, &$pf_rail_i){
    $pf_rail_i++;
    $id = 'pf-rail-' . $pf_rail_i;
    ?>
    <div class="pf-sec-head">
      <div>
        <h2 class="pf-h2"><?= htmlspecialchars(pf_sec_title($section, $fallback)); ?></h2>
        <?php if($s = pf_sec_sub($section)): ?><p class="pf-sec-sub"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
      <div class="pf-sec-side">
        <?php if($link): ?><a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pf-sec-link">View All</a><?php endif; ?>
        <div class="pf-rail-nav">
          <button class="pf-rail-btn" onclick="pfRail('<?= $id; ?>',-1)" aria-label="Previous"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></button>
          <button class="pf-rail-btn" onclick="pfRail('<?= $id; ?>',1)" aria-label="Next"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></button>
        </div>
      </div>
    </div>
    <?php
    return $id;
};

foreach($orderedSections as $sectionKey => $section):
    if(!$section->is_enabled) continue;
    $baseKey = preg_replace('/_\d+$/', '', $sectionKey);
    switch($baseKey):

    case 'hero_banner':
?>
<div class="pf-hero pf-hero-full" id="pf-hero">
  <?php foreach($heroSlides as $hi => $hero):
    $heroKicker = htmlspecialchars($hero['kicker'] !== '' ? $hero['kicker'] : ($settings->store_subheadline ?? ''));
    $heroTitle  = htmlspecialchars($hero['title'] !== '' ? $hero['title'] : ($settings->store_headline ?: ($store->store_name ?? '')));
    $heroBtnT   = $hero['btn_t'] !== '' ? $hero['btn_t'] : 'Shop Fragrances';
    $heroBtnU   = $hero['btn_u'] !== '' ? $hero['btn_u'] : base_url('store/' . $slug . '/products');
  ?>
  <div class="pf-hero-slide <?= $hi === 0 ? 'active' : ''; ?> <?= $hero['img'] ? 'has-media' : ''; ?>">
    <?php if($hero['img']): ?>
    <div class="pf-hero-full-bg"><img src="<?= $hero['img']; ?>" alt="<?= $heroTitle; ?>" <?= $hi === 0 ? 'loading="eager"' : 'loading="lazy"'; ?>></div>
    <?php endif; ?>
    <div class="pf-wrap">
      <div class="pf-hero-body">
        <?php if($heroKicker): ?><p class="pf-kicker"><?= $heroKicker; ?></p><?php endif; ?>
        <h1 class="pf-hero-title"><?= $heroTitle; ?></h1>
        <?php if(!empty($settings->store_description)): ?><p class="pf-hero-lead"><?= htmlspecialchars($settings->store_description); ?></p><?php endif; ?>
        <div class="pf-btn-row">
          <a href="<?= htmlspecialchars($heroBtnU); ?>" class="pf-btn pf-btn-accent"><?= htmlspecialchars($heroBtnT); ?></a>
          <?php if($waNum): ?>
          <a href="https://wa.me/<?= $waNum; ?>?text=<?= $waHello; ?>" target="_blank" class="pf-btn pf-btn-ghost"<?= $hero['img'] ? ' style="background:' . $PF['surface'] . ';"' : ''; ?>>WhatsApp Us</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if(count($heroSlides) > 1): ?>
  <div class="pf-hero-dots">
    <?php foreach($heroSlides as $hi => $hs): ?>
    <button class="pf-hero-dot <?= $hi === 0 ? 'active' : ''; ?>" onclick="pfHeroGo(<?= $hi; ?>)" aria-label="Slide <?= $hi + 1; ?>"></button>
    <?php endforeach; ?>
  </div>
  <script>
  (function(){
    var cur = 0, slides = document.querySelectorAll('#pf-hero .pf-hero-slide'), dots = document.querySelectorAll('#pf-hero .pf-hero-dot');
    function go(i){ cur = i; slides.forEach(function(s, k){ s.classList.toggle('active', k === i); }); dots.forEach(function(d, k){ d.classList.toggle('active', k === i); }); }
    window.pfHeroGo = go;
    var timer = setInterval(function(){ go((cur + 1) % slides.length); }, 6000);
    var hero = document.getElementById('pf-hero');
    hero.addEventListener('pointerdown', function(){ clearInterval(timer); timer = setInterval(function(){ go((cur + 1) % slides.length); }, 8000); });
  })();
  </script>
  <?php endif; ?>
</div>
<?php
        break;

    case 'trust_badges':
        if(empty($pfBadges)) break;
?>
<div class="pf-sec pf-band">
  <div class="pf-wrap">
    <div class="pf-values">
      <?php foreach(array_slice($pfBadges, 0, 4) as $b): ?>
      <div class="pf-value">
        <?php if(!empty($b['icon'])): ?><div class="pf-value-icon"><?= $b['icon']; ?></div><?php endif; ?>
        <div class="pf-value-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="pf-value-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
        break;

    case 'promo_banner':
        if(!empty($promo_banners)):
        foreach($promo_banners as $promo):
        $promoImg = (!empty($promo->desktop_image) && file_exists($promo->desktop_image)) ? mp_minified_image_url($promo->desktop_image, 1200) : '';
        $promoSub = $promo->banner_subtitle ?? '';
        $promoTxt = $promo->banner_text ?? $promo->banner_description ?? '';
        $promoBtnT = trim($promo->button_text ?? '') ?: 'Shop Now';
        $promoBtnU = trim($promo->button_url ?? '') ?: base_url('store/' . $slug . '/products');
?>
<div class="pf-sec">
  <div class="pf-wrap">
    <div class="pf-story <?= $PF['story_invert'] ? 'pf-story-invert' : ''; ?>">
      <div class="pf-story-media">
        <?php if($promoImg): ?><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? ''); ?>" loading="lazy"><?php endif; ?>
      </div>
      <div class="pf-story-txt">
        <?php if($promoSub): ?><div class="pf-kicker"><?= htmlspecialchars($promoSub); ?></div><?php endif; ?>
        <h3 class="pf-story-title"><?= htmlspecialchars($promo->banner_title ?? ''); ?></h3>
        <?php if($promoTxt): ?><p class="pf-story-lead"><?= htmlspecialchars($promoTxt); ?></p><?php endif; ?>
        <div class="pf-btn-row">
          <a href="<?= htmlspecialchars($promoBtnU); ?>" class="pf-btn pf-btn-accent"><?= htmlspecialchars($promoBtnT); ?></a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
        endforeach;
        endif;
        break;

    case 'featured_categories':
        if(!empty($categories)):
?>
<div class="pf-sec pf-band">
  <div class="pf-wrap">
    <div class="pf-sec-head pf-sec-head-center">
      <div>
        <h2 class="pf-h2"><?= htmlspecialchars(pf_sec_title($section, 'Fragrance Families')); ?></h2>
        <div class="pf-rule"></div>
        <?php if($s = pf_sec_sub($section)): ?><p class="pf-sec-sub" style="margin-top:10px;"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
    </div>
    <div class="pf-fam-grid">
      <?php foreach(array_slice($categories, 0, 8) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? base_url($cat->category_image) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pf-fam">
        <div class="pf-fam-media">
          <?php if($catImg): ?><img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy">
          <?php else: ?><div class="pf-fam-ph"><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></div><?php endif; ?>
        </div>
        <div class="pf-fam-veil"></div>
        <div class="pf-fam-body">
          <div class="pf-fam-name"><?= htmlspecialchars($cat->category_name); ?></div>
          <?php if($itemCount > 0): ?><div class="pf-fam-count"><?= $itemCount; ?> scents</div><?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'featured_products':
        $rail = !empty($featured_products) ? $pf_mark(array_slice($featured_products, 0, 8)) : [];
        if(!empty($rail)):
?>
<div class="pf-sec">
  <div class="pf-wrap">
    <?php if($PF['featured_layout'] === 'rail'):
      $rid = $pf_rail_head($section, 'Featured');
    ?>
    <div class="pf-rail">
      <div class="pf-rail-track" id="<?= $rid; ?>">
        <?php foreach($rail as $p) pf_card($p, $cur, $settings, $slug); ?>
      </div>
    </div>
    <?php else: ?>
    <div class="pf-sec-head">
      <div>
        <h2 class="pf-h2"><?= htmlspecialchars(pf_sec_title($section, 'Featured')); ?></h2>
        <?php if($s = pf_sec_sub($section)): ?><p class="pf-sec-sub"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pf-sec-link">View All</a>
    </div>
    <div class="pf-grid">
      <?php foreach($rail as $p) pf_card($p, $cur, $settings, $slug); ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php
        endif;
        break;

    case 'featured_services':
        if(!empty($featured_services)):
?>
<div class="pf-sec pf-band">
  <div class="pf-wrap">
    <div class="pf-sec-head">
      <div>
        <h2 class="pf-h2"><?= htmlspecialchars(pf_sec_title($section, 'Services')); ?></h2>
        <?php if($s = pf_sec_sub($section)): ?><p class="pf-sec-sub"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
    </div>
    <div class="pf-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->price ?? 0;
        $sImg   = (!empty($s->item_image) && file_exists($s->item_image)) ? mp_minified_image_url($s->item_image, 600)
               : ((!empty($s->service_image) && file_exists($s->service_image)) ? mp_minified_image_url($s->service_image, 600) : '');
        $sName  = $s->item_name ?? $s->service_name ?? '';
      ?>
      <div class="pf-card">
        <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="pf-card-media">
          <?php if($sImg): ?><img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($sName); ?>" loading="lazy"><?php else: ?><div class="pf-card-ph"><span><?= htmlspecialchars(substr($sName, 0, 1)); ?></span></div><?php endif; ?>
        </a>
        <div class="pf-card-body">
          <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="pf-card-name"><?= htmlspecialchars($sName); ?></a>
          <div class="pf-card-foot">
            <div class="pf-card-price"><?= sf_currency($sPrice, $cur); ?></div>
            <div class="pf-card-actions">
              <button class="pf-btn-add" onclick="addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($sName)); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)">Book</button>
              <?php if($waNum): ?>
              <button class="pf-btn-wa" onclick="pfWaOrder(<?= $s->id; ?>,'<?= htmlspecialchars(addslashes($sName)); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>')" aria-label="Enquire on WhatsApp">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
              </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'best_sellers':
        $rail = !empty($best_sellers) ? $pf_fresh(array_slice($best_sellers, 0, 8)) : [];
        if(!empty($rail)):
?>
<div class="pf-sec">
  <div class="pf-wrap">
    <div class="pf-sec-head">
      <div>
        <h2 class="pf-h2"><?= htmlspecialchars(pf_sec_title($section, 'Best Sellers')); ?></h2>
        <?php if($s = pf_sec_sub($section)): ?><p class="pf-sec-sub"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pf-sec-link">View All</a>
    </div>
    <div class="pf-grid">
      <?php foreach($rail as $p) pf_card($p, $cur, $settings, $slug); ?>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'new_arrivals':
        $rail = !empty($new_arrivals) ? $pf_mark(array_slice($new_arrivals, 0, 8)) : [];
        if(!empty($rail)):
?>
<div class="pf-sec pf-band">
  <div class="pf-wrap">
    <?php $rid = $pf_rail_head($section, 'New Arrivals'); ?>
    <div class="pf-rail">
      <div class="pf-rail-track" id="<?= $rid; ?>">
        <?php foreach($rail as $p) pf_card($p, $cur, $settings, $slug); ?>
      </div>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'brands':
        if(!empty($brands)):
?>
<div class="pf-sec">
  <div class="pf-wrap">
    <div class="pf-sec-head pf-sec-head-center">
      <div>
        <h2 class="pf-h2"><?= htmlspecialchars(pf_sec_title($section, 'Our Brands')); ?></h2>
        <div class="pf-rule"></div>
      </div>
    </div>
    <div class="pf-brands">
      <?php foreach($brands as $brand): ?>
      <div class="pf-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'testimonials':
        if(!empty($testimonials)):
?>
<div class="pf-sec pf-band">
  <div class="pf-wrap">
    <div class="pf-sec-head pf-sec-head-center">
      <div>
        <h2 class="pf-h2"><?= htmlspecialchars(pf_sec_title($section, 'Reviews')); ?></h2>
        <div class="pf-rule"></div>
      </div>
    </div>
    <div class="pf-testi-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="pf-testi">
        <div class="pf-testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="pf-testi-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="pf-testi-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'instagram_gallery':
        if(!empty($instagram_posts)):
?>
<div class="pf-sec">
  <div class="pf-wrap">
    <div class="pf-sec-head pf-sec-head-center">
      <div>
        <h2 class="pf-h2"><?= htmlspecialchars(pf_sec_title($section, 'Journal')); ?></h2>
        <div class="pf-rule"></div>
      </div>
    </div>
    <div class="pf-journal-grid">
      <?php foreach(array_slice($instagram_posts, 0, 8) as $post):
        $postImg = $post->media_url ?? $post->image ?? '';
        $postCap = $post->caption ?? $post->title ?? '';
      ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="pf-journal">
        <div class="pf-journal-img"><?php if($postImg): ?><img src="<?= htmlspecialchars($postImg); ?>" alt="" loading="lazy"><?php endif; ?></div>
        <div class="pf-journal-body">
          <div class="pf-journal-kicker">Instagram</div>
          <div class="pf-journal-title"><?= htmlspecialchars(mb_strimwidth($postCap, 0, 80, '…') ?: 'View post'); ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'store_info':
        $aboutImg = '';
        if(!empty($hero_banners[1])){
            $hb = $hero_banners[1];
            if(!empty($hb->desktop_image) && file_exists($hb->desktop_image)) $aboutImg = mp_minified_image_url($hb->desktop_image, 1200);
        }
        if(empty($settings->store_description) && !$aboutImg) break;
?>
<div class="pf-sec">
  <div class="pf-wrap">
    <div class="pf-story">
      <div class="pf-story-media">
        <?php if($aboutImg): ?><img src="<?= $aboutImg; ?>" alt="<?= htmlspecialchars($store->store_name ?? ''); ?>" loading="lazy"><?php endif; ?>
      </div>
      <div class="pf-story-txt">
        <div class="pf-kicker"><?= htmlspecialchars(pf_sec_title($section, 'Our Story')); ?></div>
        <h3 class="pf-story-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? '')); ?></h3>
        <?php if(!empty($settings->store_description)): ?><p class="pf-story-lead"><?= htmlspecialchars($settings->store_description); ?></p><?php endif; ?>
        <div class="pf-btn-row">
          <a href="<?= base_url('store/' . $slug . '/about'); ?>" class="pf-btn pf-btn-ghost">About Us</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
        break;

    case 'faqs':
        if(!empty($faqs)):
?>
<div class="pf-sec">
  <div class="pf-wrap">
    <div class="pf-sec-head pf-sec-head-center">
      <div>
        <h2 class="pf-h2"><?= htmlspecialchars(pf_sec_title($section, 'FAQ')); ?></h2>
        <div class="pf-rule"></div>
      </div>
    </div>
    <div class="pf-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="pf-faq" onclick="this.classList.toggle('open')">
        <div class="pf-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="pf-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'contact_section':
        $hasContact = !empty($settings->store_phone) || !empty($settings->store_email) || !empty($settings->store_address) || $waNum;
        if($hasContact):
?>
<div class="pf-sec pf-band">
  <div class="pf-wrap">
    <div class="pf-sec-head pf-sec-head-center">
      <div>
        <h2 class="pf-h2"><?= htmlspecialchars(pf_sec_title($section, 'Contact')); ?></h2>
        <div class="pf-rule"></div>
      </div>
    </div>
    <div class="pf-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="pf-contact">
        <div class="pf-contact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="pf-contact-l">Phone</div>
        <div class="pf-contact-v"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="pf-contact">
        <div class="pf-contact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="pf-contact-l">Email</div>
        <div class="pf-contact-v"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_address)): ?>
      <div class="pf-contact">
        <div class="pf-contact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
        <div class="pf-contact-l">Visit Us</div>
        <div class="pf-contact-v">
          <?php if(!empty($settings->footer_address_url)): ?>
          <a href="<?= htmlspecialchars($settings->footer_address_url); ?>" target="_blank" style="color:inherit;"><?= nl2br(htmlspecialchars($settings->store_address)); ?></a>
          <?php else: ?>
          <?= nl2br(htmlspecialchars($settings->store_address)); ?>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
      <?php if($waNum): ?>
      <div class="pf-contact">
        <div class="pf-contact-ic"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg></div>
        <div class="pf-contact-l">WhatsApp</div>
        <div class="pf-contact-v"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'whatsapp_cta':
        if($waNum && ($settings->show_whatsapp_cta ?? 1)):
        $ctaTitle = pf_sec_title($section, 'Talk to Us');
        $ctaSub   = pf_sec_sub($section);
?>
<div class="pf-sec">
  <div class="pf-wrap">
    <div class="pf-cta">
      <div class="pf-cta-title"><?= htmlspecialchars($ctaTitle); ?></div>
      <?php if($ctaSub): ?><p class="pf-cta-text"><?= htmlspecialchars($ctaSub); ?></p><?php endif; ?>
      <div class="pf-btn-row" style="justify-content:center;">
        <a href="https://wa.me/<?= $waNum; ?>?text=<?= $waHello; ?>" target="_blank" class="pf-btn pf-btn-wa-full"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>Chat on WhatsApp</a>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pf-btn pf-btn-ghost">Shop Fragrances</a>
      </div>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'newsletter':
?>
<div class="pf-sec">
  <div class="pf-wrap">
    <div class="pf-news">
      <div class="pf-kicker" style="color:<?= $PF['accent']; ?>;margin-bottom:10px;">Newsletter</div>
      <div class="pf-news-title"><?= htmlspecialchars(pf_sec_title($section, $settings->newsletter_title ?: 'Stay in the Loop')); ?></div>
      <?php if($s = pf_sec_sub($section, $settings->newsletter_subtitle ?? '')): ?><p class="pf-news-text"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      <form class="pf-news-form" onsubmit="return mpNewsletterSubmit(event)">
        <input type="email" name="email" placeholder="Your email address" required>
        <button type="submit">Subscribe</button>
      </form>
    </div>
  </div>
</div>
<?php
        break;

    case 'store_hours':
        if(!empty($business_hours)):
?>
<div class="pf-sec pf-band">
  <div class="pf-wrap">
    <div class="pf-sec-head pf-sec-head-center">
      <div>
        <h2 class="pf-h2"><?= htmlspecialchars(pf_sec_title($section, 'Opening Hours')); ?></h2>
        <div class="pf-rule"></div>
      </div>
    </div>
    <div class="pf-hours">
      <?php foreach($business_hours as $line): ?>
      <div class="pf-hours-row"><?= htmlspecialchars($line); ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    endswitch;
endforeach;
