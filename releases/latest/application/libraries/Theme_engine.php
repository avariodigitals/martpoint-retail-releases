<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Theme Engine - Resolves and loads premium storefront themes
 */
class Theme_engine {

    private $CI;
    private $theme = null;
    private $settings = null;
    private $store = null;
    private $industry = null;
    // Resolved once per request by catalogueMode(); null means "not asked yet".
    private $catalogueMode = null;

    public function __construct(){
        $this->CI =& get_instance();
        $this->CI->load->model('storefront_model');
    }

    /**
     * Initialize theme engine for a store
     */
    public function init($storeId = null, $previewTheme = null){
        if(!$storeId) $storeId = get_current_store_id();

        // init() is called more than once in a process — the acceptance suite
        // switches stores to compare a service trade against a retail one.
        // Anything memoised must be dropped here or the second store inherits
        // the first store's answers.
        //
        // $this->theme MUST be part of that reset. It was not, and because every
        // resolution step below is guarded by `if(!$this->theme)`, a second
        // init() for a different store kept the FIRST store's theme and skipped
        // resolution entirely. Observed: initialising store 2 (printing) after
        // store 1 (nylon) left store 2 showing the nylon theme.
        $this->catalogueMode = null;
        $this->theme = null;
        $this->industry = null;

        // Ensure themes are seeded before any lookup
        $this->CI->storefront_model->seedThemesIfEmpty();

        $this->settings = $this->CI->storefront_model->getSettings($storeId);
        $this->store = get_store_details($storeId);

        $businessProfile = mp_get_store_profile($storeId);
        $this->industry = $businessProfile['industry_type'] ?? 'general_retail';

        // Build the allowed theme set for this industry. This is the single source
        // of truth: any stored theme (preview, profile, settings, legacy) that is
        // not in this set is rejected and the engine falls back to an allowed theme.
        $allowedThemes = $this->CI->storefront_model->getThemesByIndustryForStore($this->industry, true);
        $allowedById = [];
        $allowedByKey = [];
        foreach($allowedThemes as $t){
            $allowedById[$t->id] = $t;
            $allowedByKey[$t->theme_key] = $t;
        }

        // The store's own stored selections are always valid. Preset/theme
        // mappings can change over time, and a saved merchant choice must
        // never be silently invalidated by an industry remap.
        //
        // BUT "always valid" must not mean "valid for any industry". This
        // admission step previously accepted ANY stored theme key or theme_id
        // regardless of what it was designed for, and every resolution step
        // below reads from these maps — so one stale row was enough to paint a
        // store with another trade's storefront. That is a real, observed fault:
        // a `nylon_polythene` store carried a leftover print_inkpress theme_id
        // and rendered a printing shopfront.
        //
        // A theme is only admitted if its `industry` matches the industry group
        // resolved for THIS store (or the theme declares no industry at all, in
        // which case it is generic and safe). Anything else is ignored, which
        // lets resolution fall through to a correct theme instead of the wrong
        // one.
        $thisIndustry = $this->CI->storefront_model->normalizeThemeIndustry($this->industry);
        $themeFitsIndustry = function ($t) use ($thisIndustry) {
            if (!$t) { return false; }
            $tInd = trim((string) ($t->industry ?? ''));
            if ($tInd === '') { return true; }
            return $this->CI->storefront_model->normalizeThemeIndustry($tInd) === $thisIndustry;
        };

        foreach(array_filter([$businessProfile['theme_key'] ?? null, $this->store->storefront_theme_key ?? null]) as $k){
            if(!isset($allowedByKey[$k]) && ($row = $this->getThemeByKey($k))){
                if (!$themeFitsIndustry($row)) {
                    log_message('error', 'Theme_engine: refusing stored theme "' . $k
                        . '" for industry "' . $this->industry . '" (theme industry "'
                        . ($row->industry ?? '') . '") — store ' . $storeId);
                    continue;
                }
                $allowedByKey[$k] = $row;
                $allowedById[$row->id] = $row;
                $allowedThemes[] = $row;
            }
        }
        if(!empty($this->settings->theme_id) && !isset($allowedById[$this->settings->theme_id]) && ($row = $this->getTheme($this->settings->theme_id))){
            if (!$themeFitsIndustry($row)) {
                // Do NOT admit it. Leave the stale id in place but unresolved —
                // steps 2/4/5 below will pick a correct theme, and the write-back
                // at the end of init() will repoint theme_id to that theme.
                log_message('error', 'Theme_engine: ignoring stale theme_id '
                    . $this->settings->theme_id . ' (theme industry "'
                    . ($row->industry ?? '') . '") for industry "' . $this->industry
                    . '", store ' . $storeId);
            } else {
                $allowedById[$row->id] = $row;
                $allowedByKey[$row->theme_key] = $row;
                $allowedThemes[] = $row;
            }
        }

        // 1. Preview mode takes precedence, but it must belong to the industry
        if($previewTheme && isset($allowedById[$previewTheme])){
            $this->theme = $allowedById[$previewTheme];
        }

        // 2. Business Profile theme — the industry default for a store that has
        //    never chosen a theme.
        //
        //    This deliberately yields to step 3 below. It used to run FIRST and
        //    unconditionally, and init() then wrote the resolved theme back into
        //    `db_storefront_settings.theme_id` — so a merchant picking a new
        //    theme in Appearance had their choice overwritten on the very next
        //    page load. The profile key is a PROVISIONING default (set when the
        //    industry is configured), not an ongoing preference, so an explicit
        //    `theme_id` from the Appearance screen must outrank it. Without this
        //    order, switching theme appeared to do nothing at all: whichever
        //    theme the profile named was reinstated every time.
        if(!$this->theme && empty($this->settings->theme_id)){
            $businessThemeKey = !empty($businessProfile['theme_key']) ? $businessProfile['theme_key'] : null;
            if($businessThemeKey && isset($allowedByKey[$businessThemeKey])){
                $this->theme = $allowedByKey[$businessThemeKey];
            }
        }

        // 3. Explicit choice saved from the Appearance screen.
        if(!$this->theme && !empty($this->settings->theme_id) && isset($allowedById[$this->settings->theme_id])){
            $this->theme = $allowedById[$this->settings->theme_id];
        }

        // 3b. An explicit theme_id that did not survive the industry filter: the
        //     store picked something its industry no longer offers. Fall back to
        //     the profile default rather than skipping straight to step 5, so a
        //     re-classified store lands on a sensible theme for its trade.
        if(!$this->theme){
            $businessThemeKey = !empty($businessProfile['theme_key']) ? $businessProfile['theme_key'] : null;
            if($businessThemeKey && isset($allowedByKey[$businessThemeKey])){
                $this->theme = $allowedByKey[$businessThemeKey];
            }
        }

        // 4. Fallback: db_store.storefront_theme_key
        if(!$this->theme && $this->store && !empty($this->store->storefront_theme_key) && isset($allowedByKey[$this->store->storefront_theme_key])){
            $this->theme = $allowedByKey[$this->store->storefront_theme_key];
        }

        // 5. Final fallback: first allowed theme, or general_retail if nothing else
        if(!$this->theme){
            $this->theme = !empty($allowedThemes) ? $allowedThemes[0] : $this->getDefaultTheme();
        }

        // Keep db_storefront_settings.theme_id in sync with the resolved theme —
        // but ONLY to repair a value that is missing or points at a theme this
        // store may no longer use.
        //
        // This used to write unconditionally whenever the resolved theme differed
        // from the stored id, which is precisely how a merchant's explicit choice
        // got erased: resolution ran through a lower-priority default, disagreed
        // with the saved id, and overwrote it. Re-deriving an authoritatively
        // chosen value from a fallback is never right. If the stored id already
        // names a theme this store is allowed to use, it IS the source of truth
        // and resolving it again would be circular.
        $storedId   = !empty($this->settings->theme_id) ? (int) $this->settings->theme_id : null;
        $storedOk   = $storedId !== null && isset($allowedById[$storedId]);
        if($this->theme && !$storedOk && $storedId !== (int) $this->theme->id){
            $this->CI->storefront_model->saveSettings($storeId, ['theme_id' => $this->theme->id]);
            $this->settings = $this->CI->storefront_model->getSettings($storeId);
        }

        return $this;
    }

