<?php
/**
 * Skin Core — homepage. Markup only; all CSS/JS lives in header.php.
 * Editorial commerce composition for organic skincare brands. Section
 * order + visibility come from the backend homepage builder; headings
 * come from section_label / config_json. Products already shown in an
 * earlier rail are skipped so nothing repeats.
 */
include APPPATH . 'views/themes/skin_core/_skin.php';

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
$waHello    = rawurlencode('Hello ' . ($store->store_name ?? '') . ', I would like some skincare advice.');

/* trust badges (rendered in their own section) */
$skBadges = json_decode($settings->trust_badges_json ?? '', true);
if(!is_array($skBadges)) $skBadges = [];

/* Track product ids already rendered so later rails don't repeat them */
$sk_seen = [];
$sk_fresh = function($items) use (&$sk_seen){
    $out = [];
    foreach($items as $p){ if(isset($sk_seen[$p->id])) continue; $sk_seen[$p->id] = 1; $out[] = $p; }
    return $out;
};
/* Curated rails (admin-flagged items) render in full — mark ids seen so
   computed rails (best sellers, related) don't repeat them. */
$sk_mark = function($items) use (&$sk_seen){
    foreach($items as $p){ $sk_seen[$p->id] = 1; }
    return $items;
};

$sk_rail_i = 0;
$sk_rail_head = function($section, $fallback, $link = true) use ($slug, &$sk_rail_i){
    $sk_rail_i++;
    $id = 'sk-rail-' . $sk_rail_i;
    ?>
    <div class="sk-sec-head">
      <div>
        <h2 class="sk-h2"><?= htmlspecialchars(sk_sec_title($section, $fallback)); ?></h2>
        <?php if($s = sk_sec_sub($section)): ?><p class="sk-sec-sub"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
      <div class="sk-sec-side">
        <?php if($link): ?><a href="<?= base_url('store/' . $slug . '/products'); ?>" class="sk-sec-link">View All</a><?php endif; ?>
        <div class="sk-rail-nav">
          <button class="sk-rail-btn" onclick="skRail('<?= $id; ?>',-1)" aria-label="Previous"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></button>
          <button class="sk-rail-btn" onclick="skRail('<?= $id; ?>',1)" aria-label="Next"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></button>
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
<div class="sk-hero sk-hero-full" id="sk-hero">
  <?php foreach($heroSlides as $hi => $hero):
    $heroKicker = htmlspecialchars($hero['kicker'] !== '' ? $hero['kicker'] : ($settings->store_subheadline ?? ''));
    $heroTitle  = htmlspecialchars($hero['title'] !== '' ? $hero['title'] : ($settings->store_headline ?: ($store->store_name ?? '')));
    $heroBtnT   = $hero['btn_t'] !== '' ? $hero['btn_t'] : 'Shop the Collection';
    $heroBtnU   = $hero['btn_u'] !== '' ? $hero['btn_u'] : base_url('store/' . $slug . '/products');
  ?>
  <div class="sk-hero-slide <?= $hi === 0 ? 'active' : ''; ?> <?= $hero['img'] ? 'has-media' : ''; ?>">
    <?php if($hero['img']): ?>
    <div class="sk-hero-full-bg"><img src="<?= $hero['img']; ?>" alt="<?= $heroTitle; ?>" <?= $hi === 0 ? 'loading="eager"' : 'loading="lazy"'; ?>></div>
    <?php endif; ?>
    <div class="sk-wrap">
      <div class="sk-hero-body">
        <?php if($heroKicker): ?><p class="sk-kicker"><?= $heroKicker; ?></p><?php endif; ?>
        <h1 class="sk-hero-title"><?= $heroTitle; ?></h1>
        <?php if(!empty($settings->store_description)): ?><p class="sk-hero-lead"><?= htmlspecialchars($settings->store_description); ?></p><?php endif; ?>
        <div class="sk-btn-row">
          <a href="<?= htmlspecialchars($heroBtnU); ?>" class="sk-btn sk-btn-accent"><?= htmlspecialchars($heroBtnT); ?></a>
          <?php if($waNum): ?>
          <a href="https://wa.me/<?= $waNum; ?>?text=<?= $waHello; ?>" target="_blank" class="sk-btn sk-btn-ghost"<?= $hero['img'] ? ' style="background:' . $SK['surface'] . ';"' : ''; ?>>WhatsApp Us</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if(count($heroSlides) > 1): ?>
  <div class="sk-hero-dots">
    <?php foreach($heroSlides as $hi => $hs): ?>
    <button class="sk-hero-dot <?= $hi === 0 ? 'active' : ''; ?>" onclick="skHeroGo(<?= $hi; ?>)" aria-label="Slide <?= $hi + 1; ?>"></button>
    <?php endforeach; ?>
  </div>
  <script>
  (function(){
    var cur = 0, slides = document.querySelectorAll('#sk-hero .sk-hero-slide'), dots = document.querySelectorAll('#sk-hero .sk-hero-dot');
    function go(i){ cur = i; slides.forEach(function(s, k){ s.classList.toggle('active', k === i); }); dots.forEach(function(d, k){ d.classList.toggle('active', k === i); }); }
    window.skHeroGo = go;
    var timer = setInterval(function(){ go((cur + 1) % slides.length); }, 6000);
    var hero = document.getElementById('sk-hero');
    hero.addEventListener('pointerdown', function(){ clearInterval(timer); timer = setInterval(function(){ go((cur + 1) % slides.length); }, 8000); });
  })();
  </script>
  <?php endif; ?>
</div>

<!-- Signature ritual strip — the skincare brand promise -->
<div class="sk-sec">
  <div class="sk-wrap">
    <div class="sk-sec-head">
      <div>
        <p class="sk-kicker"><?= htmlspecialchars($SK_RITUAL['kicker']); ?></p>
        <h2 class="sk-h2" style="margin-top:8px;"><?= htmlspecialchars($SK_RITUAL['title']); ?></h2>
        <p class="sk-sec-sub"><?= htmlspecialchars($SK_RITUAL['sub']); ?></p>
      </div>
    </div>
    <div class="sk-ritual">
      <?php foreach($SK_RITUAL['steps'] as $step): ?>
      <div class="sk-ritual-step">
        <div class="sk-ritual-n"><?= $step['n']; ?></div>
        <div class="sk-ritual-t"><?= htmlspecialchars($step['t']); ?></div>
        <div class="sk-ritual-d"><?= htmlspecialchars($step['d']); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
        break;

    case 'trust_badges':
        if(empty($skBadges)) break;
?>
<div class="sk-sec sk-band">
  <div class="sk-wrap">
    <div class="sk-values">
      <?php foreach(array_slice($skBadges, 0, 4) as $b): ?>
      <div class="sk-value">
        <?php if(!empty($b['icon'])): ?><div class="sk-value-icon"><?= $b['icon']; ?></div><?php endif; ?>
        <div class="sk-value-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="sk-value-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
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
<div class="sk-sec">
  <div class="sk-wrap">
    <div class="sk-story <?= $SK['story_invert'] ? 'sk-story-invert' : ''; ?>">
      <div class="sk-story-media">
        <?php if($promoImg): ?><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? ''); ?>" loading="lazy"><?php endif; ?>
      </div>
      <div class="sk-story-txt">
        <?php if($promoSub): ?><div class="sk-kicker"><?= htmlspecialchars($promoSub); ?></div><?php endif; ?>
        <h3 class="sk-story-title"><?= htmlspecialchars($promo->banner_title ?? ''); ?></h3>
        <?php if($promoTxt): ?><p class="sk-story-lead"><?= htmlspecialchars($promoTxt); ?></p><?php endif; ?>
        <div class="sk-btn-row">
          <a href="<?= htmlspecialchars($promoBtnU); ?>" class="sk-btn sk-btn-accent"><?= htmlspecialchars($promoBtnT); ?></a>
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
<div class="sk-sec sk-band">
  <div class="sk-wrap">
    <div class="sk-sec-head sk-sec-head-center">
      <div>
        <h2 class="sk-h2"><?= htmlspecialchars(sk_sec_title($section, 'Shop by Category')); ?></h2>
        <div class="sk-rule"></div>
        <?php if($s = sk_sec_sub($section)): ?><p class="sk-sec-sub" style="margin-top:10px;"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
    </div>
    <div class="sk-fam-grid">
      <?php foreach(array_slice($categories, 0, 8) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? base_url($cat->category_image) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="sk-fam">
        <div class="sk-fam-media">
          <?php if($catImg): ?><img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy">
          <?php else: ?><div class="sk-fam-ph"><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></div><?php endif; ?>
        </div>
        <div class="sk-fam-veil"></div>
        <div class="sk-fam-body">
          <div class="sk-fam-name"><?= htmlspecialchars($cat->category_name); ?></div>
          <?php if($itemCount > 0): ?><div class="sk-fam-count"><?= $itemCount; ?> products</div><?php endif; ?>
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
        $rail = !empty($featured_products) ? $sk_mark(array_slice($featured_products, 0, 8)) : [];
        if(!empty($rail)):
?>
<div class="sk-sec">
  <div class="sk-wrap">
    <?php if($SK['featured_layout'] === 'rail'):
      $rid = $sk_rail_head($section, 'Featured');
    ?>
    <div class="sk-rail">
      <div class="sk-rail-track" id="<?= $rid; ?>">
        <?php foreach($rail as $p) sk_card($p, $cur, $settings, $slug); ?>
      </div>
    </div>
    <?php else: ?>
    <div class="sk-sec-head">
      <div>
        <h2 class="sk-h2"><?= htmlspecialchars(sk_sec_title($section, 'Featured')); ?></h2>
        <?php if($s = sk_sec_sub($section)): ?><p class="sk-sec-sub"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="sk-sec-link">View All</a>
    </div>
    <div class="sk-grid">
      <?php foreach($rail as $p) sk_card($p, $cur, $settings, $slug); ?>
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
<div class="sk-sec sk-band">
  <div class="sk-wrap">
    <div class="sk-sec-head">
      <div>
        <h2 class="sk-h2"><?= htmlspecialchars(sk_sec_title($section, 'Services')); ?></h2>
        <?php if($s = sk_sec_sub($section)): ?><p class="sk-sec-sub"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
    </div>
    <div class="sk-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->price ?? 0;
        $sImg   = (!empty($s->item_image) && file_exists($s->item_image)) ? mp_minified_image_url($s->item_image, 600)
               : ((!empty($s->service_image) && file_exists($s->service_image)) ? mp_minified_image_url($s->service_image, 600) : '');
        $sName  = $s->item_name ?? $s->service_name ?? '';
      ?>
      <div class="sk-card">
        <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="sk-card-media">
          <?php if($sImg): ?><img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($sName); ?>" loading="lazy"><?php else: ?><div class="sk-card-ph"><span><?= htmlspecialchars(substr($sName, 0, 1)); ?></span></div><?php endif; ?>
        </a>
        <div class="sk-card-body">
          <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="sk-card-name"><?= htmlspecialchars($sName); ?></a>
          <div class="sk-card-foot">
            <div class="sk-card-price"><?= sf_currency($sPrice, $cur); ?></div>
            <div class="sk-card-actions">
              <button class="sk-btn-add" onclick="addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($sName)); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)">Book</button>
              <?php if($waNum): ?>
              <button class="sk-btn-wa" onclick="skWaOrder(<?= $s->id; ?>,'<?= htmlspecialchars(addslashes($sName)); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>')" aria-label="Enquire on WhatsApp">
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
        $rail = !empty($best_sellers) ? $sk_fresh(array_slice($best_sellers, 0, 8)) : [];
        if(!empty($rail)):
?>
<div class="sk-sec">
  <div class="sk-wrap">
    <div class="sk-sec-head">
      <div>
        <h2 class="sk-h2"><?= htmlspecialchars(sk_sec_title($section, 'Best Sellers')); ?></h2>
        <?php if($s = sk_sec_sub($section)): ?><p class="sk-sec-sub"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="sk-sec-link">View All</a>
    </div>
    <div class="sk-grid">
      <?php foreach($rail as $p) sk_card($p, $cur, $settings, $slug); ?>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'new_arrivals':
        $rail = !empty($new_arrivals) ? $sk_mark(array_slice($new_arrivals, 0, 8)) : [];
        if(!empty($rail)):
?>
<div class="sk-sec sk-band">
  <div class="sk-wrap">
    <?php $rid = $sk_rail_head($section, 'New Arrivals'); ?>
    <div class="sk-rail">
      <div class="sk-rail-track" id="<?= $rid; ?>">
        <?php foreach($rail as $p) sk_card($p, $cur, $settings, $slug); ?>
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
<div class="sk-sec">
  <div class="sk-wrap">
    <div class="sk-sec-head sk-sec-head-center">
      <div>
        <h2 class="sk-h2"><?= htmlspecialchars(sk_sec_title($section, 'Our Brands')); ?></h2>
        <div class="sk-rule"></div>
      </div>
    </div>
    <div class="sk-brands">
      <?php foreach($brands as $brand): ?>
      <div class="sk-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
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
<div class="sk-sec sk-band">
  <div class="sk-wrap">
    <div class="sk-sec-head sk-sec-head-center">
      <div>
        <h2 class="sk-h2"><?= htmlspecialchars(sk_sec_title($section, 'Reviews')); ?></h2>
        <div class="sk-rule"></div>
      </div>
    </div>
    <div class="sk-testi-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="sk-testi">
        <div class="sk-testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="sk-testi-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="sk-testi-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
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
<div class="sk-sec">
  <div class="sk-wrap">
    <div class="sk-sec-head sk-sec-head-center">
      <div>
        <h2 class="sk-h2"><?= htmlspecialchars(sk_sec_title($section, 'Journal')); ?></h2>
        <div class="sk-rule"></div>
      </div>
    </div>
    <div class="sk-journal-grid">
      <?php foreach(array_slice($instagram_posts, 0, 8) as $post):
        $postImg = $post->media_url ?? $post->image ?? '';
        $postCap = $post->caption ?? $post->title ?? '';
      ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="sk-journal">
        <div class="sk-journal-img"><?php if($postImg): ?><img src="<?= htmlspecialchars($postImg); ?>" alt="" loading="lazy"><?php endif; ?></div>
        <div class="sk-journal-body">
          <div class="sk-journal-kicker">Instagram</div>
          <div class="sk-journal-title"><?= htmlspecialchars(mb_strimwidth($postCap, 0, 80, '…') ?: 'View post'); ?></div>
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
<div class="sk-sec">
  <div class="sk-wrap">
    <div class="sk-story">
      <div class="sk-story-media">
        <?php if($aboutImg): ?><img src="<?= $aboutImg; ?>" alt="<?= htmlspecialchars($store->store_name ?? ''); ?>" loading="lazy"><?php endif; ?>
      </div>
      <div class="sk-story-txt">
        <div class="sk-kicker"><?= htmlspecialchars(sk_sec_title($section, 'Our Story')); ?></div>
        <h3 class="sk-story-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? '')); ?></h3>
        <?php if(!empty($settings->store_description)): ?><p class="sk-story-lead"><?= htmlspecialchars($settings->store_description); ?></p><?php endif; ?>
        <div class="sk-btn-row">
          <a href="<?= base_url('store/' . $slug . '/about'); ?>" class="sk-btn sk-btn-ghost">About Us</a>
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
<div class="sk-sec">
  <div class="sk-wrap">
    <div class="sk-sec-head sk-sec-head-center">
      <div>
        <h2 class="sk-h2"><?= htmlspecialchars(sk_sec_title($section, 'FAQ')); ?></h2>
        <div class="sk-rule"></div>
      </div>
    </div>
    <div class="sk-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="sk-faq" onclick="this.classList.toggle('open')">
        <div class="sk-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="sk-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
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
<div class="sk-sec sk-band">
  <div class="sk-wrap">
    <div class="sk-sec-head sk-sec-head-center">
      <div>
        <h2 class="sk-h2"><?= htmlspecialchars(sk_sec_title($section, 'Contact')); ?></h2>
        <div class="sk-rule"></div>
      </div>
    </div>
    <div class="sk-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="sk-contact">
        <div class="sk-contact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="sk-contact-l">Phone</div>
        <div class="sk-contact-v"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="sk-contact">
        <div class="sk-contact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="sk-contact-l">Email</div>
        <div class="sk-contact-v"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_address)): ?>
      <div class="sk-contact">
        <div class="sk-contact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
        <div class="sk-contact-l">Visit Us</div>
        <div class="sk-contact-v">
          <?php if(!empty($settings->footer_address_url)): ?>
          <a href="<?= htmlspecialchars($settings->footer_address_url); ?>" target="_blank" style="color:inherit;"><?= nl2br(htmlspecialchars($settings->store_address)); ?></a>
          <?php else: ?>
          <?= nl2br(htmlspecialchars($settings->store_address)); ?>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
      <?php if($waNum): ?>
      <div class="sk-contact">
        <div class="sk-contact-ic"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg></div>
        <div class="sk-contact-l">WhatsApp</div>
        <div class="sk-contact-v"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
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
        $ctaTitle = sk_sec_title($section, 'Talk to Us');
        $ctaSub   = sk_sec_sub($section);
