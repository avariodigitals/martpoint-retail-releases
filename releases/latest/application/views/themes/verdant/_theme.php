<?php
/**
 * Verdant — design tokens + shared helpers.
 *
 * Calm editorial skincare flagship: cream canvas, deep forest bands, sage
 * accents and a high-contrast serif display face. Every heading, image and
 * block of copy on the storefront is driven by the Online Store backend
 * (Appearance, Banners, Homepage Builder, Settings, FAQs, Testimonials,
 * Instagram/Journal, Brands). Nothing here is hard-coded brand copy.
 *
 * Loaded by header.php (every page) and by each page view.
 */
if (isset($VD)) return;

$VD = [
    'theme_key'    => 'verdant',
    'bg'           => '#F4F1EA',   /* cream page canvas            */
    'surface'      => '#FFFFFF',
    'soft'         => '#ECE8DE',   /* warm tile behind imagery     */
    'sage_soft'    => '#E3EBE2',   /* pale sage band               */
    'forest'       => '#1F3A2E',   /* deep forest bands / footer   */
    'forest_deep'  => '#16291F',
    'ink'          => '#1B2A22',
    'muted'        => '#5F6B63',
    'accent'       => '#4F7A5C',   /* sage green                   */
    'accent_ink'   => '#FFFFFF',
    'sage'         => '#C9D8C5',   /* pale sage button on dark     */
    'clay'         => '#B8683F',   /* terracotta secondary         */
    'border'       => '#E1DCD0',
    'radius'       => '22px',
    'radius_sm'    => '14px',
    'btn_radius'   => '999px',
    'font_display' => "'Instrument Serif', 'Fraunces', Georgia, serif",
    'font_body'    => "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
    'fonts_url'    => 'https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700&display=swap',
    /* Appearance-resolved (filled below) */
    'btn_bg'       => '',
    'btn_ink'      => '',
    'header_ink'   => '',
    'footer_bg'    => '',
    'footer_text'  => '',
];

/* ---------- colour helpers ---------- */
if (!function_exists('vd_hex')) {
    function vd_hex($v){
        $v = trim((string)$v);
        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $v) ? $v : '';
    }
    function vd_expand($hex){
        $hex = ltrim($hex, '#');
        if(strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        return $hex;
    }
    function vd_mix($hex, $pct, $to = 'ffffff'){
        $hex = vd_expand($hex); $to = vd_expand($to);
        $out = '#';
        for($i = 0; $i < 6; $i += 2){
            $a = hexdec(substr($hex, $i, 2)); $b = hexdec(substr($to, $i, 2));
            $out .= sprintf('%02x', (int)round($a + ($b - $a) * $pct / 100));
        }
        return $out;
    }
    function vd_contrast($hex){
        $hex = vd_expand($hex);
        $lum = (0.299 * hexdec(substr($hex,0,2)) + 0.587 * hexdec(substr($hex,2,2)) + 0.114 * hexdec(substr($hex,4,2))) / 255;
        return $lum > 0.6 ? '#1B2A22' : '#FFFFFF';
    }
}

/* ---------- Appearance overrides (Online Store → Appearance) ----------
   The designed palette is the default. A stored value only overrides when
   it differs from the generic Appearance defaults, so stores that never
   touched Appearance keep the designed look. */
