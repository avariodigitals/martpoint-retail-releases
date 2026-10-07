<?php
/**
 * Printing service visuals.
 *
 * Print services rarely have photographs: the schema has `db_services.service_image`
 * but in practice it is empty, and print categories have no image column at all.
 * Rather than render a grey "No Image" box, every service is given a deliberate,
 * recognisable glyph derived from its name.
 *
 * If a real image IS supplied it is used, with a graceful fallback on error.
 */

if (!function_exists('mp_print_service_icon')) {
    /**
     * Return the SVG path data for a service, chosen by keyword.
     * Falls back to a neutral print-sheet glyph — never a bare initial.
     */
    function mp_print_service_icon($name)
    {
        $map = [
            'large format' => '<path d="M3 5h18v10H3z"/><path d="M7 19h10"/><path d="M12 15v4"/>',
            'banner'       => '<path d="M3 5h18v10H3z"/><path d="M7 19h10"/><path d="M12 15v4"/>',
            'signage'      => '<path d="M12 3v18"/><path d="M5 6h14v6H5z"/><path d="M9 21h6"/>',
            'fabricat'     => '<path d="M12 3v18"/><path d="M5 6h14v6H5z"/><path d="M9 21h6"/>',
            'digital'      => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M7 20h10"/><path d="M12 16v4"/>',
            'booklet'      => '<path d="M4 4h11a3 3 0 0 1 3 3v13H7a3 3 0 0 1-3-3z"/><path d="M18 20h2V7"/>',
            'binding'      => '<path d="M6 3h12v18H6z"/><path d="M9 3v18"/>',
            'apparel'      => '<path d="M8 3l4 2 4-2 5 3-2 4-2-1v12H7V9L5 10 3 6z"/>',
            'dtf'          => '<path d="M4 6h16v12H4z"/><path d="M8 10h8"/><path d="M8 14h5"/>',
            'garment'      => '<path d="M8 3l4 2 4-2 5 3-2 4-2-1v12H7V9L5 10 3 6z"/>',
            'graphic'      => '<path d="M12 3a9 9 0 1 0 0 18h1a2 2 0 0 0 0-4h-1a5 5 0 0 1 0-10h1a2 2 0 0 0 0-4z"/><circle cx="7.5" cy="10.5" r="1"/><circle cx="12" cy="7" r="1"/>',
            'design'       => '<path d="M12 3a9 9 0 1 0 0 18h1a2 2 0 0 0 0-4h-1a5 5 0 0 1 0-10h1a2 2 0 0 0 0-4z"/><circle cx="7.5" cy="10.5" r="1"/><circle cx="12" cy="7" r="1"/>',
            'finishing'    => '<path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h10"/>',
            'lamination'   => '<path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h10"/>',
            'vehicle'      => '<path d="M3 13l2-5h14l2 5v5h-3"/><circle cx="7.5" cy="18" r="1.5"/><circle cx="16.5" cy="18" r="1.5"/>',
            'rush'         => '<path d="M13 2L4 14h6l-1 8 9-12h-6z"/>',
            'same-day'     => '<path d="M13 2L4 14h6l-1 8 9-12h-6z"/>',
            'sticker'      => '<path d="M12 3l9 9-9 9-9-9z"/>',
            'packaging'    => '<path d="M3 7l9-4 9 4-9 4z"/><path d="M3 7v10l9 4 9-4V7"/>',
            'print'        => '<path d="M7 8V3h10v5"/><rect x="4" y="8" width="16" height="8" rx="2"/><path d="M7 16h10v5H7z"/>',
        ];
        $n = strtolower((string)$name);
        foreach ($map as $needle => $path) {
            if (strpos($n, $needle) !== false) return $path;
        }
        // Neutral fallback: a print sheet, always legible, never an initial.
        return '<path d="M7 8V3h10v5"/><rect x="4" y="8" width="16" height="8" rx="2"/><path d="M7 16h10v5H7z"/>';
    }
}

if (!function_exists('mp_print_service_thumb')) {
    /**
     * Render a service thumbnail: real image when present, otherwise an
     * intentional icon tile. Keeps every card visually complete.
     *
     * @param object $service  db_services row
     * @param int    $size     tile size in px
     * @return string HTML
     */
    function mp_print_service_thumb($service, $size = 44)
    {
        $img = trim((string)($service->service_image ?? ''));
        $alt = htmlspecialchars($service->service_name ?? 'Service');

        // A stored path that does not resolve would render a broken image, so
        // only use it when the file actually exists on disk.
        $usable = false;
        if ($img !== '') {
            $fs = FCPATH . ltrim($img, '/');
            if (file_exists($fs)) { $usable = true; }
            elseif (preg_match('#^https?://#i', $img)) { $usable = true; }
        }

        if ($usable) {
            $src = preg_match('#^https?://#i', $img) ? $img : base_url($img);
            // onerror swaps to an icon tile if the image fails at runtime.
            return '<span class="mp-svc-thumb" style="width:' . (int)$size . 'px;height:' . (int)$size . 'px;">'
                 . '<img src="' . htmlspecialchars($src) . '" alt="' . $alt . '" loading="lazy"'
                 . ' onerror="this.parentNode.classList.add(\'is-fallback\');this.remove();">'
                 . '</span>';
        }

        return '<span class="mp-svc-thumb is-icon" style="width:' . (int)$size . 'px;height:' . (int)$size . 'px;" aria-hidden="true">'
             . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"'
             . ' stroke-linecap="round" stroke-linejoin="round" width="' . (int)round($size * 0.46) . '" height="' . (int)round($size * 0.46) . '">'
             . mp_print_service_icon($service->service_name ?? '')
             . '</svg></span>';
    }
}

if (!function_exists('mp_print_thumb_css')) {
    /**
     * Shared styling for the service thumbnail, injected once per page.
     *
     * Colours default to the original teal so existing printing themes are
     * unaffected. A theme whose branding uses a different accent can pass its
     * own tint/ink so icon tiles blend with the rest of the page instead of
     * fighting it.
     *
     * @param string|null $tint Background tint for the icon tile
     * @param string|null $ink  Icon stroke colour (usually the accent)
     */
    function mp_print_thumb_css($tint = null, $ink = null)
    {
        $tint = $tint ?: '#e6f4f8';
        $ink  = $ink  ?: '#0E7490';
        return '<style>
.mp-svc-thumb{display:inline-flex;align-items:center;justify-content:center;border-radius:12px;overflow:hidden;flex:0 0 auto;background:' . htmlspecialchars($tint) . ';color:' . htmlspecialchars($ink) . '}
.mp-svc-thumb img{width:100%;height:100%;object-fit:cover;display:block}
.mp-svc-thumb.is-fallback{background:#eef4f7;color:#7c8f99}
</style>';
    }
}
