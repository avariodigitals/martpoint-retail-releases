<?php if (!empty($product_image_fallbacks)): ?>
<script>
(function(){
  'use strict';
  var images = <?= json_encode($product_image_fallbacks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  var entries = Object.keys(images).map(function(path){
    try {
      return {url: new URL(path, <?= json_encode(base_url(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>).href, path:path.replace(/^\/+/, ''), fallback:images[path]};
    } catch(e) { return null; }
  }).filter(Boolean);
  function replace(img){
    if (!img || img.tagName !== 'IMG') return;
    var src = img.currentSrc || img.src;
    for (var i=0; i<entries.length; i++) {
      var item = entries[i];
      var path;
      try { path = decodeURIComponent(new URL(src).pathname); } catch(e) { continue; }
      if (src !== item.fallback && (src === item.url || path.endsWith('/'+item.path))) {
        // Remove responsive sources so the browser cannot keep choosing the failed image.
        img.removeAttribute('srcset');
        if (img.parentElement && img.parentElement.tagName === 'PICTURE') {
          img.parentElement.querySelectorAll('source').forEach(function(source){ source.removeAttribute('srcset'); });
        }
        img.src = item.fallback;
        return;
      }
    }
  }
  document.addEventListener('error', function(event){replace(event.target);}, true);
  document.querySelectorAll('img').forEach(function(img){if(img.complete && img.naturalWidth===0) replace(img);});
})();
</script>
<?php endif; ?>
