<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Uploadable HTML theme package manager.
 *
 * Packages are data, never PHP views. They are extracted into MartPoint's
 * private storage and are rendered by Html_theme_renderer as escaped text.
 */
class Html_theme_manager {

    const SCHEMA_VERSION = 1;
    const MAX_ZIP_BYTES = 10485760;       // 10 MiB upload
    const MAX_EXTRACTED_BYTES = 31457280; // 30 MiB expanded package
    const MAX_FILES = 300;

    private $CI;

    public function __construct(){
        $this->CI =& get_instance();
    }

    public function validateZip($zipPath){
        $result = ['ok' => false, 'errors' => [], 'warnings' => [], 'manifest' => null, 'files' => [], 'bytes' => 0, 'root' => ''];
        if(!class_exists('ZipArchive')){
            $result['errors'][] = 'ZIP support is not enabled on this PHP installation.';
            return $result;
        }
        if(!is_file($zipPath) || filesize($zipPath) > self::MAX_ZIP_BYTES){
            $result['errors'][] = 'Theme ZIP must be a readable file no larger than 10 MiB.';
            return $result;
        }
        $zip = new ZipArchive();
        if($zip->open($zipPath) !== true){
            $result['errors'][] = 'The uploaded file is not a readable ZIP archive.';
            return $result;
        }
        $count = $zip->numFiles;
        if($count < 1 || $count > self::MAX_FILES){
            $result['errors'][] = 'Theme ZIP must contain between 1 and 300 files.';
        }
        $names = [];
        $total = 0;
        for($i = 0; $i < $count; $i++){
            $stat = $zip->statIndex($i);
            $name = str_replace('\\', '/', (string)($stat['name'] ?? ''));
            $trimmed = trim($name, '/');
            if($trimmed === '' || substr($name, -1) === '/') continue;
            if($name !== $trimmed || strpos($name, "\0") !== false || preg_match('~^(?:/|[A-Za-z]:/)|(?:^|/)\.\.(?:/|$)~', $name)){
                $result['errors'][] = 'Unsafe ZIP path: ' . $name;
                continue;
            }
            if(isset($names[$name])){
                $result['errors'][] = 'Duplicate ZIP path: ' . $name;
                continue;
            }
            $names[$name] = true;
            $bytes = (int)($stat['size'] ?? 0);
            $total += $bytes;
            if($total > self::MAX_EXTRACTED_BYTES){
                $result['errors'][] = 'Expanded theme size exceeds 30 MiB.';
                break;
            }
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowed = ['html','htm','css','js','json','png','jpg','jpeg','webp','gif','svg','ico','woff','woff2','ttf','otf','map'];
            if(!in_array($ext, $allowed, true)){
                $result['errors'][] = 'Unsupported theme file type: ' . $name;
            }
            if(preg_match('~(^|/)(?:\.htaccess|web\.config|composer\.(json|lock)|.*\.php[0-9]?)$~i', $name)){
                $result['errors'][] = 'Executable or server configuration files are not allowed: ' . $name;
            }
            $contents = $zip->getFromIndex($i);
            if($contents !== false && preg_match('~<\?(?:php|=)|\?>~i', $contents)){
                $result['errors'][] = 'PHP tags are not allowed in theme files: ' . $name;
            }
            $result['files'][] = $name;
        }
        $zip->close();
        $result['bytes'] = $total;
        if($result['errors']) return $result;

        $themeFile = null;
        foreach($result['files'] as $name){
            if($name === 'theme.json') $themeFile = $name;
        }
        if(!$themeFile){
            $candidates = array_values(array_filter($result['files'], function($name){ return preg_match('~^[^/]+/theme\.json$~i', $name); }));
            if(count($candidates) === 1){
                $themeFile = $candidates[0];
                $result['root'] = dirname($themeFile) === '.' ? '' : dirname($themeFile) . '/';
            }
        }
        if(!$themeFile){
            $result['errors'][] = 'Theme ZIP must contain theme.json at its root or inside one top-level folder.';
            return $result;
        }
        $zip = new ZipArchive();
        $zip->open($zipPath);
        $manifest = json_decode((string)$zip->getFromName($themeFile), true);
        $zip->close();
        if(!is_array($manifest)){
            $result['errors'][] = 'theme.json must contain valid JSON.';
            return $result;
        }
        $result['manifest'] = $manifest;
        $this->validateManifest($manifest, $result['files'], $result['root'], $result);
        if(!$result['errors']){
            $result['warnings'] = $this->capabilityWarnings($manifest, (int)$this->CI->session->userdata('store_id'));
            $result['ok'] = true;
        }
        return $result;
    }