?>
<div class="sk-sec">
  <div class="sk-wrap">
    <div class="sk-cta">
      <div class="sk-cta-title"><?= htmlspecialchars($ctaTitle); ?></div>
      <?php if($ctaSub): ?><p class="sk-cta-text"><?= htmlspecialchars($ctaSub); ?></p><?php endif; ?>
      <div class="sk-btn-row" style="justify-content:center;">
        <a href="https://wa.me/<?= $waNum; ?>?text=<?= $waHello; ?>" target="_blank" class="sk-btn sk-btn-wa-full"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>Chat on WhatsApp</a>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="sk-btn sk-btn-ghost">Shop Skincare</a>
      </div>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    case 'newsletter':
?>
<div class="sk-sec">
  <div class="sk-wrap">
    <div class="sk-news">
      <div class="sk-kicker" style="color:<?= $SK['accent']; ?>;margin-bottom:10px;">Newsletter</div>
      <div class="sk-news-title"><?= htmlspecialchars(sk_sec_title($section, $settings->newsletter_title ?: 'Stay in the Loop')); ?></div>
      <?php if($s = sk_sec_sub($section, $settings->newsletter_subtitle ?? '')): ?><p class="sk-news-text"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      <form class="sk-news-form" onsubmit="return mpNewsletterSubmit(event)">
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
<div class="sk-sec sk-band">
  <div class="sk-wrap">
    <div class="sk-sec-head sk-sec-head-center">
      <div>
        <h2 class="sk-h2"><?= htmlspecialchars(sk_sec_title($section, 'Opening Hours')); ?></h2>
        <div class="sk-rule"></div>
      </div>
    </div>
    <div class="sk-hours">
      <?php foreach($business_hours as $line): ?>
      <div class="sk-hours-row"><?= htmlspecialchars($line); ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
        endif;
        break;

    endswitch;
endforeach;
