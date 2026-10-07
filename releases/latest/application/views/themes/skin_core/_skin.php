<?php
/**
 * Skin Core — skin tokens + shared helpers.
 *
 * Three skincare/cosmetics skins. Each theme dir is a thin shell that sets
 * $SK_SKIN and includes the engine view. All CSS lives in header.php (loaded
 * on every page) — page views are markup only.
 */
if (!isset($SK_SKIN)) $SK_SKIN = 'botanica';

$SK_SKINS = [
    'botanica' => [ /* Botanical editorial — warm cream, forest ink, sage */
        'theme_key'      => 'botanica',
        'bg'             => '#FAF7F1',
        'surface'        => '#FFFFFF',
        'card'           => '#FFFFFF',
        'ink'            => '#22302A',
        'muted'          => '#6E7A70',
        'accent'         => '#4A7C59',
        'accent_soft'    => '#E7EDE4',
        'accent_ink'     => '#FFFFFF',
        'border'         => '#E5E0D3',
        'soft'           => '#F1EDE3',
        'kicker_color'   => '#4A7C59',
        'font_display'   => "'Fraunces', Georgia, serif",
        'font_body'      => "'Jost', 'Inter', -apple-system, sans-serif",
        'font_kicker'    => "'Jost', sans-serif",
        'fonts_url'      => 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400;1,9..144,500&family=Jost:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap',
        'display_italic' => true,
        'display_case'   => 'none',
        'radius'         => '20px',
        'btn_radius'     => '999px',
        'hero_align'     => 'left',
        'header_style'   => 'center',
        'media_shape'    => 'arch',
        'featured_layout'=> 'rail',
        'story_invert'   => false,
    ],
    'derma' => [ /* Clinical minimal lab — white, ink, derma teal, mono labels */
        'theme_key'      => 'derma_pure',
        'bg'             => '#FFFFFF',
        'surface'        => '#FFFFFF',
        'card'           => '#FFFFFF',
        'ink'            => '#101418',
        'muted'          => '#5C6670',
        'accent'         => '#2F6B5E',
        'accent_soft'    => '#E7F0ED',
        'accent_ink'     => '#FFFFFF',
        'border'         => '#E4E7EA',
        'soft'           => '#F5F7F6',
        'kicker_color'   => '#2F6B5E',
        'font_display'   => "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
        'font_body'      => "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
        'font_kicker'    => "'IBM Plex Mono', 'SFMono-Regular', monospace",
        'fonts_url'      => 'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@300;400;500;600;700;800&display=swap',
        'display_italic' => false,
        'display_case'   => 'uppercase',
        'radius'         => '8px',
        'btn_radius'     => '8px',
        'hero_align'     => 'left',
        'header_style'   => 'inline',
        'media_shape'    => 'sharp',
        'featured_layout'=> 'grid',
        'story_invert'   => true,
    ],
    'terra' => [ /* Warm earth-luxe — sand, clay ink, terracotta */
        'theme_key'      => 'terra_glow',
        'bg'             => '#F8F0E6',
        'surface'        => '#FFFDF8',
        'card'           => '#FFFDF8',
        'ink'            => '#33231B',
        'muted'          => '#8A7160',
        'accent'         => '#B5643C',
        'accent_soft'    => '#F3E3D3',
        'accent_ink'     => '#FFFFFF',
        'border'         => '#EBDFCE',
        'soft'           => '#F4EADA',
        'kicker_color'   => '#B5643C',
        'font_display'   => "'Cormorant Garamond', Georgia, serif",
        'font_body'      => "'Jost', 'Inter', -apple-system, sans-serif",
        'font_kicker'    => "'Jost', sans-serif",
        'fonts_url'      => 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Jost:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap',
        'display_italic' => true,
        'display_case'   => 'none',
        'radius'         => '24px',
        'btn_radius'     => '999px',
        'hero_align'     => 'center',
        'header_style'   => 'center',
        'media_shape'    => 'rounded',
        'featured_layout'=> 'grid',
        'story_invert'   => false,
    ],
];

$SK = $SK_SKINS[$SK_SKIN] ?? $SK_SKINS['botanica'];
$SK['skin'] = $SK_SKIN;

