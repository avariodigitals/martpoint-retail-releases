<?php
/**
 * Verdant — storefront homepage.
 * Markup only; all CSS/JS lives in header.php. Every block is driven by the
 * Online Store backend:
 *   Hero            → Banners (type: hero) + Appearance headline/sub-headline
 *   Proof chips     → Settings → Trust Badges
 *   Concern cards   → Homepage Builder: Featured Categories (+Copy = texture)
 *   Dark values band→ Homepage Builder: Trust Badges
 *   Product sets    → Featured Products / Best Sellers / New Arrivals
 *   Story           → Store Information (copy from Settings, image: hero 2)
 *   Routine sets    → Banners (type: promo), alternates direction
 *   Journal         → Instagram Gallery
 *   Reviews         → Testimonials · Brands → Brands · FAQs → FAQs
 *   Newsletter      → Newsletter CTA copy + store/hero image
 */
include APPPATH . 'views/themes/verdant/_theme.php';

$slug  = $settings->store_slug ?? '';
$cur   = $store_currency ?? null;
$waNum = ($settings->allow_whatsapp ?? 1) ? preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '') : '';

$orderedSections = [];
if(!empty($homepage_sections)){
    $orderedSections = $homepage_sections;
    uasort($orderedSections, function($a, $b){ return ($a->display_order ?? 0) <=> ($b->display_order ?? 0); });
}

/* Hero slides — banner images cycle on the right; copy comes from the first
   banner or from Appearance → Store Branding. */
$heroSlides = [];
foreach((array)($hero_banners ?? []) as $hb){
    $heroSlides[] = [
        'img'    => vd_banner_img($hb, 1200),
        'kicker' => trim($hb->banner_subtitle ?? ''),
        'title'  => trim($hb->banner_title ?? ''),
        'btn_t'  => trim($hb->button_text ?? ''),
        'btn_u'  => trim($hb->button_url ?? ''),
    ];
}
$heroMedia = array_values(array_filter($heroSlides, function($s){ return $s['img'] !== ''; }));
$firstHero = $heroSlides[0] ?? null;

/* *word* inside backend headings renders as an italic serif accent. */
$vd_em = function($text){
    $text = htmlspecialchars($text);
    return preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);
};

/* Trust badges feed both the hero proof chips and the dark values band. */
$vdBadges = json_decode($settings->trust_badges_json ?? '', true);
if(!is_array($vdBadges)) $vdBadges = [];
$vdBadgesOk = array_values(array_filter($vdBadges, function($b){ return trim(($b['title'] ?? '') . ($b['desc'] ?? '')) !== ''; }));

/* Dedup products across rails; category cursor across concern copies. */
$vd_seen = [];
$vd_fresh = function($items) use (&$vd_seen){
    $out = [];
    foreach($items as $p){ if(isset($vd_seen[$p->id])) continue; $vd_seen[$p->id] = 1; $out[] = $p; }
    return $out;
};
$vd_mark = function($items) use (&$vd_seen){
    foreach($items as $p){ $vd_seen[$p->id] = 1; }
    return $items;
};
$vd_cat_i = 0;

$vd_rail_i = 0;
$vd_rail_head = function($section, $fallback, $link = true) use ($slug, &$vd_rail_i, $vd_em){
    $vd_rail_i++;
    $id = 'vd-rail-' . $vd_rail_i;
    ?>
    <div class="vd-sec-head">
      <div>
        <h2 class="vd-h2"><?= $vd_em(vd_sec_title($section, $fallback)); ?></h2>
        <?php if($s = vd_sec_sub($section)): ?><p class="vd-lead"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
      <div class="vd-sec-side">
        <?php if($link): ?><a href="<?= base_url('store/' . $slug . '/products'); ?>" class="vd-link">View all <?= vd_arrow_svg(); ?></a><?php endif; ?>
        <div class="vd-rail-nav">
          <button class="vd-rail-btn" onclick="vdRail('<?= $id; ?>',-1)" aria-label="Previous"><svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></button>
          <button class="vd-rail-btn" onclick="vdRail('<?= $id; ?>',1)" aria-label="Next"><svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></button>
        </div>
      </div>
    </div>
    <?php
    return $id;
};
$vd_sec_open = function($section, $fallback, $subFallback = '') use ($vd_em){
    ?>
    <div class="vd-sec-head vd-sec-head-center">
      <div>
        <h2 class="vd-h2"><?= $vd_em(vd_sec_title($section, $fallback)); ?></h2>
        <?php if($s = vd_sec_sub($section, $subFallback)): ?><p class="vd-lead"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
    </div>
    <?php
};
$vd_promo_i = 0;

