<?php
/**
 * MartPoint — PRINT storefront closing scripts.
 *
 * Deliberately tiny. The printing designs in `themes/printing/` own their own
 * interactivity: the in-app select component (`enhanceSelects`), the
 * specification fields (`specs`), the category filters, and the quote-request
 * POST to `store/<slug>/lead`. None of that belongs here.
 *
 * `themes/shared/scripts.php` is NOT loaded on a print page. It is a 357-line
 * cart / variant / pixel-tracking bundle whose every entry point assumes retail
 * cart markup (`STORE_ID`, the cart drawer, variant picker, sticky bar) that a
 * quote-first print page does not have — loading it would throw on first call.
 *
 * What remains is the one piece of genuinely shared infrastructure a print page
 * still wants: telling the store's analytics that the page was viewed, which is
 * what the merchant's storefront report counts.
 */
?>
<script>
  window.MP_STORE_ID = <?= (int) ($store->id ?? 0); ?>;
  window.MP_THEME_KEY = <?= json_encode($theme_key ?? '', JSON_UNESCAPED_SLASHES); ?>;
</script>
<?php if (!empty($mp_track_view)): ?>
<script>
(function () {
  // Record each actual page load, consistent with the retail storefront.
  try {
    if (!window.MP_STORE_ID) { return; }
    if (window.MP_NEED_CONSENT && !(window.mpTrackConsent && window.mpTrackConsent.granted())) { return; }
    var fd = new FormData();
    fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
    fd.append('store_id', window.MP_STORE_ID);
    fd.append('page_url', window.location.href);
    fd.append('referrer', document.referrer);
    var st = new URLSearchParams(window.location.search).get('search') || '';
    if (st) { fd.append('search_term', st); }
    fetch('<?= base_url('online_store/track_visit'); ?>', { method: 'POST', body: fd, keepalive: true }).catch(function () {});
  } catch (e) {}
})();
</script>
<?php endif; ?>
