<?php include(APPPATH.'views/themes/creator_focus/_nav.php'); ?>
<?php
$pt = $product->product_type ?? 'physical';
$isPhysical = $pt === 'physical';
$cta = ['digital'=>'Get Instant Access','course'=>'Enroll Now','membership'=>'Join Now','service'=>'Book Service','physical'=>'Add to Cart'][$pt] ?? 'Buy Now';
$soldLabel = $pt === 'course' ? 'student' : ($pt === 'membership' ? 'member' : 'sold');
$benefits = ['digital' => ['Instant download after payment','Keep forever, use anywhere','No shipping, no waiting'],
            'course' => ['Self-paced video lessons','Lifetime access','Watch on any device'],
            'membership' => ['Recurring access while active','New content every cycle','Cancel anytime'],
            'service' => ['Book a convenient time','Direct communication','Personalized service'],
            'physical' => ['Real product, real delivery','Pay on delivery available','Ships to your address']][$pt];
$billing = $pt === 'membership' ? ($product->membership_billing ?? 'monthly') : '';
?>
<style>
.crf-detail-page{background:<?= $c['surface'];?>;min-height:80vh;padding-bottom:40px;}
.crf-breadcrumb{max-width:1200px;margin:0 auto;padding:24px 24px 0;font-size:13px;color:<?= $c['muted'];?>;}
.crf-breadcrumb a{color:<?= $c['primary'];?>;text-decoration:none;}
.crf-detail{max-width:1200px;margin:0 auto;padding:40px 24px;display:grid;grid-template-columns:1fr 1.1fr;gap:48px;align-items:start;}
.crf-detail-img{aspect-ratio:4/3;border-radius:20px;overflow:hidden;background:<?= $c['border'];?>;}
.crf-detail-img img{width:100%;height:100%;object-fit:cover;}
.crf-detail h1{font-size:36px;font-weight:900;line-height:1.1;margin-bottom:14px;color:<?= $text;?>;}
.crf-detail-price{font-size:32px;font-weight:900;color:<?= $c['primary'];?>;margin-bottom:6px;}
.crf-detail-old{font-size:18px;color:<?= $c['muted'];?>;text-decoration:line-through;margin-left:8px;}
.crf-detail-meta{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:20px 0;font-size:14px;color:<?= $c['muted'];?>;}
.crf-tag{padding:4px 12px;border-radius:999px;background:<?= $c['primary'];?>;color:#fff;font-size:12px;font-weight:800;text-transform:uppercase;}
.crf-tag-green{padding:4px 12px;border-radius:999px;background:rgba(5,150,105,.1);color:#047857;font-weight:800;}
.crf-detail-desc{font-size:15px;line-height:1.8;color:<?= $c['muted'];?>;margin:28px 0;}
.crf-detail-cta{display:block;width:100%;padding:18px;border-radius:12px;background:<?= $c['primary'];?>;color:#fff;font-size:18px;font-weight:800;border:none;cursor:pointer;text-align:center;transition:background .15s;}
.crf-detail-cta:hover{background:<?= $c['primary-dark'];?>;}
.crf-detail-cta.secondary{background:<?= $c['border'];?>;color:<?= $text;?>;margin-top:12px;}
.crf-benefits{display:flex;flex-direction:column;gap:12px;margin-top:28px;}
.crf-benefit{display:flex;align-items:center;gap:12px;font-size:14px;color:<?= $c['muted'];?>;}
.crf-benefit span{width:24px;height:24px;border-radius:50%;background:<?= $c['primary'];?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;}
.crf-qty{display:flex;align-items:center;gap:14px;margin:20px 0;}
.crf-qty button{width:40px;height:40px;border-radius:50%;border:1px solid <?= $c['border'];?>;background:#fff;font-size:18px;cursor:pointer;}
.crf-qty input{width:50px;height:40px;text-align:center;border:1px solid <?= $c['border'];?>;border-radius:8px;font-weight:800;}
.crf-related{max-width:1200px;margin:0 auto;padding:0 24px 64px;}
.crf-related h2{font-size:24px;font-weight:800;margin-bottom:24px;color:<?= $text;?>;}
.crf-rel-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;}
.crf-rel-card{background:<?= $c['surface'];?>;border:1px solid <?= $c['border'];?>;border-radius:14px;overflow:hidden;display:flex;flex-direction:column;text-decoration:none;transition:transform .2s,box-shadow .2s;}
.crf-rel-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,.1);}
.crf-rel-img{aspect-ratio:4/3;overflow:hidden;background:<?= $c['border'];?>;position:relative;}
.crf-rel-img img{width:100%;height:100%;object-fit:cover;transition:transform .4s;display:block;}
.crf-rel-card:hover .crf-rel-img img{transform:scale(1.05);}
.crf-rel-badge{position:absolute;top:10px;left:10px;padding:4px 10px;border-radius:999px;background:<?= $c['primary'];?>;color:#fff;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;}
.crf-rel-body{padding:14px 16px 16px;display:flex;flex-direction:column;gap:8px;flex:1;}
.crf-rel-name{font-size:14.5px;font-weight:700;color:<?= $text;?>;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.crf-rel-foot{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:auto;padding-top:6px;}
.crf-rel-price{font-size:16px;font-weight:800;color:<?= $c['primary'];?>;}
.crf-rel-old{font-size:12px;color:<?= $c['muted'];?>;text-decoration:line-through;margin-left:4px;font-weight:500;}
.crf-rel-share{width:36px;height:36px;border-radius:50%;border:1px solid <?= $c['border'];?>;background:transparent;color:<?= $c['muted'];?>;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;transition:all .15s;}
.crf-rel-share:hover{border-color:<?= $c['primary'];?>;color:<?= $c['primary'];?>;}
.crf-rel-share svg{width:16px;height:16px;}
@media(max-width:900px){.crf-detail{grid-template-columns:1fr;}.crf-detail h1{font-size:28px;}.crf-rel-grid{grid-template-columns:repeat(2,1fr);gap:12px;}}
@media(max-width:420px){.crf-rel-body{padding:12px;}.crf-rel-name{font-size:13.5px;}}
</style>

<div class="crf-detail-page">
  <div class="crf-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a> / <a href="<?= base_url('store/' . $slug . '/products'); ?>">Library</a> / <?= htmlspecialchars($product->item_name); ?>
  </div>

  <div class="crf-detail">
    <div class="crf-detail-img">
      <?php if($product->item_image && file_exists($product->item_image)): ?>
        <img src="<?= base_url($product->item_image); ?>" alt="<?= htmlspecialchars($product->item_name); ?>">
      <?php else: ?>
        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:<?= $c['border'];?>;color:<?= $c['muted'];?>;font-size:80px;font-weight:800;"><?= htmlspecialchars(substr($product->item_name,0,1)); ?></div>
      <?php endif; ?>
    </div>
    <div>
      <div style="display:flex;gap:8px;align-items:center;margin-bottom:10px;flex-wrap:wrap;">
        <span class="crf-tag"><?= ['digital'=>'Download','course'=>'Course','membership'=>'Membership','service'=>'Service','physical'=>'Product'][$pt] ?? 'Product'; ?></span>
        <span class="crf-tag-green">Available</span>
      </div>
      <h1><?= htmlspecialchars($product->item_name); ?></h1>
      <div class="crf-detail-price">
        <?= sf_currency($product->effective_price, $store_currency ?? null); ?>
        <?php if($product->original_price > $product->effective_price): ?><span class="crf-detail-old"><?= sf_currency($product->original_price, $store_currency ?? null); ?></span><?php endif; ?>
      </div>
      <div class="crf-detail-meta">
        <?php if($product->sold_count > 0): ?><span><?= number_format($product->sold_count); ?> <?= $soldLabel . ($product->sold_count != 1 ? 's' : ''); ?></span><?php endif; ?>
        <?php if($product->category_name): ?><span><?= htmlspecialchars($product->category_name); ?></span><?php endif; ?>
        <?php if($pt === 'membership'): ?><span>Billed <?= htmlspecialchars($billing); ?></span><?php endif; ?>
      </div>

      <?php $this->load->view('themes/shared/variant_picker', ['sf_picker_mode' => 'select']); ?>

      <?php if($isPhysical): ?>
      <div class="crf-qty">
        <button onclick="adjustDetailQty(-1)">-</button>
        <input type="number" id="detail-qty" value="1" min="1" readonly>
        <button onclick="adjustDetailQty(1)">+</button>
      </div>
      <?php endif; ?>

      <?php $crfOos = $isPhysical && (int)$product->stock <= 0 && empty($settings->allow_backorder); ?>
      <button class="crf-detail-cta" <?= $crfOos ? 'disabled style="opacity:.5;cursor:not-allowed;"' : ''; ?> onclick="addDetailToCart()"><?= $crfOos ? 'Out of Stock' : $cta; ?></button>
      <?php if($crfOos) $this->load->view('themes/shared/restock_subscribe', ['sf_item_id' => $product->id]); ?>
      <?php if($pt === 'physical' && !empty($settings->whatsapp_number)): ?>
      <button class="crf-detail-cta secondary" onclick="sendDetailWhatsApp()"><?= $crfOos ? 'Ask about availability' : 'Order via WhatsApp'; ?></button>
      <?php endif; ?>

      <div class="crf-benefits">
        <?php foreach($benefits as $b): ?>
        <div class="crf-benefit"><span>✓</span><?= $b; ?></div>
        <?php endforeach; ?>
      </div>

      <div class="crf-detail-desc">
        <?= nl2br(htmlspecialchars($product->description ?? '')); ?>
      </div>
    </div>
  </div>

  <?php if(!empty($related_products)): ?>
  <div class="crf-related">
    <h2>You may also like</h2>
    <div class="crf-rel-grid">
      <?php foreach(array_slice($related_products,0,4) as $p):
        $price = $p->effective_price ?? $p->sales_price; $old = $p->original_price ?? $p->sales_price; $disc = $old > $price;
        $rpt = $p->product_type ?? 'physical';
        $relUrl = base_url('store/' . $slug . '/product/' . $p->id);
        $relBadge = ['digital'=>'Download','course'=>'Course','membership'=>'Membership','service'=>'Service','physical'=>'Product'][$rpt] ?? 'Product';
      ?>
      <a href="<?= $relUrl; ?>" class="crf-rel-card">
        <div class="crf-rel-img">
          <?php if($p->item_image && file_exists($p->item_image)): ?>
            <img src="<?= base_url($p->item_image); ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:<?= $c['border'];?>;color:<?= $c['muted'];?>;font-size:60px;font-weight:800;"><?= htmlspecialchars(substr($p->item_name,0,1)); ?></div>
          <?php endif; ?>
          <span class="crf-rel-badge"><?= $relBadge; ?></span>
        </div>
        <div class="crf-rel-body">
          <div class="crf-rel-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="crf-rel-foot">
            <span class="crf-rel-price"><?= sf_currency($price, $store_currency ?? null); ?><?php if($disc): ?><span class="crf-rel-old"><?= sf_currency($old, $store_currency ?? null); ?></span><?php endif; ?></span>
            <button type="button" class="crf-rel-share" aria-label="Share <?= htmlspecialchars($p->item_name); ?>" onclick="shareRelated(event, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', '<?= $relUrl; ?>')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            </button>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
  let detailQty = 1;
  const detailProduct = {
    id: <?= $product->id; ?>,
    name: '<?= htmlspecialchars(addslashes($product->item_name)); ?>',
    price: <?= $product->effective_price; ?>,
    image: '<?= $product->item_image; ?>',
    stock: <?= (int)$product->stock; ?>
  };
  const needsVariant = <?= (($product->item_group ?? '') === 'Variants') ? 'true' : 'false'; ?>;
  document.addEventListener('sf:variant-picked', function(e){
    const v = e.detail; if(!v) return;
    detailProduct.id = v.id; detailProduct.name = v.name;
    detailProduct.price = v.price; detailProduct.image = v.image; detailProduct.stock = v.stock;
    const pe = document.querySelector('.crf-detail-price');
    if(pe) pe.childNodes[0].textContent = formatMoney(v.price);
    const vOos = <?= $isPhysical ? 'true' : 'false'; ?> && v.stock <= 0 && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?>;
    const vCta = document.querySelector('.crf-detail-cta:not(.secondary)');
    if(vCta){ vCta.disabled = vOos; vCta.textContent = vOos ? 'Out of Stock' : '<?= $cta; ?>'; vCta.style.opacity = vOos ? '.5' : ''; vCta.style.cursor = vOos ? 'not-allowed' : ''; }
    const vWa = document.querySelector('.crf-detail-cta.secondary'); if(vWa) vWa.textContent = vOos ? 'Ask about availability' : 'Order via WhatsApp';
  });
  function adjustDetailQty(d){
    detailQty = Math.max(1, detailQty + d);
    const el = document.getElementById('detail-qty');
    if(el) el.value = detailQty;
  }
  function addDetailToCart(){
    if(needsVariant && !window.sfPickedVariant){ showToast('Please choose an option'); return; }
    addToCart(detailProduct.id, '<?= $pt; ?>', detailProduct.name, detailProduct.price, detailProduct.image, detailQty, detailProduct.stock);
  }
  function shareRelated(ev, name, url){
    ev.preventDefault(); ev.stopPropagation();
    const payload = {title: name, text: name + ' — ' + '<?= htmlspecialchars(addslashes($store->store_name ?? '')); ?>', url: url};
    if(navigator.share){
      navigator.share(payload).catch(()=>{});
    } else if(navigator.clipboard){
      navigator.clipboard.writeText(url).then(()=>{ if(typeof showToast==='function') showToast('Link copied'); });
    } else {
      window.prompt('Copy this link', url);
    }
  }
  function sendDetailWhatsApp(){
    if(typeof detailProduct !== 'undefined' && detailProduct.stock !== undefined && detailProduct.stock <= 0 && (!detailProduct.type || detailProduct.type === 'product' || detailProduct.type === 'physical') && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?>){ const wnum2 = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>'; if(wnum2) window.open('https://wa.me/' + wnum2 + '?text=' + encodeURIComponent('Hello, is ' + detailProduct.name + ' back in stock?'), '_blank'); return; }
    let msg = 'Hello, I am interested in: <?= htmlspecialchars(addslashes($product->item_name)); ?> — <?= sf_currency($product->effective_price, $store_currency ?? null); ?>';
    const wnum = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(wnum) window.open('https://wa.me/' + wnum + '?text=' + encodeURIComponent(msg), '_blank');
  }
</script>

<?php include(APPPATH.'views/themes/creator_focus/_footer.php'); ?>