foreach($orderedSections as $sectionKey => $section):
    if(!$section->is_enabled) continue;
    $baseKey = preg_replace('/_\d+$/', '', $sectionKey);
    switch($baseKey):

    case 'hero_banner':
        $hTitle = ($firstHero && $firstHero['title'] !== '') ? $firstHero['title']
                : ($settings->store_headline ?: $store->store_name ?? '');
        $hSub   = ($firstHero && $firstHero['kicker'] !== '') ? $firstHero['kicker']
                : ($settings->store_subheadline ?? '');
        $hBtnT  = ($firstHero && $firstHero['btn_t'] !== '') ? $firstHero['btn_t'] : 'Shop the range';
        $hBtnU  = ($firstHero && $firstHero['btn_u'] !== '') ? $firstHero['btn_u'] : base_url('store/' . $slug . '/products');
?>
<section class="vd-hero">
  <div class="vd-wrap">
    <div class="vd-hero-grid <?= empty($heroMedia) ? 'no-media' : ''; ?>">
      <div class="vd-hero-copy">
        <h1 class="vd-display vd-hero-title"><?= $vd_em($hTitle); ?></h1>
        <?php if($hSub): ?><p class="vd-hero-sub"><?= htmlspecialchars($hSub); ?></p><?php endif; ?>
        <?php if(!empty($settings->store_description)): ?>
        <p class="vd-lead"><?= htmlspecialchars($settings->store_description); ?></p>
        <?php endif; ?>
        <div class="vd-btn-row">
          <a href="<?= htmlspecialchars($hBtnU); ?>" class="vd-btn vd-btn-primary"><?= htmlspecialchars($hBtnT); ?> <?= vd_arrow_svg(); ?></a>
          <?php if($waNum): ?>
          <a href="https://wa.me/<?= $waNum; ?>?text=<?= rawurlencode('Hello ' . ($store->store_name ?? '') . ', I would like some skincare advice.'); ?>" target="_blank" rel="noopener" class="vd-btn vd-btn-ghost">Talk to us <?= vd_wa_svg(); ?></a>
          <?php endif; ?>
        </div>
        <?php if(!empty($vdBadgesOk)): ?>
        <div class="vd-hero-proof">
          <?php foreach(array_slice($vdBadgesOk, 0, 3) as $b): ?>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?= htmlspecialchars($b['title']); ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php if(!empty($heroMedia)): ?>
      <div class="vd-hero-media" id="vd-hero-media">
        <?php foreach($heroMedia as $hi => $hs): ?>
        <div class="vd-hero-slide <?= $hi === 0 ? 'active' : ''; ?>">
          <img src="<?= $hs['img']; ?>" alt="<?= htmlspecialchars($hs['title'] ?: $hTitle); ?>" <?= $hi === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'; ?>>
          <?php if($hs['title'] !== ''): ?>
          <div class="vd-hero-slide-cap">
            <div>
              <b><?= htmlspecialchars($hs['title']); ?></b>
              <?php if($hs['kicker']): ?><small><?= htmlspecialchars($hs['kicker']); ?></small><?php endif; ?>
            </div>
            <a href="<?= htmlspecialchars($hs['btn_u'] ?: base_url('store/' . $slug . '/products')); ?>" aria-label="<?= htmlspecialchars($hs['btn_t'] ?: 'Shop'); ?>"><?= vd_arrow_svg(); ?></a>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if(count($heroMedia) > 1): ?>
        <div class="vd-hero-dots">
          <?php foreach($heroMedia as $hi => $hs): ?>
          <button class="vd-hero-dot <?= $hi === 0 ? 'active' : ''; ?>" onclick="vdHeroGo(<?= $hi; ?>)" aria-label="Slide <?= $hi + 1; ?>"></button>
          <?php endforeach; ?>
        </div>
        <script>
        (function(){
          var cur = 0, slides = document.querySelectorAll('#vd-hero-media .vd-hero-slide'), dots = document.querySelectorAll('#vd-hero-media .vd-hero-dot');
          function go(i){ cur = i; slides.forEach(function(s,k){ s.classList.toggle('active', k===i); }); dots.forEach(function(d,k){ d.classList.toggle('active', k===i); }); }
          window.vdHeroGo = go;
          var t = setInterval(function(){ go((cur+1) % slides.length); }, 6000);
          document.getElementById('vd-hero-media').addEventListener('pointerdown', function(){ clearInterval(t); t = setInterval(function(){ go((cur+1) % slides.length); }, 8000); });
        })();
        </script>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php
        break;

    case 'featured_categories':
        if(empty($categories)) break;
        $vd_cat_i++;
        $isTexture = $vd_cat_i > 1; /* duplicated copies render as wide texture cards */
        if(!$isTexture):
            $cats = array_slice($categories, 0, 6);
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <?php $vd_sec_open($section, 'Start with your *skin concern*.', 'Choose what your skin needs today — we keep the routine simple.'); ?>
    <div class="vd-concern-grid">
      <?php foreach($cats as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? base_url($cat->category_image) : '';
        $count  = (int)($cat->item_count ?? 0);
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="vd-concern">
        <div class="vd-concern-media">
          <?php if($catImg): ?><img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy"><?php else: ?><div class="vd-concern-ph"><?= htmlspecialchars(mb_substr($cat->category_name, 0, 1)); ?></div><?php endif; ?>
        </div>
        <div class="vd-concern-body">
          <div>
            <div class="vd-concern-name"><?= htmlspecialchars($cat->category_name); ?></div>
            <?php if($count > 0): ?><div class="vd-concern-count"><?= $count; ?> products</div><?php endif; ?>
          </div>
          <span class="vd-concern-arrow"><?= vd_arrow_svg(); ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
        else:
            $cats = array_slice($categories, 6, 4);
            if(empty($cats)) $cats = array_slice($categories, 0, 4);
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <?php $vd_sec_open($section, 'Shop by *texture*.'); ?>
    <div class="vd-texture-grid">
      <?php foreach($cats as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? base_url($cat->category_image) : '';
        $count  = (int)($cat->item_count ?? 0);
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="vd-texture">
        <?php if($catImg): ?><img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy"><?php else: ?><div class="vd-concern-ph"><?= htmlspecialchars(mb_substr($cat->category_name, 0, 1)); ?></div><?php endif; ?>
        <div class="vd-texture-body">
          <div>
            <div class="vd-texture-name"><?= htmlspecialchars($cat->category_name); ?></div>
            <?php if($count > 0): ?><div class="vd-texture-count"><?= $count; ?> products</div><?php endif; ?>
          </div>
          <span class="vd-concern-arrow"><?= vd_arrow_svg(); ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
        endif;
        break;

    case 'trust_badges':
        if(empty($vdBadgesOk)) break;
?>
<section class="vd-sec">
  <div class="vd-band">
    <div class="vd-values">
      <div>
        <span class="vd-kicker"><?= htmlspecialchars(vd_sec_sub($section, 'Why ' . ($store->store_name ?? 'us'))); ?></span>
        <h2 class="vd-h2" style="margin-top:14px;"><?= $vd_em(vd_sec_title($section, 'Kind to skin. *Clear* about what is inside.')); ?></h2>
      </div>
      <div class="vd-values-list">
        <?php foreach(array_slice($vdBadgesOk, 0, 4) as $b): ?>
        <div class="vd-value">
          <span class="vd-value-ic"><?= !empty($b['icon']) ? $b['icon'] : '&#10003;'; ?></span>
          <div>
            <div class="vd-value-t"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
            <div class="vd-value-d"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php
        break;

    case 'featured_products':
        $rail = !empty($featured_products) ? $vd_mark(array_slice($featured_products, 0, 8)) : [];
        if(empty($rail)) break;
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <div class="vd-sec-head">
      <div>
        <span class="vd-kicker"><?= htmlspecialchars(vd_sec_sub($section) ?: 'The edit'); ?></span>
        <h2 class="vd-h2" style="margin-top:12px;"><?= $vd_em(vd_sec_title($section, 'The *essentials* edit')); ?></h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="vd-link">View all <?= vd_arrow_svg(); ?></a>
    </div>
    <div class="vd-grid">
      <?php foreach($rail as $p) vd_card($p, $cur, $settings, $slug); ?>
    </div>
  </div>
</section>
<?php
        break;

    case 'best_sellers':
        $rail = !empty($best_sellers) ? $vd_fresh(array_slice($best_sellers, 0, 8)) : [];
        if(empty($rail)) break;
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <div class="vd-sec-head">
      <div>
        <h2 class="vd-h2"><?= $vd_em(vd_sec_title($section, 'Loved and *re-bought*')); ?></h2>
        <?php if($s = vd_sec_sub($section)): ?><p class="vd-lead"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="vd-link">View all <?= vd_arrow_svg(); ?></a>
    </div>
    <div class="vd-grid">
      <?php foreach($rail as $p) vd_card($p, $cur, $settings, $slug); ?>
    </div>
  </div>
</section>
<?php
        break;

    case 'new_arrivals':
        $rail = !empty($new_arrivals) ? $vd_fresh(array_slice($new_arrivals, 0, 10)) : [];
        if(empty($rail)) break;
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <?php $rid = $vd_rail_head($section, 'Just *in*'); ?>
    <div class="vd-rail">
      <div class="vd-rail-track" id="<?= $rid; ?>">
        <?php foreach($rail as $p) vd_card($p, $cur, $settings, $slug); ?>
      </div>
    </div>
  </div>
</section>
<?php
        break;

    case 'featured_services':
        if(empty($featured_services)) break;
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <div class="vd-sec-head">
      <div>
        <h2 class="vd-h2"><?= $vd_em(vd_sec_title($section, 'Treatments')); ?></h2>
        <?php if($s = vd_sec_sub($section)): ?><p class="vd-lead"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
    </div>
    <div class="vd-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->price ?? 0;
        $sImg   = (!empty($s->item_image) && file_exists($s->item_image)) ? mp_minified_image_url($s->item_image, 640)
               : ((!empty($s->service_image) && file_exists($s->service_image)) ? mp_minified_image_url($s->service_image, 640) : '');
        $sName  = $s->item_name ?? $s->service_name ?? '';
      ?>
      <article class="vd-card">
        <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="vd-card-media">
          <?php if($sImg): ?><img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($sName); ?>" loading="lazy"><?php else: ?><span class="vd-card-ph"><?= htmlspecialchars(mb_substr($sName, 0, 1)); ?></span><?php endif; ?>
        </a>
        <div class="vd-card-body">
          <div class="vd-card-range">Service</div>
          <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="vd-card-name"><?= htmlspecialchars($sName); ?></a>
          <div class="vd-card-foot">
            <div class="vd-card-price"><?= sf_currency($sPrice, $cur); ?></div>
            <div class="vd-card-actions">
              <button type="button" class="vd-add" onclick="addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($sName)); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)">Book</button>
              <?php if($waNum): ?>
              <button type="button" class="vd-wa-ic" onclick="vdWaOrder(<?= $s->id; ?>,'<?= htmlspecialchars(addslashes($sName)); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>')" aria-label="Enquire on WhatsApp"><?= vd_wa_svg(); ?></button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
        break;

    case 'store_info':
        $aboutImg = '';
        foreach((array)($hero_banners ?? []) as $i => $hb){
            if($i === 0) continue;
            $aboutImg = vd_banner_img($hb, 1200);
            if($aboutImg) break;
        }
        if(!$aboutImg) $aboutImg = vd_store_img($settings, 1200);
        if(empty($settings->store_description) && !$aboutImg) break;
?>
<section class="vd-sec">
  <div class="vd-band">
    <div class="vd-story">
      <?php if($aboutImg): ?>
      <div class="vd-story-media"><img src="<?= $aboutImg; ?>" alt="<?= htmlspecialchars($store->store_name ?? ''); ?>" loading="lazy"></div>
      <?php endif; ?>
      <div class="vd-story-copy">
        <span class="vd-kicker"><?= htmlspecialchars(vd_sec_sub($section, 'Our story')); ?></span>
        <h2 class="vd-h2" style="margin-top:16px;"><?= $vd_em(vd_sec_title($section, 'Made slowly, *with you* in mind')); ?></h2>
        <?php if(!empty($settings->store_description)): ?><p class="vd-lead"><?= nl2br(htmlspecialchars($settings->store_description)); ?></p><?php endif; ?>
        <?php if(!empty($vdBadgesOk)): ?>
        <div class="vd-story-facts">
          <?php foreach(array_slice($vdBadgesOk, 0, 3) as $b): ?><span><?= htmlspecialchars($b['title']); ?></span><?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="vd-btn-row" style="margin-top:28px;">
          <a href="<?= base_url('store/' . $slug . '/about'); ?>" class="vd-btn vd-btn-sage">About us <?= vd_arrow_svg(); ?></a>
          <?php if($waNum): ?><a href="https://wa.me/<?= $waNum; ?>" target="_blank" rel="noopener" class="vd-btn vd-btn-outline-light"><?= vd_wa_svg(); ?> WhatsApp</a><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
        break;

    case 'promo_banner':
        if(empty($promo_banners)) break;
        foreach($promo_banners as $promo):
        $vd_promo_i++;
        $pImg = vd_banner_img($promo, 1200);
        $pBtnT = trim($promo->button_text ?? '') ?: 'Shop the set';
        $pBtnU = trim($promo->button_url ?? '') ?: base_url('store/' . $slug . '/products');
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <div class="vd-set <?= ($vd_promo_i % 2 === 0) ? 'invert' : ''; ?>">
      <div class="vd-set-media">
        <?php if($pImg): ?><img src="<?= $pImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? ''); ?>" loading="lazy"><?php else: ?><span class="vd-card-ph" style="font-size:72px;">&#9733;</span><?php endif; ?>
      </div>
      <div class="vd-set-copy">
        <span class="vd-kicker"><?= htmlspecialchars(trim($promo->banner_subtitle ?? '') ?: 'Routine set'); ?></span>
        <h2 class="vd-h2" style="margin-top:14px;"><?= $vd_em(vd_sec_title($section, $promo->banner_title ?: 'The routine, *together*')); ?></h2>
        <?php $pTxt = trim($promo->banner_text ?? $promo->banner_description ?? ''); ?>
        <?php if($pTxt): ?><p class="vd-lead"><?= nl2br(htmlspecialchars($pTxt)); ?></p><?php endif; ?>
        <div class="vd-btn-row">
          <a href="<?= htmlspecialchars($pBtnU); ?>" class="vd-btn vd-btn-primary"><?= htmlspecialchars($pBtnT); ?> <?= vd_arrow_svg(); ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
        endforeach;
        break;

    case 'instagram_gallery':
        if(empty($instagram_posts)) break;
        $posts = array_slice($instagram_posts, 0, 4);
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <div class="vd-sec-head">
      <div>
        <span class="vd-kicker">From the journal</span>
        <h2 class="vd-h2" style="margin-top:12px;"><?= $vd_em(vd_sec_title($section, 'Notes on *skin*')); ?></h2>
        <?php if($s = vd_sec_sub($section)): ?><p class="vd-lead"><?= htmlspecialchars($s); ?></p><?php endif; ?>
      </div>
      <?php if(!empty($social_links['instagram'])): ?><a href="<?= htmlspecialchars($social_links['instagram']); ?>" target="_blank" rel="noopener" class="vd-link">Follow us <?= vd_arrow_svg(); ?></a><?php endif; ?>
    </div>
    <div class="vd-journal">
      <?php foreach($posts as $pi => $post):
        $postImg = $post->image_url ?? $post->media_url ?? '';
        $postCap = trim($post->caption ?? '') ?: 'Read on Instagram';
        $postUrl = $post->link_url ?? $post->permalink ?? ($social_links['instagram'] ?? '#');
      ?>
      <a href="<?= htmlspecialchars($postUrl); ?>" target="_blank" rel="noopener" class="vd-post <?= $pi === 0 ? 'vd-journal-lead' : ''; ?>">
        <div class="vd-post-img"><?php if($postImg): ?><img src="<?= htmlspecialchars($postImg); ?>" alt="" loading="lazy"><?php endif; ?></div>
        <div class="vd-post-body">
          <div class="vd-post-k">Journal</div>
          <div class="vd-post-t"><?= htmlspecialchars(mb_strimwidth($postCap, 0, 140, '…')); ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
        break;

    case 'testimonials':
        if(empty($testimonials)) break;
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <?php $vd_sec_open($section, 'In their *words*.'); ?>
    <div class="vd-quotes">
      <?php foreach(array_slice($testimonials, 0, 3) as $t):
        $tPhoto = (!empty($t->customer_photo) && file_exists($t->customer_photo)) ? base_url($t->customer_photo) : '';
        $tName  = $t->customer_name ?? 'Customer';
        $stars  = min(5, max(1, (int)($t->rating ?? 5)));
      ?>
      <figure class="vd-quote">
        <div class="vd-quote-stars"><?= str_repeat('&#9733;', $stars) . str_repeat('&#9734;', 5 - $stars); ?></div>
        <blockquote class="vd-quote-text">&ldquo;<?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?>&rdquo;</blockquote>
        <figcaption class="vd-quote-by">
          <?php if($tPhoto): ?><img src="<?= $tPhoto; ?>" alt="<?= htmlspecialchars($tName); ?>" loading="lazy"><?php else: ?><span class="ph"><?= htmlspecialchars(mb_substr($tName, 0, 1)); ?></span><?php endif; ?>
          <?= htmlspecialchars($tName); ?>
        </figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
        break;

    case 'brands':
        if(empty($brands)) break;
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <?php $vd_sec_open($section, 'Brands we *stock*.'); ?>
    <div class="vd-brands">
      <?php foreach($brands as $brand):
        $bUrl  = !empty($brand->brand_url) ? $brand->brand_url : '';
        $bLogo = (!empty($brand->brand_logo) && file_exists($brand->brand_logo)) ? base_url($brand->brand_logo) : '';
      ?>
      <<?= $bUrl ? 'a href="' . htmlspecialchars($bUrl) . '" target="_blank" rel="noopener"' : 'span'; ?> class="vd-brand">
        <?php if($bLogo): ?><img src="<?= $bLogo; ?>" alt="<?= htmlspecialchars($brand->brand_name); ?>" loading="lazy"><?php endif; ?>
        <?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?>
      </<?= $bUrl ? 'a' : 'span'; ?>>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
        break;

    case 'faqs':
        if(empty($faqs)) break;
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <?php $vd_sec_open($section, 'Questions, *answered*.'); ?>
    <div class="vd-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="vd-faq">
        <button class="vd-faq-q" onclick="vdFaq(this)" aria-expanded="false"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></button>
        <div class="vd-faq-a"><p><?= nl2br(htmlspecialchars($faq->answer ?? $faq->faq_answer ?? '')); ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
        break;

    case 'contact_section':
        $hasContact = !empty($settings->store_phone) || !empty($settings->store_email) || !empty($settings->store_address) || $waNum;
        if(!$hasContact) break;
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <?php $vd_sec_open($section, 'Get in *touch*.'); ?>
    <div class="vd-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="vd-contact">
        <div class="vd-contact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="vd-contact-l">Call us</div>
        <div class="vd-contact-v"><a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>"><?= htmlspecialchars($settings->store_phone); ?></a></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="vd-contact">
        <div class="vd-contact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="vd-contact-l">Email</div>
        <div class="vd-contact-v"><a href="mailto:<?= htmlspecialchars($settings->store_email); ?>"><?= htmlspecialchars($settings->store_email); ?></a></div>
      </div>
      <?php endif; ?>
      <?php if($waNum): ?>
      <div class="vd-contact">
        <div class="vd-contact-ic" style="background:#25D366;color:#fff;"><?= vd_wa_svg(); ?></div>
        <div class="vd-contact-l">WhatsApp</div>
        <div class="vd-contact-v"><a href="https://wa.me/<?= $waNum; ?>" target="_blank" rel="noopener"><?= htmlspecialchars($settings->whatsapp_number); ?></a></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_address)): ?>
      <div class="vd-contact">
        <div class="vd-contact-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
        <div class="vd-contact-l">Visit the shop</div>
        <div class="vd-contact-v">
          <?php if(!empty($settings->footer_address_url)): ?>
          <a href="<?= htmlspecialchars($settings->footer_address_url); ?>" target="_blank" rel="noopener"><?= nl2br(htmlspecialchars($settings->store_address)); ?></a>
          <?php else: ?>
          <?= nl2br(htmlspecialchars($settings->store_address)); ?>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php
        break;

    case 'whatsapp_cta':
        if(!$waNum || !($settings->show_whatsapp_cta ?? 1)) break;
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <div class="vd-cta">
      <h2 class="vd-h2"><?= $vd_em(vd_sec_title($section, 'Not sure where to *start*?')); ?></h2>
      <p class="vd-lead"><?= htmlspecialchars(vd_sec_sub($section, 'Tell us about your skin on WhatsApp — a real person will help you build a routine.')); ?></p>
      <div class="vd-btn-row">
        <a href="https://wa.me/<?= $waNum; ?>?text=<?= rawurlencode('Hello ' . ($store->store_name ?? '') . ', I would like some skincare advice.'); ?>" target="_blank" rel="noopener" class="vd-btn vd-btn-wa"><?= vd_wa_svg(); ?> Chat on WhatsApp</a>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="vd-btn vd-btn-ghost">Shop all products</a>
      </div>
    </div>
  </div>
