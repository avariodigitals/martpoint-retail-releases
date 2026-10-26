<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$mpShareName = trim((string)($product->item_name ?? 'Product'));
$mpShareIsFood = !empty($restaurant_share);
$mpShareStore = trim((string)($store->store_name ?? $settings->store_name ?? 'MartPoint Store'));
$mpShareUrl = trim((string)($seo_canonical ?? ''));
if ($mpShareUrl === '' && !empty($settings->store_slug) && !empty($product->id)) {
    $mpShareUrl = base_url('store/' . rawurlencode((string)$settings->store_slug) . '/product/' . (int)$product->id);
}
if ($mpShareUrl === '') return;
$mpShareTitle = $mpShareName . ' | ' . $mpShareStore;
$mpShareText = 'Check out ' . $mpShareName . ' at ' . $mpShareStore;
$mpShareEncoded = rawurlencode($mpShareText . ' ' . $mpShareUrl);
?>
<section class="mp-product-share" aria-label="Share this product" data-mp-product-share
  data-share-title="<?= htmlspecialchars($mpShareTitle, ENT_QUOTES, 'UTF-8'); ?>"
  data-share-text="<?= htmlspecialchars($mpShareText, ENT_QUOTES, 'UTF-8'); ?>"
  data-share-url="<?= htmlspecialchars($mpShareUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <span class="mp-product-share__label"><?= $mpShareIsFood ? 'Share with a friend' : 'Share this product'; ?></span>
  <div class="mp-product-share__actions">
    <button type="button" class="mp-product-share__button mp-product-share__native" data-share-native aria-label="Share product">Share</button>
    <a class="mp-product-share__button" href="https://wa.me/?text=<?= $mpShareEncoded; ?>" target="_blank" rel="noopener noreferrer" aria-label="Share product on WhatsApp">WhatsApp</a>
    <a class="mp-product-share__button" href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($mpShareUrl); ?>" target="_blank" rel="noopener noreferrer" aria-label="Share product on Facebook">Facebook</a>
    <button type="button" class="mp-product-share__button" data-share-copy>Copy link</button>
  </div>
  <span class="mp-product-share__status" data-share-status role="status" aria-live="polite"></span>
</section>
<style>
  .mp-product-share{margin:18px 0 26px;padding:14px 0;border-top:1px solid var(--mp-border,#e5e7eb);border-bottom:1px solid var(--mp-border,#e5e7eb)}
  .mp-product-share__label{display:block;margin-bottom:9px;font-size:13px;font-weight:700;color:var(--mp-dark,#1f2937)}
  .mp-product-share__actions{display:flex;flex-wrap:wrap;gap:8px}
  .mp-product-share__button{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:8px 13px;border:1px solid var(--mp-border,#d1d5db);border-radius:8px;background:var(--mp-white,#fff);color:var(--mp-dark,#1f2937);font:inherit;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer}
  .mp-product-share__button:hover{border-color:var(--mp-primary,#2563eb);color:var(--mp-primary,#2563eb)}
  .mp-product-share__status{display:block;min-height:1em;margin-top:6px;font-size:12px;color:var(--mp-gray,#64748b)}
  .mp-product-share__native[hidden]{display:none!important}
  @media(prefers-reduced-motion:no-preference){.mp-product-share__button{transition:border-color .15s ease,color .15s ease}}
</style>
<script>
(function(){
  var box=document.currentScript&&document.currentScript.previousElementSibling;
  if(!box||!box.matches('[data-mp-product-share]'))box=document.querySelector('[data-mp-product-share]:not([data-share-ready])');
  if(!box||box.dataset.shareReady)return;
  box.dataset.shareReady='1';
  var status=box.querySelector('[data-share-status]'), shareButton=box.querySelector('[data-share-native]');
  if(!navigator.share&&shareButton)shareButton.hidden=true;
  shareButton&&shareButton.addEventListener('click',function(){
    if(!navigator.share)return;
    shareButton.disabled=true;if(status)status.textContent='Opening share options…';
    try{
      Promise.resolve(navigator.share({title:box.dataset.shareTitle,text:box.dataset.shareText,url:box.dataset.shareUrl}))
        .then(function(){if(status)status.textContent='Share completed.';})
        .catch(function(error){if(status)status.textContent=error&&error.name==='AbortError'?'':'Sharing was not completed. You can copy the link instead.';})
        .finally(function(){shareButton.disabled=false;});
    }catch(error){shareButton.disabled=false;if(status)status.textContent='Sharing is unavailable. You can copy the link instead.';}
  });
  var copy=box.querySelector('[data-share-copy]');
  copy&&copy.addEventListener('click',function(){
    var url=box.dataset.shareUrl;
    if(status)status.textContent='Copying link…';
    var done=function(){if(status)status.textContent='Link copied.';};
    if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(url).then(done).catch(function(){fallback(url,done);});}
    else fallback(url,done);
  });
  function fallback(value,done){
    var focused=document.activeElement;
    var field=document.createElement('textarea');field.value=value;field.setAttribute('readonly','');field.style.position='fixed';field.style.opacity='0';document.body.appendChild(field);field.select();
    try{if(document.execCommand('copy'))done();else if(status)status.textContent='Copy this product link: '+value;}catch(e){if(status)status.textContent='Copy this product link: '+value;}
    document.body.removeChild(field);
    if(focused&&typeof focused.focus==='function')focused.focus({preventScroll:true});
  }
})();
</script>