/* Signature "ritual" strip — per-skin copy. */
$SK_RITUALS = [
    'botanica' => [
        'kicker' => 'The Ritual',
        'title'  => 'Three steps. Nothing more.',
        'sub'    => 'Every formula is built to slot into a simple daily ritual',
        'steps'  => [
            ['n' => '01', 't' => 'Cleanse', 'd' => 'Start fresh — gentle, pH-balanced cleansing that never strips the skin barrier.'],
            ['n' => '02', 't' => 'Treat',   'd' => 'Targeted creams and serums, blended in small batches from raw botanicals.'],
            ['n' => '03', 't' => 'Seal',    'd' => 'Rich butters lock in moisture so skin stays soft, supple and protected all day.'],
        ],
    ],
    'derma' => [
        'kicker' => 'The Protocol',
        'title'  => 'Formulated. Tested. Measured.',
        'sub'    => 'A disciplined approach to skin health — no filler, no fragrance',
        'steps'  => [
            ['n' => '01', 't' => 'Analyse',   'd' => 'We map your skin type and concerns before recommending a single product.'],
            ['n' => '02', 't' => 'Formulate', 'd' => 'Each batch is compounded to spec — exact actives, exact percentages.'],
            ['n' => '03', 't' => 'Maintain',  'd' => 'Simple AM/PM routines tracked over time. Results you can measure.'],
        ],
    ],
    'terra' => [
        'kicker' => 'The Glow Ritual',
        'title'  => 'Slow beauty, made by hand',
        'sub'    => 'Whipped, poured and cured in small batches — the way it should be',
        'steps'  => [
            ['n' => '01', 't' => 'Melt',    'd' => 'Raw shea and botanical butters, gently warmed and whipped to silk.'],
            ['n' => '02', 't' => 'Nourish', 'd' => 'Cold-pressed oils feed the skin — nothing synthetic, nothing wasted.'],
            ['n' => '03', 't' => 'Glow',    'd' => 'A protective finish that leaves skin soft, luminous and cared for.'],
        ],
    ],
];
$SK_RITUAL = $SK_RITUALS[$SK_SKIN] ?? $SK_RITUALS['botanica'];

/* Colour / font helpers for Appearance overrides. */
if (!function_exists('sk_hex')) {
    function sk_hex($v){
        $v = trim((string)$v);
        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $v) ? $v : '';
    }
    function sk_expand($hex){
        $hex = ltrim($hex, '#');
        if(strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        return $hex;
    }
    function sk_mix($hex, $pct){
        $hex = sk_expand($hex);
        $r = (int)round(hexdec(substr($hex,0,2)) + (255 - hexdec(substr($hex,0,2))) * $pct / 100);
        $g = (int)round(hexdec(substr($hex,2,2)) + (255 - hexdec(substr($hex,2,2))) * $pct / 100);
        $b = (int)round(hexdec(substr($hex,4,2)) + (255 - hexdec(substr($hex,4,2))) * $pct / 100);
        return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
    }
    function sk_contrast($hex){
        $hex = sk_expand($hex);
        $lum = (0.299 * hexdec(substr($hex,0,2)) + 0.587 * hexdec(substr($hex,2,2)) + 0.114 * hexdec(substr($hex,4,2))) / 255;
        return $lum > 0.6 ? '#1C1917' : '#FFFFFF';
    }
}

/* ---------- Appearance settings (Online Store → Appearance) ----------
   The skin palette is the default; a stored value overrides the matching
   token only when it differs from the generic Appearance defaults, so
   stores that never customised Appearance keep the designed look. */
$SK['btn_bg']       = '';
$SK['btn_ink']      = '';
$SK['header_ink']   = '';
$SK['footer_bg']    = '';
$SK['footer_text']  = '';
$SK['footer_head']  = '';
if (isset($settings) && is_object($settings)) {
    $skPrimary = sk_hex($settings->primary_color ?? '');
    if ($skPrimary && strcasecmp($skPrimary, '#3B82F6') !== 0) {
        $SK['accent']       = $skPrimary;
        $SK['kicker_color'] = $skPrimary;
        $SK['accent_soft']  = sk_mix($skPrimary, 88);
    }
    $skSecondary = sk_hex($settings->secondary_color ?? '');
    if ($skSecondary && strcasecmp($skSecondary, '#10B981') !== 0) {
        $SK['ink'] = $skSecondary;
    }
    $skButton = sk_hex($settings->button_color ?? '');
    if ($skButton && strcasecmp($skButton, '#3B82F6') !== 0) {
        $SK['btn_bg']  = $skButton;
        $SK['btn_ink'] = sk_contrast($skButton);
    }
    $skFont = trim($settings->font_family ?? '');
    if ($skFont !== '' && strcasecmp($skFont, 'Inter') !== 0) {
        $serif = in_array($skFont, ['Playfair Display', 'Lora'], true);
        $SK['font_body']    = "'" . $skFont . "', -apple-system, BlinkMacSystemFont, sans-serif";
        $SK['font_display'] = "'" . $skFont . "', " . ($serif ? "Georgia, 'Times New Roman', serif" : "-apple-system, BlinkMacSystemFont, sans-serif");
        $SK['font_kicker']  = "'" . $skFont . "', sans-serif";
    }
    $skBtnStyle = strtolower(trim($settings->button_style ?? ''));
    if ($skBtnStyle === 'pill')       $SK['btn_radius'] = '999px';
    elseif ($skBtnStyle === 'square') $SK['btn_radius'] = '4px';
    $SK['header_ink']  = sk_hex($settings->header_text_color ?? '');
    if ($SK['header_ink'] && strcasecmp($SK['header_ink'], '#000000') === 0) $SK['header_ink'] = '';
    $skFootBg  = sk_hex($settings->footer_bg_color ?? '');
    $skFootTxt = sk_hex($settings->footer_text_color ?? '');
    if ($skFootBg && strcasecmp($skFootBg, '#0F172A') !== 0) $SK['footer_bg'] = $skFootBg;
    if ($skFootTxt && strcasecmp($skFootTxt, '#94A3B8') !== 0) $SK['footer_text'] = $skFootTxt;
    if ($SK['footer_bg'] && !$SK['footer_text']) $SK['footer_text'] = sk_contrast($SK['footer_bg']);
    if ($SK['footer_bg'] || $SK['footer_text']) $SK['footer_head'] = $SK['footer_bg'] ? sk_contrast($SK['footer_bg']) : $SK['ink'];
}

