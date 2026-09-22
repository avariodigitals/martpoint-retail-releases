<?php
/**
 * Parfum Core — skin tokens + shared helpers.
 *
 * Four light, modern, conversion-focused perfumery skins. Each theme dir
 * is a thin shell that sets $PF_SKIN and includes the engine view.
 * All CSS lives in header.php (loaded on every page) — page views are markup only.
 */
if (!isset($PF_SKIN)) $PF_SKIN = 'noir';

$PF_SKINS = [
    'noir' => [ /* Warm ivory luxe — stone canvas, ink, muted gold (Aesop-style) */
        'theme_key'      => 'noir_parfum',
        'bg'             => '#F7F4EE',
        'surface'        => '#FFFFFF',
        'card'           => '#FFFFFF',
        'ink'            => '#1C1917',
        'muted'          => '#78716C',
        'accent'         => '#9C7C4F',
        'accent_soft'    => '#EFE7DA',
        'accent_ink'     => '#FFFFFF',
        'border'         => '#E9E3D8',
        'soft'           => '#EFEAE0',
        'kicker_color'   => '#9C7C4F',
        'font_display'   => "'Fraunces', Georgia, serif",
        'font_body'      => "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
        'font_kicker'    => "'Inter', sans-serif",
        'fonts_url'      => 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400;1,9..144,500&family=Inter:wght@300;400;500;600;700&display=swap',
        'display_italic' => true,
        'display_case'   => 'none',
        'radius'         => '16px',
        'btn_radius'     => '999px',
        'hero_align'     => 'left',
        'header_style'   => 'center',
        'media_shape'    => 'rounded',
        'featured_layout'=> 'rail',
        'story_invert'   => true,
    ],
    'maison' => [ /* Clean white boutique — white, ink, dusty rose */
        'theme_key'      => 'maison_blanche',
        'bg'             => '#FFFFFF',
        'surface'        => '#FFFFFF',
        'card'           => '#FFFFFF',
        'ink'            => '#18181B',
        'muted'          => '#71717A',
        'accent'         => '#B76E79',
        'accent_soft'    => '#F9F0F1',
        'accent_ink'     => '#FFFFFF',
        'border'         => '#ECECEC',
        'soft'           => '#F6F3F1',
        'kicker_color'   => '#B76E79',
        'font_display'   => "'DM Serif Display', Georgia, serif",
        'font_body'      => "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
        'font_kicker'    => "'Inter', sans-serif",
        'fonts_url'      => 'https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&display=swap',
        'display_italic' => false,
        'display_case'   => 'none',
        'radius'         => '18px',
        'btn_radius'     => '999px',
        'hero_align'     => 'center',
        'header_style'   => 'center',
        'media_shape'    => 'rounded',
        'featured_layout'=> 'grid',
        'story_invert'   => false,
    ],
    'royale' => [ /* Warm sand modern — cream, espresso ink, amber terracotta */
        'theme_key'      => 'oud_royale',
        'bg'             => '#FBF6EE',
        'surface'        => '#FFFFFF',
        'card'           => '#FFFFFF',
        'ink'            => '#29211A',
        'muted'          => '#7D7268',
        'accent'         => '#B45309',
        'accent_soft'    => '#F6E8D8',
        'accent_ink'     => '#FFFFFF',
        'border'         => '#EDE4D6',
        'soft'           => '#F3EBDD',
        'kicker_color'   => '#B45309',
        'font_display'   => "'Marcellus', Georgia, serif",
        'font_body'      => "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
        'font_kicker'    => "'Inter', sans-serif",
        'fonts_url'      => 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Marcellus&display=swap',
        'display_italic' => false,
        'display_case'   => 'none',
        'radius'         => '20px',
        'btn_radius'     => '999px',
        'hero_align'     => 'left',
        'header_style'   => 'center',
        'media_shape'    => 'arch',
        'featured_layout'=> 'grid',
        'story_invert'   => false,
    ],
    'atelier' => [ /* Crisp apothecary — off-white, ink, deep green, mono labels */
        'theme_key'      => 'atelier_essence',
        'bg'             => '#F8F8F6',
        'surface'        => '#FFFFFF',
        'card'           => '#FFFFFF',
        'ink'            => '#14151A',
        'muted'          => '#6B7280',
        'accent'         => '#2F5D50',
        'accent_soft'    => '#E7EFEB',
        'accent_ink'     => '#FFFFFF',
        'border'         => '#E5E7E3',
        'soft'           => '#EFF1EC',
        'kicker_color'   => '#2F5D50',
        'font_display'   => "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
        'font_body'      => "'Inter', -apple-system, BlinkMacSystemFont, sans-serif",
        'font_kicker'    => "'IBM Plex Mono', 'SFMono-Regular', monospace",
        'fonts_url'      => 'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@300;400;500;600;700;800&display=swap',
        'display_italic' => false,
        'display_case'   => 'uppercase',
        'radius'         => '4px',
        'btn_radius'     => '4px',
        'hero_align'     => 'left',
        'header_style'   => 'inline',
        'media_shape'    => 'sharp',
        'featured_layout'=> 'rail',
        'story_invert'   => true,
    ],
];