if (isset($settings) && is_object($settings)) {
    /* White is the generic Appearance default — it means "keep the theme canvas". */
    $bg = vd_hex($settings->background_color ?? '');
    if ($bg && strcasecmp($bg, '#FFFFFF') !== 0) $VD['bg'] = $bg;
    $p = vd_hex($settings->primary_color ?? '');
    if ($p && strcasecmp($p, '#3B82F6') !== 0) {
        $VD['accent']    = $p;
        $VD['sage']      = vd_mix($p, 62);
        $VD['sage_soft'] = vd_mix($p, 86);
    }
    $s = vd_hex($settings->secondary_color ?? '');
    if ($s && strcasecmp($s, '#10B981') !== 0) {
        $VD['forest']      = $s;
        $VD['forest_deep'] = vd_mix($s, 22, '000000');
    }
    $b = vd_hex($settings->button_color ?? '');
    if ($b && strcasecmp($b, '#3B82F6') !== 0) {
        $VD['btn_bg']  = $b;
        $VD['btn_ink'] = vd_contrast($b);
    }
    $f = trim($settings->font_family ?? '');
    if ($f !== '' && strcasecmp($f, 'Inter') !== 0) {
        $serif = in_array($f, ['Instrument Serif', 'Playfair Display', 'Lora', 'Cormorant Garamond', 'Marcellus'], true);
        $VD['font_body']    = "'" . $f . "', -apple-system, BlinkMacSystemFont, sans-serif";
        if ($serif) $VD['font_display'] = "'" . $f . "', Georgia, serif";
        /* Load the picked family — fonts_url only ships the theme's own faces. */
        if (!in_array($f, ['Instrument Serif', 'Inter'], true)) {
            $VD['fonts_url'] .= '&family=' . str_replace(' ', '+', $f) . ':wght@400;500;600;700';
        }
    }
    $bs = strtolower(trim($settings->button_style ?? ''));
    if ($bs === 'square')       { $VD['btn_radius'] = '6px'; }
    elseif ($bs === 'rounded')  { $VD['btn_radius'] = '12px'; }
    $hi = vd_hex($settings->header_text_color ?? '');
    if ($hi && strcasecmp($hi, '#000000') !== 0) $VD['header_ink'] = $hi;
    $fb = vd_hex($settings->footer_bg_color ?? '');
    $ft = vd_hex($settings->footer_text_color ?? '');
    if ($fb && strcasecmp($fb, '#0F172A') !== 0) $VD['footer_bg'] = $fb;
    if ($ft && strcasecmp($ft, '#94A3B8') !== 0) $VD['footer_text'] = $ft;
}
$VD['btn']       = $VD['btn_bg'] ?: $VD['forest'];
$VD['btn_text']  = $VD['btn_bg'] ? $VD['btn_ink'] : '#FFFFFF';
$VD['foot_bg']   = $VD['footer_bg'] ?: $VD['forest_deep'];
$VD['foot_text'] = $VD['footer_text'] ?: 'rgba(255,255,255,0.62)';
$VD['foot_head'] = $VD['footer_bg'] ? vd_contrast($VD['footer_bg']) : '#FFFFFF';

/* ---------- section heading helpers (Homepage Builder → pencil) ---------- */
if (!function_exists('vd_sec_title')) {
    function vd_sec_title($section, $fallback = ''){
        if(!empty($section->config_json)){
            $cfg = json_decode($section->config_json, true);
            if(!empty($cfg['title'])) return $cfg['title'];
        }
        $label = trim((string)($section->section_label ?? ''));
        /* Default labels ("Featured Products", "Featured Categories (2)") are
           builder names, not storefront copy — use the designed fallback. */
        $base = preg_replace('/_\d+$/', '', (string)($section->section_key ?? ''));
        $defaultLabel = ucwords(str_replace('_', ' ', $base));
        $labelClean = preg_replace('/\s*\(\d+\)$/', '', $label);
        if($labelClean === '' || strcasecmp($labelClean, $defaultLabel) === 0 || in_array($labelClean, ['Hero Banner','Trust Badges','Promotional Banner','Store Information','Contact Section','WhatsApp CTA','Newsletter CTA','FAQs'], true)){
            return $fallback;
        }
        return $labelClean;
    }
    function vd_sec_sub($section, $fallback = ''){
        if(!empty($section->config_json)){
            $cfg = json_decode($section->config_json, true);
            if(!empty($cfg['subtitle'])) return $cfg['subtitle'];
        }
        return $fallback;
    }
}