    public function getIndustry(){
        return $this->industry;
    }

    public function getTheme($themeId){
        if(!$themeId) return null;
        return $this->CI->db->where('id', $themeId)->where('status', 1)->get('db_storefront_themes')->row();
    }

    public function getThemeByKey($key){
        return $this->CI->db->where('theme_key', $key)->where('status', 1)->get('db_storefront_themes')->row();
    }

    public function getDefaultTheme(){
        return $this->CI->db->where('theme_key', 'general_retail')->where('status', 1)->get('db_storefront_themes')->row();
    }

    public function getAllThemes(){
        return $this->CI->db->where('status', 1)->order_by('sort_order', 'asc')->get('db_storefront_themes')->result();
    }

    public function currentTheme(){
        return $this->theme;
    }

    public function settings(){
        return $this->settings;
    }

    public function store(){
        return $this->store;
    }

    /* ------------------------------------------------------------------ */
    /*  Catalogue mode — what this store sells online                     */
    /* ------------------------------------------------------------------ */

    /**
     * What the storefront sells: 'services', 'products' or 'both'.
     *
     * Lives on db_storefront_settings.catalogue_mode (migration 4.0.9.96),
     * which the printing theme views already read as $catalogue_mode — but
     * nothing ever produced it, so those views always saw null and fell back
     * to 'services'.
     *
     * An unrecognised or missing value resolves to a VALID mode rather than
     * null: a store must always have a defined catalogue shape, and a bad row
     * must not be able to half-render a theme. The fallback is 'products'
     * because that is the long-standing default for every store created
     * before service modes existed — defaulting to 'services' would silently
     * hide the catalogue of an ordinary retail shop.
     *
     * Memoised: the views and the flag helpers all ask for this during one
     * render, and it never changes mid-request.
     */
    public function catalogueMode(){
        if ($this->catalogueMode !== null) {
            return $this->catalogueMode;
        }
        $mode = '';
        try {
            if ($this->settings && isset($this->settings->catalogue_mode)) {
                $mode = (string) $this->settings->catalogue_mode;
            }
        } catch (Throwable $e) {
            $mode = '';
        }
        $this->catalogueMode = in_array($mode, ['services', 'products', 'both'], true)
            ? $mode
            : 'products';
        return $this->catalogueMode;
    }

