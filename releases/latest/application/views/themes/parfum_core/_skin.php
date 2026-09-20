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

/* Resolve a section heading: backend config_json title → section_label → fallback. */
if (!function_exists('pf_sec_title')) {
    function pf_sec_title($section, $fallback = ''){
        if(!empty($section->config_json)){
            $cfg = json_decode($section->config_json, true);
            if(!empty($cfg['title'])) return $cfg['title'];
        }
        if(!empty($section->section_label)) return $section->section_label;
        return $fallback;
    }
}

/* Optional section subtitle from backend config_json. */
if (!function_exists('pf_sec_sub')) {
    function pf_sec_sub($section){
        if(!empty($section->config_json)){
            $cfg = json_decode($section->config_json, true);
            if(!empty($cfg['subtitle'])) return $cfg['subtitle'];
        }
        return '';
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
        $hasWa     = !empty($settings->whatsapp_number);
        ?>
        <div class="pf-card">
          <a href="<?= $url; ?>" class="pf-card-media" aria-label="<?= htmlspecialchars($p->item_name); ?>">
            <?php if($hasDisc && $pct > 0): ?><span class="pf-badge pf-badge-sale">−<?= $pct; ?>%</span><?php endif; ?>
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
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php
    }
}
