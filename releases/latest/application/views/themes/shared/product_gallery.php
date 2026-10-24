<?php
$galleryProduct = $product ?? null;
$galleryRows = $product_gallery_images ?? [];
$galleryPhotos = [];
if ($galleryProduct && !empty($galleryProduct->item_image) && is_file(FCPATH . ltrim($galleryProduct->item_image, '/\\'))) {
    $galleryPhotos[] = $galleryProduct->item_image;
}
foreach ($galleryRows as $galleryRow) {
    $photo = (string)($galleryRow->image_path ?? '');
    if ($photo !== '' && is_file(FCPATH . ltrim($photo, '/\\')) && !in_array($photo, $galleryPhotos, true)) $galleryPhotos[] = $photo;
}
if (count($galleryPhotos) > 1):
    $galleryId = 'mp-product-gallery-' . (int)$galleryProduct->id;
?>
<div id="<?= $galleryId; ?>" class="mp-product-gallery" data-primary="<?= htmlspecialchars(base_url($galleryPhotos[0]), ENT_QUOTES, 'UTF-8'); ?>" aria-label="Product photo gallery">
  <div class="mp-product-gallery__label">More product photos</div>
  <div class="mp-product-gallery__thumbs" role="list">
    <?php foreach ($galleryPhotos as $index => $photo): $url = base_url($photo); ?>
    <button type="button" class="mp-product-gallery__thumb<?= $index === 0 ? ' is-active' : ''; ?>" role="listitem" data-image="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" aria-label="View product photo <?= $index + 1; ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false'; ?>">
      <img src="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy">
    </button>
    <?php endforeach; ?>
  </div>
</div>
<style>
#<?= $galleryId; ?>{margin:14px 0 22px;font-family:inherit;color:inherit}
#<?= $galleryId; ?> .mp-product-gallery__label{font-size:13px;font-weight:600;margin:0 0 8px}
#<?= $galleryId; ?> .mp-product-gallery__thumbs{display:flex;gap:9px;overflow-x:auto;padding:2px 1px 7px;max-width:100%;}
#<?= $galleryId; ?> .mp-product-gallery__thumb{flex:0 0 68px;width:68px;height:68px;padding:3px;border:1px solid rgba(100,116,139,.3);border-radius:8px;background:#fff;cursor:pointer;overflow:hidden}
#<?= $galleryId; ?> .mp-product-gallery__thumb.is-active{border:2px solid var(--mp-primary,#2563eb)}
#<?= $galleryId; ?> .mp-product-gallery__thumb img{display:block;width:100%;height:100%;object-fit:cover;border-radius:5px}
</style>
<script>
(function(){
  var root=document.getElementById(<?= json_encode($galleryId); ?>); if(!root)return;
  var buttons=[].slice.call(root.querySelectorAll('[data-image]'));
  function findMain(src){
    var images=[].slice.call(document.querySelectorAll('img'));
    var primary=root.getAttribute('data-primary');
    return images.find(function(img){return img.id==='detail-img' || img.currentSrc===src || img.src===src || img.currentSrc===primary || img.src===primary;})
      || images.find(function(img){return /product|detail|gallery/i.test((img.className||'')+' '+img.id);});
  }
  var mainImage=findMain(root.getAttribute('data-primary'));
  function select(button){
    var source=button.getAttribute('data-image'), main=mainImage; if(!main)return;
    main.src=source; main.removeAttribute('srcset');
    buttons.forEach(function(b){var active=b===button;b.classList.toggle('is-active',active);b.setAttribute('aria-pressed',active?'true':'false');});
  }
  buttons.forEach(function(button){button.addEventListener('click',function(){select(button);});});
  var primary=root.getAttribute('data-primary');
  if(mainImage){var host=mainImage.closest('figure')||mainImage.parentElement;if(host&&host.parentNode)host.parentNode.insertBefore(root,host.nextSibling);}
  document.addEventListener('sf:variant-picked',function(event){var image=event.detail&&event.detail.image;if(!image)return;var url=new URL(image,document.baseURI).href;if(mainImage){mainImage.src=url;mainImage.removeAttribute('srcset');}var match=buttons.find(function(b){return b.getAttribute('data-image')===url;});if(match)buttons.forEach(function(b){var active=b===match;b.classList.toggle('is-active',active);b.setAttribute('aria-pressed',active?'true':'false');});});
})();
</script>
<?php endif; ?>