    /** Does this storefront offer services? */
    public function sellsServices(): bool {
        return in_array($this->catalogueMode(), ['services', 'both'], true);
    }

    /** Does this storefront offer products? */
    public function sellsProducts(): bool {
        return in_array($this->catalogueMode(), ['products', 'both'], true);
    }

    /**
     * Should the storefront show a cart?
     *
     * Three gates, all of which must pass:
     *  1. the catalogue mode must include products;
     *  2. the store must allow products online at all
     *     (allow_products_online — how a service business sells a few goods);
     *  3. the resolved industry must not be a service-only trade.
     *
     * (3) is what keeps a printing shop's cart off in 'products' mode: the
     * business is service-led, so a cart appearing because someone set a mode
     * wrong would be worse than no cart. 'both' is an explicit operator
     * decision and overrides it — that is the whole point of the mode.
     */
    public function cartEnabled(): bool {
        if (!$this->sellsProducts()) {
            return false;
        }
        $allowedOnline = true;
        try {
            if ($this->settings && isset($this->settings->allow_products_online)) {
                $allowedOnline = (int) $this->settings->allow_products_online === 1;
            }
        } catch (Throwable $e) {
            $allowedOnline = true;
        }
        if (!$allowedOnline) {
            return false;
        }
        // A service-led trade in a non-'both' mode never gets a cart.
        if ($this->isServiceStore() && $this->catalogueMode() !== 'both') {
            return false;
        }
        return true;
    }