$PF = $PF_SKINS[$PF_SKIN] ?? $PF_SKINS['noir'];
$PF['skin'] = $PF_SKIN;

/* Colour / font helpers for Appearance overrides. */
if (!function_exists('pf_hex')) {
    function pf_hex($v){
        $v = trim((string)$v);
        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $v) ? $v : '';
    }
    function pf_expand($hex){
        $hex = ltrim($hex, '#');
        if(strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        return $hex;
    }
    function pf_mix($hex, $pct){
        $hex = pf_expand($hex);
        $r = (int)round(hexdec(substr($hex,0,2)) + (255 - hexdec(substr($hex,0,2))) * $pct / 100);
        $g = (int)round(hexdec(substr($hex,2,2)) + (255 - hexdec(substr($hex,2,2))) * $pct / 100);
        $b = (int)round(hexdec(substr($hex,4,2)) + (255 - hexdec(substr($hex,4,2))) * $pct / 100);
        return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
    }
    function pf_contrast($hex){
        $hex = pf_expand($hex);
        $lum = (0.299 * hexdec(substr($hex,0,2)) + 0.587 * hexdec(substr($hex,2,2)) + 0.114 * hexdec(substr($hex,4,2))) / 255;
        return $lum > 0.6 ? '#1C1917' : '#FFFFFF';
    }
}

/* ---------- Appearance settings (Online Store → Appearance) ----------
   The skin palette is the default; a stored value overrides the matching
   token only when it differs from the generic Appearance defaults, so
   stores that never customised Appearance keep the designed look. */
