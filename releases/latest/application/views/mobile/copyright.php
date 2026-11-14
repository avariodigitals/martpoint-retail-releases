<?php
$mp_copyright_store = trim((string)($store_name ?? $SITE_TITLE ?? 'MartPoint'));
if (preg_match('/^saas(?:\s+admin)?$/i', $mp_copyright_store)) {
    $mp_copyright_ci =& get_instance();
    try {
        if ($mp_copyright_ci->db->table_exists('db_store')) {
            $mp_copyright_row = $mp_copyright_ci->db->select('store_name')->where('id >', 1)
                ->where("UPPER(TRIM(store_name)) NOT IN ('SAAS ADMIN','MY STORE')", null, false)
                ->where('store_name !=', '')->order_by('id', 'asc')->limit(1)->get('db_store')->row();
            if ($mp_copyright_row && trim((string)$mp_copyright_row->store_name) !== '') {
                $mp_copyright_store = trim((string)$mp_copyright_row->store_name);
            }
        }
    } catch (Throwable $e) {
        // Keep the footer available even on an older install without db_store.
    }
}
if ($mp_copyright_store === '' || preg_match('/^saas(?:\s+admin)?$/i', $mp_copyright_store)) $mp_copyright_store = 'MartPoint';
?>
<span class="mp-mobile-copyright-text">&copy; <?= date('Y'); ?> <?= htmlspecialchars($mp_copyright_store, ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.<br><span class="mp-mobile-powered">Business operations powered by MartPoint.</span></span>