    /**
     * Is this a service business rather than a goods one?
     *
     * Keyed on the resolved industry preset, which is what decides the shape
     * of the whole storefront. Unknown industries are treated as NOT a
     * service store — the conservative answer, because calling a retail shop
     * a service store would strip its catalogue and cart.
     */
    public function isServiceStore(): bool {
        $serviceIndustries = [
            'printing', 'tailoring', 'physiotherapy_rehabilitation',
            'salon', 'spa', 'repair', 'consulting',
        ];
        return in_array((string) $this->industry, $serviceIndustries, true);
    }

    /**
     * The catalogue flags a theme view needs, in one call.
     *
     * The printing theme reads $catalogue_mode / $sells_services /
     * $sells_products directly, so those have to reach the view — derived here
     * rather than recomputed per template.
     */
    public function catalogueFlags(): array {
        return [
            'catalogue_mode'  => $this->catalogueMode(),
            'sells_services'  => $this->sellsServices(),
            'sells_products'  => $this->sellsProducts(),
            'cart_enabled'    => $this->cartEnabled(),
            'is_service_store' => $this->isServiceStore(),
        ];
    }

    /**
     * Load a themed view. Falls back to legacy views if theme view doesn't exist.
     * Wraps content in shared layout for inner pages.
     */
    public function view($view, $data = [], $return = false){
        $themeKey = $this->theme ? $this->theme->theme_key : 'general_retail';
        $themeView = 'themes/' . $themeKey . '/' . $view;

        // The print_* themes call mp_print_thumb_css() to build their SVG
        // thumbnails. That helper was loaded in exactly one place — the
        // acceptance controller — so every real storefront render of
        // print_works / print_inkpress / print_neonprint / print_papercraft
        // died with "Call to undefined function", a 500 on the shop itself.
        // Load it here, where the theme is actually rendered, so it travels
        // with the theme instead of depending on who called us.
        $this->CI->load->helper('print_visuals');

        // The three approved printing designs live in ONE file,
        // views/themes/printing/home.php, which switches its own variant off
        // $theme_key (press / atelier / desk). They were built as a complete
        // replacement — own navline, hero, grid, quote form and footer, with
        // their own --pr-* tokens — and never read a single --mp-* variable.
        //
        // Two things kept them unreachable:
        //   1. `Theme_engine` builds the path from the theme key, and the three
        //      printing keys resolve to their own print_* directories, so
        //      themes/printing/ was never addressed at all.
        //   2. Storefront::index() renders the 'store' view, but the design was
        //      authored as home.php. The stub directories happen to contain BOTH
        //      home.php and store.php, which is why print_inkpress still worked
        //      while themes/printing/ silently fell through to the retail shell.
        //
        // So map the three commercial designs onto the printing file, treating
        // 'store' as the design's entry point. print_works keeps its own view —
        // the user asked for it to stay separate, and it is a different design.
        $printDesignKeys = ['print_inkpress', 'print_papercraft', 'print_neonprint'];
        $isPrintDesign = in_array($themeKey, $printDesignKeys, true);
        if ($isPrintDesign) {
            // 'store' is what the storefront asks for; 'home' is the file's own
            // name. Accept either so a future caller using 'home' still lands.
            $printView = $view === 'store' ? 'home' : $view;
            if (file_exists(APPPATH . 'views/themes/printing/' . $printView . '.php')) {
                $themeView = 'themes/printing/' . $printView;
            }
        }

        // Catalogue flags go in FIRST so an explicit value from the caller
        // still wins. Theme templates (print_works/store.php) read
        // $catalogue_mode / $sells_services / $sells_products directly, and
        // nothing was supplying them — so a service theme rendered as though
        // it had no catalogue at all.
        $merged = array_merge($this->catalogueFlags(), $data, [
            'theme' => $this->theme,
            'settings' => $this->settings,
            'store' => $this->store,
            'theme_key' => $themeKey,
            'logo_url' => $this->logoUrl()
        ]);

        // The printing home reads $print_categories to build its category
        // filters and to map products onto print categories. Nothing supplied
        // it, so the catalogue section rendered empty. Only fetched for a print
        // design, and only when the table exists, so no other industry pays for
        // the query.
        if ($isPrintDesign && !isset($merged['print_categories'])) {
            $merged['print_categories'] = [];
            if ($this->CI->db->table_exists('db_print_categories')) {
                $sid = (int) ($this->store->id ?? get_current_store_id());
                $merged['print_categories'] = $this->CI->db
                    ->where('store_id', $sid)->order_by('sort_order', 'asc')
                    ->get('db_print_categories')->result();
            }
        }

        $viewPath = null;
        if(file_exists(APPPATH . 'views/' . $themeView . '.php')){
            $viewPath = $themeView;
        } else {
            $sharedView = 'themes/shared/' . $view;
            if(file_exists(APPPATH . 'views/' . $sharedView . '.php')){
                $viewPath = $sharedView;
            }
        }

        if($viewPath){
            // Buffer content for layout wrapping
            $merged['content'] = $this->CI->load->view($viewPath, $merged, true);
            // Make all theme data available to nested views (header, footer, scripts)
            $this->CI->load->vars($merged);

            // A printing design gets the PRINT layout, not the retail one.
            //
            // The retail layout is not merely a wrapper: it emits its own
            // `:root { --mp-primary: <settings.primary_color> }` and renders an
            // mp-* header, nav, announcement bar and footer. That single :root
            // is what made every printing theme look identical — the store's one
            // configured colour overwrote whichever theme was selected, which is
            // exactly the "the theme does not change" report. The print designs
            // carry their own --pr-* palette and their own chrome, so they must
            // not be wrapped in retail markup at all.
            //
            // Everything else in the catalogue keeps the retail layout untouched.
            $layout = $isPrintDesign ? 'themes/printing/layout' : 'themes/shared/layout';
            return $this->CI->load->view($layout, $merged, $return);
        }

        // Final fallback to legacy storefront views (no layout wrapping)
        $legacyView = 'storefront/' . $view;
        return $this->CI->load->view($legacyView, $merged, $return);
    }