/* ---------- shared product card ---------- */
if (!function_exists('vd_card')) {
    function vd_card($p, $cur, $settings, $slug){
        $price    = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDisc  = $oldPrice > $price;
        $pct      = $hasDisc ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img      = (!empty($p->item_image) && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 640) : '';
        $url      = base_url('store/' . $slug . '/product/' . $p->id);
        $nameJs   = htmlspecialchars(addslashes($p->item_name));
        $stock    = (int)($p->stock ?? 0);
        $soldOut  = $stock <= 0 && empty($settings->allow_backorder);
        $hasWa    = !empty($settings->whatsapp_number) && ($settings->allow_whatsapp ?? 1);
        $isNew    = !empty($p->is_new_arrival);
        ?>
        <article class="vd-card">
          <a href="<?= $url; ?>" class="vd-card-media" aria-label="<?= htmlspecialchars($p->item_name); ?>">
            <?php if($isNew): ?><span class="vd-tag vd-tag-new">New</span><?php endif; ?>
            <?php if($hasDisc && $pct > 0): ?><span class="vd-tag vd-tag-sale"<?= $isNew ? ' style="top:44px;"' : ''; ?>>−<?= $pct; ?>%</span><?php endif; ?>
            <?php if($soldOut): ?><span class="vd-tag vd-tag-out">Sold out</span><?php endif; ?>
            <?php if($img): ?>
            <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
            <?php else: ?>
            <span class="vd-card-ph"><?= htmlspecialchars(mb_substr($p->item_name, 0, 1)); ?></span>
            <?php endif; ?>
          </a>
          <div class="vd-card-body">
            <?php if(!empty($p->category_name)): ?><div class="vd-card-range"><?= htmlspecialchars($p->category_name); ?></div><?php endif; ?>
            <a href="<?= $url; ?>" class="vd-card-name"><?= htmlspecialchars($p->item_name); ?></a>
            <div class="vd-card-foot">
              <div class="vd-card-price"><?= sf_currency($price, $cur); ?><?php if($hasDisc): ?><s><?= sf_currency($oldPrice, $cur); ?></s><?php endif; ?></div>
              <div class="vd-card-actions">
                <button type="button" class="vd-add" <?= $soldOut ? 'disabled' : ''; ?> onclick="addToCart(<?= $p->id; ?>,'product','<?= $nameJs; ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $stock; ?>)"><?= $soldOut ? 'Sold out' : 'Add to bag'; ?></button>
                <?php if($hasWa): ?>
                <button class="vd-wa-ic" onclick="<?= $soldOut ? "window.open('https://wa.me/" . preg_replace('/[^0-9]/','',$settings->whatsapp_number ?? '') . "?text=" . rawurlencode('Hello, is ' . $p->item_name . ' back in stock?') . "','_blank')" : "vdWaOrder(" . $p->id . ",'" . $nameJs . "'," . $price . ",'" . $p->item_image . "')"; ?>" title="<?= $soldOut ? 'Ask about availability' : 'WhatsApp'; ?>" aria-label="<?= $soldOut ? 'Ask about availability' : 'Order on WhatsApp'; ?>"><?= vd_wa_svg(); ?></button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </article>
        <?php
    }
    function vd_wa_svg(){
        return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>';
    }
    function vd_arrow_svg(){
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
    }
    /* First usable image on a banner row, minified. */
    function vd_banner_img($b, $w = 1400){
        if(!$b) return '';
        if(!empty($b->desktop_image) && file_exists($b->desktop_image)) return mp_minified_image_url($b->desktop_image, $w);
        if(!empty($b->mobile_image)  && file_exists($b->mobile_image))  return mp_minified_image_url($b->mobile_image, $w);
        return '';
    }
    /* Store-level fallback image (Appearance → Store Banner). */
    function vd_store_img($settings, $w = 1400){
        foreach(['desktop_banner', 'store_banner', 'mobile_banner'] as $k){
            if(!empty($settings->$k) && file_exists($settings->$k)) return mp_minified_image_url($settings->$k, $w);
        }
        return '';
    }
}