</section>
<?php
        break;

    case 'newsletter':
        $newsImg = vd_store_img($settings, 1000);
        if(!$newsImg && !empty($hero_banners)){
            $newsImg = vd_banner_img(end($hero_banners), 1000);
        }
?>
<section class="vd-sec">
  <div class="vd-news <?= $newsImg ? '' : 'no-media'; ?>">
    <?php if($newsImg): ?><div class="vd-news-media"><img src="<?= $newsImg; ?>" alt="" loading="lazy"></div><?php endif; ?>
    <div class="vd-news-copy">
      <span class="vd-kicker"><?= htmlspecialchars(vd_sec_sub($section) ?: 'The letter'); ?></span>
      <h2 class="vd-h2" style="margin-top:14px;"><?= $vd_em(vd_sec_title($section, $settings->newsletter_title ?: 'Useful skincare notes, *twice* a month.')); ?></h2>
      <?php $newsSub = $settings->newsletter_subtitle ?? ''; if($newsSub): ?>
      <p class="vd-lead"><?= htmlspecialchars($newsSub); ?></p>
      <?php endif; ?>
      <form class="vd-news-form" onsubmit="return mpNewsletterSubmit(event)">
        <input type="email" name="email" placeholder="you@example.com" required autocomplete="email">
        <button type="submit">Join the letter</button>
      </form>
      <div class="vd-news-note">Free to join. Unsubscribe whenever you like.</div>
    </div>
  </div>
</section>
<?php
        break;

    case 'store_hours':
        if(empty($business_hours)) break;
?>
<section class="vd-sec">
  <div class="vd-wrap">
    <?php $vd_sec_open($section, 'Opening *hours*.'); ?>
    <div class="vd-hours">
      <?php foreach($business_hours as $line):
        $parts = preg_split('/[:|]/', $line, 2);
      ?>
      <div class="vd-hours-row"><span><?= htmlspecialchars(trim($parts[0])); ?></span><span><?= htmlspecialchars(trim($parts[1] ?? '')); ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
        break;

    endswitch;
endforeach;