    /**
     * Generate CSS variables for the active theme
     */
    /**
     * Resolve a brand colour ($which = 'primary' | 'secondary').
     *
     * WHY THIS IS NOT A ONE-LINER
     * ---------------------------
     * The obvious form —
     *     $settings->primary_color ?: $theme->default_primary_color
     * — looks correct and is silently broken. `db_storefront_settings`
     * declares `primary_color VARCHAR(20) NULL DEFAULT '#3B82F6'` (and
     * Storefront_model writes that same literal on settings creation), so the
     * column is NEVER empty. The `?:` therefore never falls through to the
     * theme, and every storefront paints #3B82F6 regardless of the theme
     * selected. That is the observed "theme does not change when I switch"
     * report: switching Press & Co → PrintDesk → Atelier left all three blue.
     *
     * The merchant's colour must still win when they deliberately set one —
     * "if the user decides to change the colour, that's fine". So precedence is
     * decided by *provenance*, not by emptiness:
     *
     *   1. `theme_primary_color` / `theme_secondary_color`
     *      A colour saved while a specific theme was active. Highest priority:
     *      it is an explicit per-theme override.
     *   2. `primary_color` / `secondary_color` — the store's saved brand colour,
     *      but ONLY when it differs from the stock default, i.e. the merchant
     *      genuinely edited it. The merchant's choice is the whole point of the
     *      Appearance screen, so it must beat the theme's design.
     *   3. The theme's own designed palette (`default_primary_color`).
     *      This is what makes switching themes visibly change the storefront,
     *      and it governs whenever nobody has expressed an opinion.
     *   4. The hard-coded fallback.
     *
     * A stored value that is exactly the stock default is treated as "never
     * customised" and is skipped, because it is indistinguishable from the
     * column default and cannot have been a deliberate choice. That is the
     * single fact that stops the seeded '#3B82F6' from flattening every theme.
     */
    public function resolveBrandColor($which = 'primary'){
        $which = ($which === 'secondary') ? 'secondary' : 'primary';

        $perTheme  = $this->settings->{'theme_' . $which . '_color'} ?? null;
        $storeWide = $this->settings->{$which . '_color'} ?? null;
        $themeDef  = $this->theme->{'default_' . $which . '_color'} ?? null;
        $fallback  = ($which === 'secondary') ? '#10B981' : '#3B82F6';

        // 1. An explicit per-theme override always wins.
        if (!empty($perTheme) && !$this->isStockDefault($perTheme, $which)) {
            return $perTheme;
        }
        // 2. A deliberately chosen store colour beats the theme's design.
        if (!empty($storeWide) && !$this->isStockDefault($storeWide, $which)) {
            return $storeWide;
        }
        // 3. Otherwise the theme's own designed palette governs, so switching
        //    themes changes the storefront.
        if (!empty($themeDef)) {
            return $themeDef;
        }
        // 4. Last resort: whatever exists.
        return $storeWide ?: $fallback;
    }