    private function validateManifest(array $manifest, array $files, $root, array &$result){
        if((int)($manifest['schema_version'] ?? 0) !== self::SCHEMA_VERSION){
            $result['errors'][] = 'Unsupported theme schema. Expected schema_version 1.';
        }
        $key = (string)($manifest['theme_key'] ?? '');
        if(!preg_match('/^[a-z0-9][a-z0-9_-]{1,39}$/', $key)){
            $result['errors'][] = 'theme_key must be 2–40 lowercase letters, numbers, dashes or underscores.';
        }
        if(trim((string)($manifest['name'] ?? '')) === '') $result['errors'][] = 'theme.json needs a name.';
        if(!preg_match('/^[0-9]+\.[0-9]+(?:\.[0-9]+)?(?:[-+][A-Za-z0-9.-]+)?$/', (string)($manifest['version'] ?? ''))){
            $result['errors'][] = 'theme.json needs a semantic version such as 1.0.0.';
        }
        if(!is_array($manifest['compatibility'] ?? null)){
            $result['errors'][] = 'theme.json needs a compatibility object.';
        } else {
            $phpTarget = trim((string)($manifest['compatibility']['php'] ?? ''));
            if($phpTarget !== '' && version_compare(PHP_VERSION, $phpTarget, '<')) $result['errors'][] = 'This theme requires PHP ' . $phpTarget . ' or newer.';
            if(PHP_MAJOR_VERSION !== 7 || PHP_MINOR_VERSION !== 4) $result['errors'][] = 'MartPoint HTML theme management requires PHP 7.4.';
        }
        $industry = (string)($manifest['industry'] ?? 'general');
        if(!preg_match('/^[a-z0-9_-]{2,50}$/', $industry)) $result['errors'][] = 'industry must be a lowercase compatibility key.';
        $templates = is_array($manifest['templates'] ?? null) ? $manifest['templates'] : [];
        $required = ['header','footer','home','catalogue','product','cart','checkout'];
        foreach($required as $name){
            $relative = (string)($templates[$name] ?? '');
            if($relative === ''){
                $result['errors'][] = 'Missing required template binding: templates.' . $name;
                continue;
            }
            if(!$this->safeRelative($relative) || !in_array($root . $relative, $files, true)){
                $result['errors'][] = 'Template does not exist or is unsafe: ' . $relative;
            }
        }
        foreach(['css','js'] as $kind){
            foreach((array)($manifest['assets'][$kind] ?? []) as $asset){
                if(!is_string($asset) || !$this->safeRelative($asset) || !in_array($root . $asset, $files, true)){
                    $result['errors'][] = 'Asset does not exist or is unsafe: ' . (string)$asset;
                }
            }
        }
        if(isset($manifest['settings']) && !is_array($manifest['settings'])) $result['errors'][] = 'settings must be an object.';
        if(isset($manifest['capabilities']) && !is_array($manifest['capabilities'])) $result['errors'][] = 'capabilities must be an array.';
    }

    private function safeRelative($path){
        return is_string($path) && $path !== '' && strpos($path, "\0") === false
            && !preg_match('~^(?:/|[A-Za-z]:/)|(?:^|/)\.\.(?:/|$)~', str_replace('\\','/',$path));
    }

    public function capabilityWarnings(array $manifest, $storeId){
        $requested = array_values(array_unique(array_filter(array_map('strval', (array)($manifest['capabilities'] ?? [])))));
        $supported = ['catalogue','search','product_detail','cart','checkout','services','reviews','prescriptions','fulfilment_hubs','navigation','configurable_content'];
        $warnings = [];
        $profile = function_exists('mp_get_store_profile') ? mp_get_store_profile((int)$storeId) : [];
        $industry = strtolower((string)($profile['industry_type'] ?? ''));
        foreach($requested as $capability){
            $ok = in_array($capability, $supported, true);
            if($capability === 'prescriptions') $ok = in_array($industry, ['pharmacy','healthcare','medical_store','medical'], true) && $this->CI->db->field_exists('prescription_file', 'db_online_orders');
            if($capability === 'fulfilment_hubs') $ok = $this->CI->db->table_exists('db_warehouse') && $this->CI->db->field_exists('warehouse_id', 'db_online_orders');
            if($capability === 'services') $ok = !empty($profile['features']) ? in_array('services', (array)$profile['features'], true) || !empty($profile['allow_services']) : true;
            if(!$ok) $warnings[] = $capability;
        }
        return $warnings;
    }