/* Resolve a section heading: backend config_json title → section_label → fallback. */
if (!function_exists('sk_sec_title')) {
    function sk_sec_title($section, $fallback = ''){
        if(!empty($section->config_json)){
            $cfg = json_decode($section->config_json, true);
            if(!empty($cfg['title'])) return $cfg['title'];
        }
        if(!empty($section->section_label)){
            $label = $section->section_label;
            if(preg_match('/_\d+$/', (string)($section->section_key ?? ''))){
                $label = preg_replace('/\s*\(\d+\)$/', '', $label);
            }
            return $label;
        }
        return $fallback;
    }
}

/* Optional section subtitle from backend config_json. */
if (!function_exists('sk_sec_sub')) {
    function sk_sec_sub($section, $fallback = ''){
        if(!empty($section->config_json)){
            $cfg = json_decode($section->config_json, true);
            if(!empty($cfg['subtitle'])) return $cfg['subtitle'];
        }
        return $fallback;
    }
}

/* Shared product card — one definition used by every view. */
if (!function_exists('sk_card')) {
    function sk_card($p, $cur, $settings, $slug){
        $price     = $p->effective_price ?? $p->sales_price;
        $oldPrice  = $p->original_price ?? $p->sales_price;
        $hasDisc   = $oldPrice > $price;
        $pct       = $hasDisc ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img       = (!empty($p->item_image) && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 600) : '';
        $url       = base_url('store/' . $slug . '/product/' . $p->id);
        $name      = htmlspecialchars(addslashes($p->item_name));
        $stock     = (int)($p->stock ?? 0);
        $soldOut   = $stock <= 0 && empty($settings->allow_backorder);
        $hasWa     = !empty($settings->whatsapp_number) && ($settings->allow_whatsapp ?? 1);
        $isNew     = !empty($p->is_new_arrival);
        ?>
        <div class="sk-card">
          <a href="<?= $url; ?>" class="sk-card-media" aria-label="<?= htmlspecialchars($p->item_name); ?>">
            <?php if($hasDisc && $pct > 0): ?><span class="sk-badge sk-badge-sale" <?= $isNew ? 'style="top:38px;"' : ''; ?>>−<?= $pct; ?>%</span><?php endif; ?>
            <?php if($isNew): ?><span class="sk-badge sk-badge-new">New</span><?php endif; ?>
            <?php if($soldOut): ?><span class="sk-badge sk-badge-out">Sold out</span><?php endif; ?>
            <?php if($img): ?>
            <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
            <?php else: ?>
            <div class="sk-card-ph"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
            <?php endif; ?>
          </a>
          <div class="sk-card-body">
            <?php if(!empty($p->category_name)): ?><div class="sk-card-range"><?= htmlspecialchars($p->category_name); ?></div><?php endif; ?>
            <a href="<?= $url; ?>" class="sk-card-name"><?= htmlspecialchars($p->item_name); ?></a>
            <div class="sk-card-foot">
              <div class="sk-card-price"><?= sf_currency($price, $cur); ?><?php if($hasDisc): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
              <div class="sk-card-actions">
                <button class="sk-btn-add" <?= $soldOut ? 'disabled' : ''; ?> onclick="addToCart(<?= $p->id; ?>,'product','<?= $name; ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $stock; ?>)" aria-label="Add to cart">Add</button>
                <?php if($hasWa): ?>
                <button class="sk-btn-wa" onclick="<?= $soldOut ? "window.open('https://wa.me/" . preg_replace('/[^0-9]/','',$settings->whatsapp_number ?? '') . "?text=" . rawurlencode('Hello, is ' . $p->item_name . ' back in stock?') . "','_blank')" : "skWaOrder(" . $p->id . ",'" . $name . "'," . $price . ",'" . $p->item_image . "')"; ?>" title="<?= $soldOut ? 'Ask about availability' : 'WhatsApp'; ?>" aria-label="<?= $soldOut ? 'Ask about availability' : 'Order on WhatsApp'; ?>">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php
    }
}
