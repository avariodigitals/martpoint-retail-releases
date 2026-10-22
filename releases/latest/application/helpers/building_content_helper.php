<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Copy and image settings for the two building-materials themes.
 *
 * Values live in db_storefront_settings.theme_content_json under one block per
 * theme key, so switching between Framework Atelier and Vector Systems keeps
 * each theme's own words and photography. Nothing here is demo catalogue
 * data: products, categories, projects and applications always come from the
 * merchant's own records.
 */
function mp_building_theme_keys(){
    return ['framework_atelier', 'vector_systems'];
}

function mp_building_is_theme($themeKey){
    return in_array((string)$themeKey, mp_building_theme_keys(), true);
}

function mp_building_skin($themeKey){
    return $themeKey === 'vector_systems' ? 'vector' : 'atelier';
}

/** Safe foregrounds/surfaces without changing the merchant's saved palette. */
function mp_building_color_tokens($hex){
    $hex = ltrim(trim((string)$hex), '#');
    if(strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    if(!preg_match('/^[0-9a-f]{6}$/i', $hex)) $hex = '142438';
    $linear = [];
    foreach([0, 2, 4] as $offset){
        $channel = hexdec(substr($hex, $offset, 2)) / 255;
        $linear[] = $channel <= .04045 ? $channel / 12.92 : pow(($channel + .055) / 1.055, 2.4);
    }
    $luminance = .2126*$linear[0] + .7152*$linear[1] + .0722*$linear[2];
    return [
        'foreground' => $luminance > .179 ? '#111820' : '#FFFFFF',
        // Dark panels contain white body copy, not just large headings.
        'dark_surface' => 1.05 / ($luminance + .05) >= 7 ? '#'.strtoupper($hex) : '#142438',
    ];
}

function mp_building_fields($themeKey = 'framework_atelier'){
    $vector = $themeKey === 'vector_systems';

    $title = $vector
        ? "Building systems\nengineered for certainty."
        : "Spaces work better\nwhen every detail\nworks together.";
    $support = $vector
        ? 'Entrance, loading and perimeter systems specified around performance, safety and the reality of the site.'
        : 'Door systems, loading technology and secure access—considered from the first drawing to the final handover.';

    $fields = [
        // Brand and chrome
        'brand_name' => ['group' => 'Brand & header', 'label' => 'Brand name (blank uses your store name)', 'type' => 'text', 'default' => ''],
        'brand_tagline' => ['group' => 'Brand & header', 'label' => 'Small line under the brand', 'type' => 'text', 'default' => $vector ? 'ENGINEERED BUILDING SYSTEMS' : 'ARCHITECTURAL SYSTEMS'],
        'header_cta_label' => ['group' => 'Brand & header', 'label' => 'Header button label', 'type' => 'text', 'default' => 'Discuss a project'],

        // Hero
        'hero_eyebrow' => ['group' => 'Homepage hero', 'label' => 'Hero eyebrow', 'type' => 'text', 'default' => 'BUILDING SYSTEMS / ARCHITECTURE / PERFORMANCE'],
        'hero_title' => ['group' => 'Homepage hero', 'label' => 'Hero heading (one line per row; last row is emphasised)', 'type' => 'textarea', 'default' => $title],
        'hero_support' => ['group' => 'Homepage hero', 'label' => 'Hero supporting text', 'type' => 'textarea', 'default' => $support],
        'hero_primary_label' => ['group' => 'Homepage hero', 'label' => 'Primary button label', 'type' => 'text', 'default' => 'Explore systems'],
        'hero_secondary_label' => ['group' => 'Homepage hero', 'label' => 'Secondary button label', 'type' => 'text', 'default' => 'Start a project'],
        'hero_banner_source' => ['group' => 'Homepage hero', 'label' => 'Hero media source', 'type' => 'radio', 'options' => ['theme'=>'Theme-specific image (recommended)', 'store_banner'=>'Shared store banner — only if intended for this theme'], 'default' => 'theme'],
        'hero_image' => ['group' => 'Homepage hero', 'label' => 'Theme-specific hero image', 'type' => 'image', 'default' => ''],
        'hero_side_image' => ['group' => 'Homepage hero', 'label' => 'Right-hand door / project photo (optional; blank uses the door illustration)', 'type' => 'image', 'default' => ''],
        'hero_side_image_alt' => ['group' => 'Homepage hero', 'label' => 'Description of your right-hand photo', 'type' => 'text', 'default' => 'Industrial door system'],

        // Homepage sections
        'home_perspective_eyebrow' => ['group' => 'Homepage sections', 'label' => 'Perspective eyebrow', 'type' => 'text', 'default' => '01 / OUR PERSPECTIVE'],
        'home_perspective_title' => ['group' => 'Homepage sections', 'label' => 'Perspective heading', 'type' => 'textarea', 'default' => "Not just products.\nPart of the architecture."],
        'home_perspective_support' => ['group' => 'Homepage sections', 'label' => 'Perspective side note', 'type' => 'textarea', 'default' => 'Thoughtful specification and dependable engineering shape how every space feels and performs.'],
        'home_perspective_statement' => ['group' => 'Homepage sections', 'label' => 'Perspective statement', 'type' => 'textarea', 'default' => 'We connect the right technical products with the realities of your site, application and operation.'],
        'home_systems_eyebrow' => ['group' => 'Homepage sections', 'label' => 'Systems section eyebrow', 'type' => 'text', 'default' => '02 / EXPLORE SYSTEMS'],
        'home_systems_title' => ['group' => 'Homepage sections', 'label' => 'Systems section heading', 'type' => 'text', 'default' => 'Performance by design.'],
        'home_systems_support' => ['group' => 'Homepage sections', 'label' => 'Systems section side note', 'type' => 'textarea', 'default' => 'A curated range for commercial, industrial and demanding built environments.'],
        'home_feature_eyebrow' => ['group' => 'Homepage sections', 'label' => 'Feature eyebrow', 'type' => 'text', 'default' => '03 / BUILT AROUND YOUR PROJECT'],
        'home_feature_title' => ['group' => 'Homepage sections', 'label' => 'Feature heading', 'type' => 'textarea', 'default' => "Expertise begins\nbefore installation."],
        'home_feature_body' => ['group' => 'Homepage sections', 'label' => 'Feature body', 'type' => 'textarea', 'default' => 'We bring technical requirements, site surveys, specifications, project coordination and long-term service into one connected approach.'],
        'home_feature_image' => ['group' => 'Homepage sections', 'label' => 'Feature image', 'type' => 'image', 'default' => ''],
        'home_applications_eyebrow' => ['group' => 'Homepage sections', 'label' => 'Applications eyebrow', 'type' => 'text', 'default' => '04 / SELECTED APPLICATIONS'],
        'home_applications_title' => ['group' => 'Homepage sections', 'label' => 'Applications heading', 'type' => 'text', 'default' => 'Built for the place.'],
        'home_applications_support' => ['group' => 'Homepage sections', 'label' => 'Applications side note', 'type' => 'textarea', 'default' => 'Explore solutions organized around how environments are used.'],
        'home_call_eyebrow' => ['group' => 'Homepage sections', 'label' => 'Closing panel eyebrow', 'type' => 'text', 'default' => 'FOR ARCHITECTS / DEVELOPERS / FACILITY TEAMS'],
        'home_call_title' => ['group' => 'Homepage sections', 'label' => 'Closing panel heading', 'type' => 'textarea', 'default' => "Have drawings?\nLet's understand the opening."],
        'home_call_body' => ['group' => 'Homepage sections', 'label' => 'Closing panel body', 'type' => 'textarea', 'default' => "Share the project brief. We'll help identify the information needed for a technical quotation."],
        'home_call_cta' => ['group' => 'Homepage sections', 'label' => 'Closing panel button', 'type' => 'text', 'default' => 'Submit an enquiry'],

        // Page headings
        'systems_eyebrow' => ['group' => 'Page headings', 'label' => 'Systems eyebrow', 'type' => 'text', 'default' => 'SYSTEMS / PRODUCT LIBRARY'],
        'systems_title' => ['group' => 'Page headings', 'label' => 'Systems heading', 'type' => 'textarea', 'default' => "Engineered for\nevery opening."],
        'systems_intro' => ['group' => 'Page headings', 'label' => 'Systems introduction', 'type' => 'textarea', 'default' => 'Explore equipment by function, performance and application. Every technical enquiry starts with understanding your context.'],
        'applications_eyebrow' => ['group' => 'Page headings', 'label' => 'Applications eyebrow', 'type' => 'text', 'default' => 'APPLICATIONS / INDUSTRIES'],
        'applications_title' => ['group' => 'Page headings', 'label' => 'Applications heading', 'type' => 'textarea', 'default' => "Every environment\nhas its own logic."],
        'applications_intro' => ['group' => 'Page headings', 'label' => 'Applications introduction', 'type' => 'textarea', 'default' => 'We organize solutions around what a building needs to do—not only around what a product is called.'],
        'projects_eyebrow' => ['group' => 'Page headings', 'label' => 'Projects eyebrow', 'type' => 'text', 'default' => 'PROJECTS / INSPIRATION'],
        'projects_title' => ['group' => 'Page headings', 'label' => 'Projects heading', 'type' => 'textarea', 'default' => "Built context.\nReal decisions."],
        'projects_intro' => ['group' => 'Page headings', 'label' => 'Projects introduction', 'type' => 'textarea', 'default' => 'Explore how entrance and building systems can support the needs of different spaces.'],
        'services_eyebrow' => ['group' => 'Page headings', 'label' => 'Services eyebrow', 'type' => 'text', 'default' => 'SERVICE / THROUGHOUT THE LIFECYCLE'],
        'services_title' => ['group' => 'Page headings', 'label' => 'Services heading', 'type' => 'textarea', 'default' => "Before, during.\nLong after."],
        'services_intro' => ['group' => 'Page headings', 'label' => 'Services introduction', 'type' => 'textarea', 'default' => 'Technical guidance, coordinated supply, installation and support shaped around the needs of each project.'],
        'services_image' => ['group' => 'Page headings', 'label' => 'Services hero image', 'type' => 'image', 'default' => ''],
        'insights_eyebrow' => ['group' => 'Page headings', 'label' => 'Insights eyebrow', 'type' => 'text', 'default' => 'INSIGHTS / RESOURCES'],
        'insights_title' => ['group' => 'Page headings', 'label' => 'Insights heading', 'type' => 'textarea', 'default' => "Knowledge for\nbetter decisions."],
        'insights_intro' => ['group' => 'Page headings', 'label' => 'Insights introduction', 'type' => 'textarea', 'default' => 'Practical thinking for architects, developers, engineers and facility teams.'],
        'about_eyebrow' => ['group' => 'Page headings', 'label' => 'About eyebrow', 'type' => 'text', 'default' => 'ABOUT / OUR APPROACH'],
        'about_title' => ['group' => 'Page headings', 'label' => 'About heading', 'type' => 'textarea', 'default' => "Built on detail.\nFocused on purpose."],
        'about_intro' => ['group' => 'Page headings', 'label' => 'About introduction', 'type' => 'textarea', 'default' => 'Technical guidance, carefully specified building systems and support throughout your project.'],
        'about_body' => ['group' => 'Page headings', 'label' => 'About story', 'type' => 'textarea', 'default' => ''],
        'about_image' => ['group' => 'Page headings', 'label' => 'About hero image', 'type' => 'image', 'default' => ''],
        'contact_eyebrow' => ['group' => 'Page headings', 'label' => 'Enquiry eyebrow', 'type' => 'text', 'default' => 'ENQUIRIES / START A CONVERSATION'],
        'contact_title' => ['group' => 'Page headings', 'label' => 'Enquiry heading', 'type' => 'textarea', 'default' => "Tell us what\nyou're planning."],
        'contact_intro' => ['group' => 'Page headings', 'label' => 'Enquiry introduction', 'type' => 'textarea', 'default' => "Whether you're at concept stage or ready for a technical quotation, start with a short project brief."],
        'contact_aside_title' => ['group' => 'Page headings', 'label' => 'Enquiry sidebar heading', 'type' => 'text', 'default' => 'Good projects begin with the right questions.'],

        // Service process and product guidance
        'process_1_title' => ['group' => 'Process & guidance', 'label' => 'Process step 1 title', 'type' => 'text', 'default' => 'Consult & survey'],
        'process_1_body' => ['group' => 'Process & guidance', 'label' => 'Process step 1 text', 'type' => 'textarea', 'default' => 'Understand use, measurements and project constraints.'],
        'process_2_title' => ['group' => 'Process & guidance', 'label' => 'Process step 2 title', 'type' => 'text', 'default' => 'Specify & quote'],
        'process_2_body' => ['group' => 'Process & guidance', 'label' => 'Process step 2 text', 'type' => 'textarea', 'default' => 'Align technical requirements, costs and approvals.'],
        'process_3_title' => ['group' => 'Process & guidance', 'label' => 'Process step 3 title', 'type' => 'text', 'default' => 'Procure & coordinate'],
        'process_3_body' => ['group' => 'Process & guidance', 'label' => 'Process step 3 text', 'type' => 'textarea', 'default' => 'Track agreed products and their delivery requirements.'],
        'process_4_title' => ['group' => 'Process & guidance', 'label' => 'Process step 4 title', 'type' => 'text', 'default' => 'Install & hand over'],
        'process_4_body' => ['group' => 'Process & guidance', 'label' => 'Process step 4 text', 'type' => 'textarea', 'default' => 'Coordinate site work, testing and acceptance.'],
        'process_5_title' => ['group' => 'Process & guidance', 'label' => 'Process step 5 title', 'type' => 'text', 'default' => 'Maintain & support'],
        'process_5_body' => ['group' => 'Process & guidance', 'label' => 'Process step 5 text', 'type' => 'textarea', 'default' => 'Keep service history and follow-up requirements connected.'],
        'service_banner_title' => ['group' => 'Process & guidance', 'label' => 'Service banner heading', 'type' => 'text', 'default' => 'Protect the performance of what has been installed.'],
        'service_banner_body' => ['group' => 'Process & guidance', 'label' => 'Service banner text', 'type' => 'textarea', 'default' => 'Tell us which site or system needs attention.'],
        'product_note_1_title' => ['group' => 'Process & guidance', 'label' => 'Product consideration 1', 'type' => 'text', 'default' => 'Opening dimensions'],
        'product_note_1_body' => ['group' => 'Process & guidance', 'label' => 'Product consideration 1 text', 'type' => 'textarea', 'default' => 'Clear width, height, available headroom and structural constraints.'],
        'product_note_2_title' => ['group' => 'Process & guidance', 'label' => 'Product consideration 2', 'type' => 'text', 'default' => 'Operational context'],
        'product_note_2_body' => ['group' => 'Process & guidance', 'label' => 'Product consideration 2 text', 'type' => 'textarea', 'default' => 'Frequency of use, environmental exposure and traffic requirements.'],
        'product_note_3_title' => ['group' => 'Process & guidance', 'label' => 'Product consideration 3', 'type' => 'text', 'default' => 'Control & safety'],
        'product_note_3_body' => ['group' => 'Process & guidance', 'label' => 'Product consideration 3 text', 'type' => 'textarea', 'default' => 'Automation needs, access control, operation and site safety.'],

        // Footer
        'footer_eyebrow' => ['group' => 'Footer & CTA', 'label' => 'Footer eyebrow', 'type' => 'text', 'default' => 'BUILD WHAT COMES NEXT'],
        'footer_title' => ['group' => 'Footer & CTA', 'label' => 'Footer heading', 'type' => 'textarea', 'default' => "Start with the right\nconversation."],
        'footer_support' => ['group' => 'Footer & CTA', 'label' => 'Footer supporting text', 'type' => 'textarea', 'default' => 'From early specification to the final handover, our team can help you identify the right system.'],
        'footer_cta' => ['group' => 'Footer & CTA', 'label' => 'Footer button label', 'type' => 'text', 'default' => 'Discuss a project'],
    ];

    if($vector){
        $fields['home_perspective_title']['default'] = "Systems first.\nDecisions second.";
        $fields['home_feature_title']['default'] = "Precision begins\nbefore installation.";
        $fields['systems_title']['default'] = "Specified for\nevery opening.";
        $fields['about_intro']['default'] = 'Industrial building systems, technical expertise and practical support from specification to service.';
    }
    return $fields;
}

function mp_building_defaults($themeKey = 'framework_atelier'){
    $out = [];
    foreach(mp_building_fields($themeKey) as $key => $field){
        $out[$key] = $field['default'];
    }
    return $out;
}

/**
 * Saved values over defaults. A saved value equal to the default is removed
 * on write, so this simply merges what is stored over the shipped copy.
 */
function mp_building_values($json, $themeKey = 'framework_atelier'){
    $all = json_decode((string)$json, true);
    $saved = is_array($all) && is_array($all[$themeKey] ?? null) ? $all[$themeKey] : [];
    $values = mp_building_defaults($themeKey);
    foreach(mp_building_fields($themeKey) as $key => $field){
        if(isset($saved[$key]) && is_string($saved[$key]) && trim($saved[$key]) !== ''){
            $values[$key] = $saved[$key];
        }
    }
    return $values;
}

/**
 * Resolve the public URL for one theme image, with a designed CSS fallback.
 * Images may be absolute URLs, upload paths or an empty string.
 */
function mp_building_image_url($value){
    $value = trim((string)$value);
    if($value === '') return '';
    if(preg_match('~^https?://~i', $value)) return $value;
    return base_url(ltrim($value, '/'));
}

/**
 * Render a heading where each line is escaped and the final line is wrapped
 * in <em>. The prototypes use that emphasis on every display heading.
 */
function mp_building_heading($text, $tag = 'h2', $class = ''){
    $lines = preg_split('/\r\n|\r|\n/', trim((string)$text));
    $lines = array_values(array_filter(array_map('trim', $lines), 'strlen'));
    if(!$lines) return '';
    $last = array_pop($lines);
    $html = '';
    foreach($lines as $line) $html .= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '<br>';
    $html .= '<em>' . htmlspecialchars($last, ENT_QUOTES, 'UTF-8') . '</em>';
    return '<' . $tag . ($class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '') . '>' . $html . '</' . $tag . '>';
}

/** First letter/word mark for the CSS-only image fallback. */
function mp_building_mark($brand){
    $brand = trim((string)$brand);
    if($brand === '') return 'M';
    return mb_strtoupper(mb_substr($brand, 0, 1));
}

/**
 * Text-safe version of the merchant's accent colour.
 *
 * Accent swatches are chosen for buttons and graphics, where a bright orange
 * works. The same colour at 11px on a pale panel can fall below WCAG 4.5:1, so
 * text accents use this darker derivation. The palette is returned unchanged
 * when it already passes; otherwise it is blended toward black in 5% steps.
 */
function mp_building_accent_text($hex){
    $hex = ltrim(trim((string)$hex), '#');
    if(strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    if(!preg_match('/^[0-9a-f]{6}$/i', $hex)) return '#111820';

    $rgb = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    $backgrounds = [[255, 255, 255], [245, 244, 240], [235, 235, 231], [239, 241, 243], [227, 232, 236], [223, 228, 232]];

    $luminance = function($channels){
        $values = [];
        foreach($channels as $value){
            $value /= 255;
            $values[] = $value <= 0.03928 ? $value / 12.92 : pow(($value + 0.055) / 1.055, 2.4);
        }
        return 0.2126 * $values[0] + 0.7152 * $values[1] + 0.0722 * $values[2];
    };
    $passes = function($channels) use ($backgrounds, $luminance){
        $foreground = $luminance($channels) + 0.05;
        foreach($backgrounds as $background){
            $ratio = ($foreground > $luminance($background) + 0.05)
                ? $foreground / ($luminance($background) + 0.05)
                : ($luminance($background) + 0.05) / $foreground;
            if($ratio < 4.5) return false;
        }
        return true;
    };

    if($passes($rgb)) return '#' . strtoupper($hex);
    for($percent = 5; $percent <= 80; $percent += 5){
        $shade = array_map(function($value) use ($percent){ return (int)round($value * (1 - $percent / 100)); }, $rgb);
        if($passes($shade)) return sprintf('#%02X%02X%02X', $shade[0], $shade[1], $shade[2]);
    }
    return '#111820';
}