$PF['btn_bg']       = '';
$PF['btn_ink']      = '';
$PF['header_ink']   = '';
$PF['footer_bg']    = '';
$PF['footer_text']  = '';
$PF['footer_head']  = '';
if (isset($settings) && is_object($settings)) {
    $pfPrimary = pf_hex($settings->primary_color ?? '');
    if ($pfPrimary && strcasecmp($pfPrimary, '#3B82F6') !== 0) {
        $PF['accent']       = $pfPrimary;
        $PF['kicker_color'] = $pfPrimary;
        $PF['accent_soft']  = pf_mix($pfPrimary, 88);
    }
    $pfSecondary = pf_hex($settings->secondary_color ?? '');
    if ($pfSecondary && strcasecmp($pfSecondary, '#10B981') !== 0) {
        $PF['ink'] = $pfSecondary;
    }
    $pfButton = pf_hex($settings->button_color ?? '');
    if ($pfButton && strcasecmp($pfButton, '#3B82F6') !== 0) {
        $PF['btn_bg']  = $pfButton;
        $PF['btn_ink'] = pf_contrast($pfButton);
    }
    $pfFont = trim($settings->font_family ?? '');
    if ($pfFont !== '' && strcasecmp($pfFont, 'Inter') !== 0) {
        $serif = in_array($pfFont, ['Playfair Display', 'Lora'], true);
        $PF['font_body']    = "'" . $pfFont . "', -apple-system, BlinkMacSystemFont, sans-serif";
        $PF['font_display'] = "'" . $pfFont . "', " . ($serif ? "Georgia, 'Times New Roman', serif" : "-apple-system, BlinkMacSystemFont, sans-serif");
        $PF['font_kicker']  = "'" . $pfFont . "', sans-serif";
    }
    $pfBtnStyle = strtolower(trim($settings->button_style ?? ''));
    if ($pfBtnStyle === 'pill')       $PF['btn_radius'] = '999px';
    elseif ($pfBtnStyle === 'square') $PF['btn_radius'] = '4px';
    $PF['header_ink']  = pf_hex($settings->header_text_color ?? '');
    if ($PF['header_ink'] && strcasecmp($PF['header_ink'], '#000000') === 0) $PF['header_ink'] = '';
    $pfFootBg  = pf_hex($settings->footer_bg_color ?? '');
    $pfFootTxt = pf_hex($settings->footer_text_color ?? '');
    if ($pfFootBg && strcasecmp($pfFootBg, '#0F172A') !== 0) $PF['footer_bg'] = $pfFootBg;
    if ($pfFootTxt && strcasecmp($pfFootTxt, '#94A3B8') !== 0) $PF['footer_text'] = $pfFootTxt;
    if ($PF['footer_bg'] && !$PF['footer_text']) $PF['footer_text'] = pf_contrast($PF['footer_bg']);
    if ($PF['footer_bg'] || $PF['footer_text']) $PF['footer_head'] = $PF['footer_bg'] ? pf_contrast($PF['footer_bg']) : $PF['ink'];
}

/* Resolve a section heading: backend config_json title → section_label → fallback. */
if (!function_exists('pf_sec_title')) {
    function pf_sec_title($section, $fallback = ''){
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
if (!function_exists('pf_sec_sub')) {
    function pf_sec_sub($section, $fallback = ''){
        if(!empty($section->config_json)){
            $cfg = json_decode($section->config_json, true);
            if(!empty($cfg['subtitle'])) return $cfg['subtitle'];
        }
        return $fallback;
    }
}

/* Shared product card — one definition used by every view. */
if (!function_exists('pf_card')) {
    function pf_card($p, $cur, $settings, $slug){
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
        <div class="pf-card">
          <a href="<?= $url; ?>" class="pf-card-media" aria-label="<?= htmlspecialchars($p->item_name); ?>">
            <?php if($hasDisc && $pct > 0): ?><span class="pf-badge pf-badge-sale" <?= $isNew ? 'style="top:38px;"' : ''; ?>>−<?= $pct; ?>%</span><?php endif; ?>
            <?php if($isNew): ?><span class="pf-badge pf-badge-new">New</span><?php endif; ?>
            <?php if($soldOut): ?><span class="pf-badge pf-badge-out">Sold out</span><?php endif; ?>
            <?php if($img): ?>
            <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
            <?php else: ?>
            <div class="pf-card-ph"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
            <?php endif; ?>
          </a>
          <div class="pf-card-body">
            <?php if(!empty($p->category_name)): ?><div class="pf-card-fam"><?= htmlspecialchars($p->category_name); ?></div><?php endif; ?>
            <a href="<?= $url; ?>" class="pf-card-name"><?= htmlspecialchars($p->item_name); ?></a>
            <div class="pf-card-foot">
              <div class="pf-card-price"><?= sf_currency($price, $cur); ?><?php if($hasDisc): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
              <div class="pf-card-actions">
                <button class="pf-btn-add" <?= $soldOut ? 'disabled' : ''; ?> onclick="addToCart(<?= $p->id; ?>,'product','<?= $name; ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $stock; ?>)" aria-label="Add to cart">Add</button>
                <?php if($hasWa): ?>
                <button class="pf-btn-wa" onclick="pfWaOrder(<?= $p->id; ?>,'<?= $name; ?>',<?= $price; ?>,'<?= $p->item_image; ?>')" aria-label="Order on WhatsApp">
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
