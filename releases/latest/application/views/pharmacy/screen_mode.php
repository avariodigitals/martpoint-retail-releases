<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
(function () {
  var renderedMobile = <?= !empty($pharmacy_mobile) ? 'true' : 'false'; ?>;
  var compact = window.matchMedia('(max-width: 768px), (max-width: 1024px) and (orientation: portrait)');
  function syncScreen() {
    if (compact.matches === renderedMobile) return;
    var url = new URL(window.location.href);
    url.searchParams.set('screen', compact.matches ? 'mobile' : 'desktop');
    window.location.replace(url.toString());
  }
  syncScreen();
  if (compact.addEventListener) compact.addEventListener('change', syncScreen);
  else compact.addListener(syncScreen);
})();
</script>
