<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** The editable portion of an HTML package. Commerce data never lives here. */
class Html_theme_content {
    private $CI;
    public function __construct(){ $this->CI =& get_instance(); }

    public function fields(array $manifest){
        $defaults = $manifest['settings'] ?? [];
        $schema = $manifest['settings_schema'] ?? [];
        if(!is_array($defaults) || !is_array($schema) || count($defaults) > 100 || count($schema) > 100){
            throw new InvalidArgumentException('Theme settings must contain at most 100 fields.');
        }
        $fields = [];
        $keys = array_unique(array_merge(array_keys($defaults), array_keys($schema)));
        if(count($keys) > 100) throw new InvalidArgumentException('Theme settings must contain at most 100 fields.');
        foreach($keys as $key){
            if(!is_string($key) || !preg_match('/^[a-z][a-z0-9_]{0,59}$/D', $key)) throw new InvalidArgumentException('Use lowercase setting keys.');
            // Older schema-1 packages could bind nested defaults. Keep those
            // read-only until the author explicitly defines supported fields.
            if(!array_key_exists($key,$schema) && (is_array($defaults[$key] ?? null) || (array_key_exists($key,$defaults) && $defaults[$key] === null))) continue;
            $field = $schema[$key] ?? [];
            if(!is_array($field)) throw new InvalidArgumentException('Invalid setting definition: ' . $key);
            $type = $field['type'] ?? (is_bool($defaults[$key] ?? null) ? 'boolean' : 'text');
            if(!in_array($type, ['text','textarea','url','image','boolean'], true)) throw new InvalidArgumentException('Unsupported setting type: ' . $key);
            $limit = $field['max_length'] ?? ($type === 'textarea' ? 3000 : 1500);
            if(!is_int($limit) || $limit < 1 || $limit > 10000) throw new InvalidArgumentException('Invalid setting length: ' . $key);
            foreach(['label','group'] as $label){
                if(isset($field[$label]) && (!is_string($field[$label]) || mb_strlen($field[$label]) > 150)) throw new InvalidArgumentException('Invalid setting label: ' . $key);
            }
            $fields[$key] = ['type'=>$type, 'label'=>$field['label'] ?? ucwords(str_replace('_',' ',$key)),
                'group'=>$field['group'] ?? 'Website content', 'max_length'=>$limit];
            $fields[$key]['default'] = $this->value($defaults[$key] ?? ($type === 'boolean' ? false : ''), $fields[$key]);
        }
        return $fields;
    }

    private function value($value, array $field){
        if($field['type'] === 'boolean'){
            if(!in_array($value, [true,false,0,1,'0','1'], true)) throw new InvalidArgumentException('Choose yes or no for ' . $field['label'] . '.');
            return (bool)$value;
        }
        if(!is_string($value) && !is_numeric($value)) throw new InvalidArgumentException('Enter text for ' . $field['label'] . '.');
        $value = trim((string)$value);
        if(mb_strlen($value) > $field['max_length']) throw new InvalidArgumentException('Please shorten ' . $field['label'] . '.');
        if(in_array($field['type'], ['url','image'], true) && $value !== ''){
            $absolute = filter_var($value, FILTER_VALIDATE_URL) && in_array(strtolower((string)parse_url($value, PHP_URL_SCHEME)), ['http','https'], true);
            $relative = preg_match('~^(?:/)?(?:uploads|theme)/[a-zA-Z0-9_./% -]+$~D', $value) && strpos($value, '..') === false;
            if($field['type'] === 'image' && preg_match('~^assets/[a-zA-Z0-9_./% -]+$~D', $value) && strpos($value,'..') === false) $relative = true;
            if(!$absolute && !$relative) throw new InvalidArgumentException('Enter an http/https URL or a store image path for ' . $field['label'] . '.');
        }
        return $value;
    }

    public function values($theme, array $manifest){
        $fields = $this->fields($manifest);
        $values = array_diff_key((array)($manifest['settings'] ?? []), $fields);
        foreach($fields as $key=>$field) $values[$key] = $field['default'];
        if(!$this->CI->db->table_exists('db_storefront_uploaded_theme_settings')) return $values;
        $row = $this->CI->db->where('store_id', (int)$theme->owner_store_id)->where('theme_id', (int)$theme->id)
            ->get('db_storefront_uploaded_theme_settings')->row();
        $saved = $row ? json_decode((string)$row->settings_json, true) : [];
        if(!is_array($saved)) return $values;
        foreach($fields as $key=>$field){
            if(!array_key_exists($key, $saved)) continue;
            try { $values[$key] = $this->value($saved[$key], $field); }
            catch(InvalidArgumentException $e){ /* An incompatible old value uses its package default. */ }
        }
        return $values;
    }

    public function save($storeId, $themeId, array $input){
        $this->CI->load->library('html_theme_manager');
        $theme = $this->CI->html_theme_manager->getTheme((int)$themeId, (int)$storeId);
        if(!$theme) throw new InvalidArgumentException('Theme not found for this store.');
        if(!$this->CI->db->table_exists('db_storefront_uploaded_theme_settings')) throw new RuntimeException('Run migration 4.0.9.179 before editing HTML theme content.');
        $manifest = json_decode((string)$theme->manifest_json, true);
        if(!is_array($manifest)) throw new InvalidArgumentException('Invalid theme manifest.');
        $fields = $this->fields($manifest);
        if(array_diff_key($input, $fields)) throw new InvalidArgumentException('This theme does not define one of the submitted fields.');
        $values = $this->values($theme, $manifest);
        foreach($input as $key=>$value) $values[$key] = $this->value($value, $fields[$key]);
        $encoded = json_encode($values, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        if($encoded === false) throw new InvalidArgumentException('Content could not be encoded.');
        // The unique (store_id,theme_id) key also makes concurrent first saves safe.
        $ok = $this->CI->db->query('INSERT INTO db_storefront_uploaded_theme_settings (store_id,theme_id,settings_json) VALUES (?,?,?) ON DUPLICATE KEY UPDATE settings_json=VALUES(settings_json)', [(int)$storeId,(int)$themeId,$encoded]);
        if(!$ok) throw new RuntimeException('Could not save website content.');
        return $values;
    }
}