    /**
     * Is this colour just the untouched column default?
     *
     * #3B82F6 / #10B981 are the values `db_storefront_settings` and
     * Storefront_model write on creation, so seeing them means the merchant
     * never picked a colour — not that they chose blue. Matching is
     * case-insensitive and tolerant of a missing leading hash.
     *
     * Public because the Appearance form uses it to decide whether to label a
     * swatch "(custom)" or "(theme default)".
     */
    public function isStockDefault($color, $which = 'primary'){
        $stock = ($which === 'secondary') ? ['#10B981', '10B981'] : ['#3B82F6', '3B82F6'];
        return in_array(strtoupper(ltrim(trim((string)$color), '#')), array_map(function ($c) {
            return strtoupper(ltrim($c, '#'));
        }, $stock), true);
    }

    public function cssVariables(){
        $primary = $this->resolveBrandColor('primary');
        $secondary = $this->resolveBrandColor('secondary');
        $font = $this->settings->font_family ?: ($this->theme->default_font_family ?? 'Inter');
        $btnStyle = $this->settings->button_style ?: 'rounded';
        $footerBg = $this->settings->footer_bg_color ?: '#0F172A';
        $footerText = $this->settings->footer_text_color ?: '#94A3B8';
        $headerText = $this->settings->header_text_color ?: 'inherit';
        $buttonColor = $this->settings->button_color ?: ($primary ?: '#3B82F6');

        $radius = ($btnStyle === 'pill') ? '9999px' : (($btnStyle === 'square') ? '4px' : '12px');
        $radiusSm = ($btnStyle === 'pill') ? '9999px' : (($btnStyle === 'square') ? '2px' : '8px');

        return "
            :root {
                --mp-primary: {$primary};
                --mp-primary-dark: " . $this->darken($primary, 15) . ";
                --mp-secondary: {$secondary};
                --mp-font: '{$font}', -apple-system, BlinkMacSystemFont, sans-serif;
                --mp-radius: {$radius};
                --mp-radius-sm: {$radiusSm};
                --mp-dark: #0F172A;
                --mp-gray: #64748B;
                --mp-light-gray: #F1F5F9;
                --mp-border: #E2E8F0;
                --mp-white: #ffffff;
                --mp-success: #10B981;
                --mp-warning: #F59E0B;
                --mp-danger: #EF4444;
                --mp-footer-bg: {$footerBg};
                --mp-footer-text: {$footerText};
                --mp-header-text: {$headerText};
                --mp-button: {$buttonColor};
                --mp-button-dark: " . $this->darken($buttonColor, 15) . ";
            }
        ";
    }

