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

    public function __construct(){
        $this->CI =& get_instance();
        $this->CI->load->model('storefront_model');
    }

    /**
     * Initialize theme engine for a store
     */
    public function init($storeId = null, $previewTheme = null){
        if(!$storeId) $storeId = get_current_store_id();

        // Ensure themes are seeded before any lookup
        $this->CI->storefront_model->seedThemesIfEmpty();

        $this->settings = $this->CI->storefront_model->getSettings($storeId);
        $this->store = get_store_details($storeId);

        // Determine active theme
        $themeId = $previewTheme ?: $this->settings->theme_id;
        $this->theme = $this->getTheme($themeId);
        if(!$this->theme){
            $this->theme = $this->getDefaultTheme();
        }
        return $this;
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

    /**
     * Load a themed view. Falls back to legacy views if theme view doesn't exist.
     * Wraps content in shared layout for inner pages.
     */
    public function view($view, $data = [], $return = false){
        $themeKey = $this->theme ? $this->theme->theme_key : 'general_retail';
        $themeView = 'themes/' . $themeKey . '/' . $view;

        $merged = array_merge($data, [
            'theme' => $this->theme,
            'settings' => $this->settings,
            'store' => $this->store,
            'theme_key' => $themeKey
        ]);

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
            return $this->CI->load->view('themes/shared/layout', $merged, $return);
        }

        // Final fallback to legacy storefront views (no layout wrapping)
        $legacyView = 'storefront/' . $view;
        return $this->CI->load->view($legacyView, $merged, $return);
    }

    /**
     * Generate CSS variables for the active theme
     */
    public function cssVariables(){
        $primary = $this->settings->primary_color ?: ($this->theme->default_primary_color ?? '#3B82F6');
        $secondary = $this->settings->secondary_color ?: ($this->theme->default_secondary_color ?? '#10B981');
        $font = $this->settings->font_family ?: ($this->theme->default_font_family ?? 'Inter');
        $btnStyle = $this->settings->button_style ?: 'rounded';
        $footerBg = $this->settings->footer_bg_color ?: '#0F172A';
        $footerText = $this->settings->footer_text_color ?: '#94A3B8';
        $headerText = $this->settings->header_text_color ?: 'inherit';
        $buttonColor = $this->settings->button_color ?: ($this->settings->primary_color ?: '#3B82F6');

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
            'Open Sans' => 'Open+Sans:wght@300;400;500;600;700'
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

    // Helper: darken hex color
    private function darken($hex, $percent){
        $hex = ltrim($hex, '#');
        $r = max(0, hexdec(substr($hex, 0, 2)) - round(2.55 * $percent));
        $g = max(0, hexdec(substr($hex, 2, 2)) - round(2.55 * $percent));
        $b = max(0, hexdec(substr($hex, 4, 2)) - round(2.55 * $percent));
        return '#' . sprintf('%02x', $r) . sprintf('%02x', $g) . sprintf('%02x', $b);
    }
}