    public function install($zipPath, $storeId, $userId = null){
        $validation = $this->validateZip($zipPath);
        if(!$validation['ok']) throw new InvalidArgumentException(implode(' ', $validation['errors']));
        if(!$this->CI->db->table_exists('db_storefront_uploaded_themes')) throw new RuntimeException('Run migration 4.0.9.179 before uploading HTML themes.');
        $manifest = $validation['manifest'];
        $tmp = sys_get_temp_dir() . '/mp-html-theme-' . bin2hex(random_bytes(8));
        $storageRelative = 'themes/' . (int)$storeId . '/' . $manifest['theme_key'] . '-' . preg_replace('/[^0-9A-Za-z._-]/', '-', (string)$manifest['version']) . '-' . bin2hex(random_bytes(4));
        try{
            if(!mkdir($tmp, 0700, true) && !is_dir($tmp)) throw new RuntimeException('Could not create theme staging directory.');
            $zip = new ZipArchive();
            if($zip->open($zipPath) !== true || !$zip->extractTo($tmp)){ throw new RuntimeException('Could not extract the theme ZIP.'); }
            $zip->close();
            $root = $validation['root'];
            $source = rtrim($tmp . '/' . $root, '/');
            $target = mp_private_storage_path($storageRelative, false);
            if(!$target || !mkdir($target, 0700, true)) throw new RuntimeException('Could not create private theme storage.');
            $this->copyTree($source, $target);

            $manifestJson = json_encode($manifest, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            $rowKey = 'upload_' . (int)$storeId . '_' . substr(hash('sha256', $manifest['theme_key'] . '|' . $manifest['version'] . '|' . microtime(true)), 0, 12);
            $theme = [
                'theme_key' => $rowKey, 'theme_source' => 'html_zip', 'owner_store_id' => (int)$storeId,
                'theme_name' => substr((string)$manifest['name'], 0, 100), 'theme_version' => (string)$manifest['version'],
                'industry' => (string)($manifest['industry'] ?? 'general'), 'description' => substr((string)($manifest['description'] ?? ''), 0, 1000),
                'default_primary_color' => (string)($manifest['design']['primary_color'] ?? '#173B5E'),
                'default_secondary_color' => (string)($manifest['design']['secondary_color'] ?? '#B85C38'),
                'default_font_family' => (string)($manifest['design']['font_family'] ?? 'Inter'),
                'manifest_json' => $manifestJson, 'storage_path' => $storageRelative, 'status' => 1, 'sort_order' => 9000,
            ];
            $this->CI->db->insert('db_storefront_themes', $theme);
            $themeId = (int)$this->CI->db->insert_id();
            if(!$themeId) throw new RuntimeException('Could not register the uploaded theme.');
            $this->CI->db->insert('db_storefront_uploaded_themes', [
                'theme_id' => $themeId, 'store_id' => (int)$storeId, 'theme_key' => $rowKey, 'theme_name' => substr((string)$manifest['name'], 0, 120),
                'theme_version' => (string)$manifest['version'], 'industry' => (string)($manifest['industry'] ?? 'general'),
                'manifest_json' => $manifestJson, 'storage_path' => $storageRelative, 'extracted_bytes' => (int)$validation['bytes'],
                'unsupported_capabilities_json' => json_encode($validation['warnings']), 'status' => 1, 'created_by' => $userId ? (int)$userId : null,
            ]);
            if($this->CI->db->table_exists('db_storefront_uploaded_theme_settings')){
                $this->CI->db->insert('db_storefront_uploaded_theme_settings', [
                    'store_id' => (int)$storeId, 'theme_id' => $themeId, 'settings_json' => json_encode((array)($manifest['settings'] ?? [])),
                ]);
            }
            return ['theme_id' => $themeId, 'theme' => (object)$theme, 'manifest' => $manifest, 'warnings' => $validation['warnings']];
        } finally {
            $this->removeTree($tmp);
        }
    }

    public function listForStore($storeId){
        if(!$this->CI->db->table_exists('db_storefront_uploaded_themes')) return [];
        return $this->CI->db->where('store_id', (int)$storeId)->where('status', 1)->order_by('created_at', 'desc')->get('db_storefront_uploaded_themes')->result();
    }

    public function getTheme($themeId, $storeId = null){
        if(!$this->CI->db->table_exists('db_storefront_themes')) return null;
        $this->CI->db->where('id', (int)$themeId)->where('status', 1)->where('theme_source', 'html_zip');
        if($storeId !== null) $this->CI->db->where('owner_store_id', (int)$storeId);
        return $this->CI->db->get('db_storefront_themes')->row();
    }

    public function assetPath($theme, $relative){
        $relative = ltrim(str_replace('\\','/', (string)$relative), '/');
        if(!$theme || !$this->safeRelative($relative) || preg_match('~\.(?:php[0-9]?|phtml|phar)$~i', $relative)) return null;
        $root = realpath(mp_private_storage_path((string)$theme->storage_path, true));
        $path = $root ? realpath($root . '/' . $relative) : false;
        if(!$root || !$path || strpos($path, $root . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) return null;
        return $path;
    }

    private function copyTree($source, $target){
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach($it as $item){
            $relative = str_replace('\\','/', substr($item->getPathname(), strlen(rtrim($source, DIRECTORY_SEPARATOR)) + 1));
            $dest = rtrim($target, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relative;
            if($item->isDir()) @mkdir($dest, 0700, true); else { @mkdir(dirname($dest), 0700, true); if(!copy($item->getPathname(), $dest)) throw new RuntimeException('Could not store theme file.'); }
        }
    }

    private function removeTree($path){
        if(!$path || !is_dir($path)) return;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach($it as $item){ if($item->isDir()) @rmdir($item->getPathname()); else @unlink($item->getPathname()); }
        @rmdir($path);
    }
}