    /**
     * Load Google Fonts for the active theme
     */
    public function googleFontLink(){
        $font = $this->settings->font_family ?: ($this->theme->default_font_family ?? 'Inter');
        $fonts = [
            'Inter' => 'Inter:wght@300;400;500;600;700;800',
            'Playfair Display' => 'Playfair+Display:wght@400;500;600;700;800',
            'Montserrat' => 'Montserrat:wght@300;400;500;600;700;800',
            'Roboto' => 'Roboto:wght@300;400;500;700',
            'Poppins' => 'Poppins:wght@300;400;500;600;700',
            'Lora' => 'Lora:wght@400;500;600;700',
            'Open Sans' => 'Open+Sans:wght@300;400;500;600;700',
            'Cormorant Garamond' => 'Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600',
            'Marcellus' => 'Marcellus',
            'Jost' => 'Jost:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400'
        ];
        $query = $fonts[$font] ?? $fonts['Inter'];
        return 'https://fonts.googleapis.com/css2?family=' . $query . '&display=swap';
    }

    /**
     * Get homepage sections for this store
     */
    public function homepageSections(){
        $storeId = $this->settings->store_id ?? 0;
        $rows = $this->CI->db
            ->where('store_id', $storeId)
            ->where('is_enabled', 1)
            ->order_by('display_order', 'asc')
            ->get('db_storefront_homepage_sections')
            ->result();
        // Fresh installs / stores that never opened the builder: seed defaults
        // so the homepage is never blank.
        if(empty($rows) && $storeId && !$this->CI->db->where('store_id', $storeId)->count_all_results('db_storefront_homepage_sections')){
            $this->CI->storefront_model->resetHomepageSections($storeId);
            $rows = $this->CI->db
                ->where('store_id', $storeId)
                ->where('is_enabled', 1)
                ->order_by('display_order', 'asc')
                ->get('db_storefront_homepage_sections')
                ->result();
        }
        $sections = [];
        foreach($rows as $r){
            $sections[$r->section_key] = $r;
        }
        return $sections;
    }

    /**
     * Get active banners for this store
     */
    public function activeBanners($limit = 5, $bannerType = null){
        $storeId = $this->settings->store_id ?? 0;
        $today = date('Y-m-d');
        $this->CI->db->where('store_id', $storeId)->where('status', 1);
        if($bannerType){
            $this->CI->db->where('banner_type', $bannerType);
        }
        return $this->CI->db
            ->group_start()
                ->where('start_date IS NULL', null, false)
                ->or_where('start_date <=', $today)
            ->group_end()
            ->group_start()
                ->where('end_date IS NULL', null, false)
                ->or_where('end_date >=', $today)
            ->group_end()
            ->order_by('display_order', 'asc')
            ->limit($limit)
            ->get('db_storefront_banners')
            ->result();
    }

    /**
     * Get custom domain info
     */
    public function customDomain(){
        $storeId = $this->settings->store_id ?? 0;
        return $this->CI->db
            ->where('store_id', $storeId)
            ->where('connection_status', 'connected')
            ->order_by('id', 'desc')
            ->get('db_storefront_domains')
            ->row();
    }

    /**
     * Get social links array
     */
    public function socialLinks(){
        $s = $this->settings;
        $links = [];
        if(!empty($s->instagram_url)) $links['instagram'] = $s->instagram_url;
        if(!empty($s->facebook_url)) $links['facebook'] = $s->facebook_url;
        if(!empty($s->tiktok_url)) $links['tiktok'] = $s->tiktok_url;
        if(!empty($s->x_url)) $links['x'] = $s->x_url;
        if(!empty($s->youtube_url)) $links['youtube'] = $s->youtube_url;
        return $links;
    }

    /**
     * Get business hours parsed
     */
    public function businessHours(){
        if(empty($this->settings->business_hours)) return [];
        $lines = explode("\n", $this->settings->business_hours);
        $hours = [];
        foreach($lines as $line){
            $line = trim($line);
            if($line) $hours[] = $line;
        }
        return $hours;
    }

