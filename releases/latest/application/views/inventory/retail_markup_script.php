<?php $retail_markup_config = function_exists('mp_retail_markup_config')
    ? mp_retail_markup_config(isset($store_id) ? $store_id : get_current_store_id())
    : ['percent' => null]; ?>
<?php if ($retail_markup_config['percent'] !== null): ?>
<script>window.mpRetailMarkupConfig = <?= json_encode($retail_markup_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
<script src="<?= base_url('theme/js/retail_markup.js'); ?>?v=1"></script>
<?php endif; ?>