    /**
     * Get store logo URL
     */
    public function logoUrl(){
        if(!empty($this->settings->store_logo) && file_exists($this->settings->store_logo)){
            return base_url($this->settings->store_logo);
        }
        if(!empty($this->store->store_logo) && file_exists($this->store->store_logo)){
            return base_url($this->store->store_logo);
        }
        return null;
    }

    /**
     * Get favicon URL
     */
    public function faviconUrl(){
        if(!empty($this->settings->favicon) && file_exists($this->settings->favicon)){
            return base_url($this->settings->favicon);
        }
        $logo = $this->logoUrl();
        if($logo){
            return $logo;
        }
        return base_url('uploads/site/icon.webp');
    }

    /**
     * Build store URL
     */
    public function storeUrl($path = ''){
        $slug = $this->settings->store_slug ?? '';
        $url = base_url('store/' . $slug);
        if($path) $url .= '/' . ltrim($path, '/');
        return $url;
    }

    /**
     * Get category image URL helper
     */
    public function categoryImage($cat){
        if(!empty($cat->category_image) && file_exists($cat->category_image)){
            return base_url($cat->category_image);
        }
        return null;
    }

    /**
     * Get store currency info
     */
    public function getStoreCurrency($storeId = null){
        $storeId = $storeId ?: ($this->settings->store_id ?? get_current_store_id());
        $row = $this->CI->db->query("SELECT a.currency as symbol, a.currency_code as code, b.currency_placement as placement FROM db_currency a, db_store b WHERE a.id = b.currency_id AND b.id = ? LIMIT 1", [$storeId])->row();
        if(!$row){
            return ['symbol' => '&#8358;', 'code' => 'NGN', 'placement' => 'Left'];
        }
        return ['symbol' => $row->symbol, 'code' => $row->code, 'placement' => $row->placement];
    }

    /**
     * Get storefront brands
     */
    public function storefrontBrands(){
        return $this->CI->storefront_model->getStorefrontBrands($this->settings->store_id ?? 0);
    }

    /**
     * Get storefront testimonials
     */
    public function storefrontTestimonials(){
        return $this->CI->storefront_model->getStorefrontTestimonials($this->settings->store_id ?? 0);
    }

    /**
     * Get storefront instagram posts
     */
    public function storefrontInstagram(){
        return $this->CI->storefront_model->getStorefrontInstagram($this->settings->store_id ?? 0);
    }

    /**
     * Get storefront FAQs
     */
    public function storefrontFaqs(){
        return $this->CI->storefront_model->getStorefrontFaqs($this->settings->store_id ?? 0);
    }

    /**
     * Get categories that have online services
     */
    public function serviceCategories($storeId = null){
        $storeId = $storeId ?: ($this->settings->store_id ?? get_current_store_id());
        return $this->CI->storefront_model->getServiceCategories($storeId);
    }

    /**
     * Get active branches/warehouses for this store
     */
    public function branches($storeId = null){
        $storeId = $storeId ?: ($this->settings->store_id ?? get_current_store_id());
        return $this->CI->db
            ->where('store_id', $storeId)
            ->where('status', 1)
            ->where('warehouse_type', 'Custom')
            ->order_by('warehouse_name', 'asc')
            ->get('db_warehouse')
            ->result();
    }

    // Helper: darken hex color
    private function darken($hex, $percent){
        $hex = ltrim($hex, '#');
        $r = max(0, hexdec(substr($hex, 0, 2)) - round(2.55 * $percent));
        $g = max(0, hexdec(substr($hex, 2, 2)) - round(2.55 * $percent));
        $b = max(0, hexdec(substr($hex, 4, 2)) - round(2.55 * $percent));
        return '#' . sprintf('%02x', $r) . sprintf('%02x', $g) . sprintf('%02x', $b);
    }
}
