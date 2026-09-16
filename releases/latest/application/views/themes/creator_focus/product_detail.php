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
@media(max-width:900px){.crf-detail{grid-template-columns:1fr;}.crf-detail h1{font-size:28px;}}
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

      <?php if($isPhysical): ?>
      <div class="crf-qty">
        <button onclick="adjustDetailQty(-1)">-</button>
        <input type="number" id="detail-qty" value="1" min="1" readonly>
        <button onclick="adjustDetailQty(1)">+</button>
      </div>
      <?php endif; ?>

      <button class="crf-detail-cta" onclick="addDetailToCart()"><?= $cta; ?></button>
      <?php if($pt === 'physical' && !empty($settings->whatsapp_number)): ?>
      <button class="crf-detail-cta secondary" onclick="sendDetailWhatsApp()">Order via WhatsApp</button>
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
    <div class="crf-grid" style="grid-template-columns:repeat(4,1fr);">
      <?php foreach(array_slice($related_products,0,4) as $p):
        $price = $p->effective_price ?? $p->sales_price; $old = $p->original_price ?? $p->sales_price; $disc = $old > $price;
        $rpt = $p->product_type ?? 'physical';
      ?>
      <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="crf-card">
        <div class="crf-card-img">
          <?php if($p->item_image && file_exists($p->item_image)): ?>
            <img src="<?= base_url($p->item_image); ?>" alt="" loading="lazy">
          <?php else: ?>
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:<?= $c['border'];?>;color:<?= $c['muted'];?>;font-size:60px;font-weight:800;"><?= htmlspecialchars(substr($p->item_name,0,1)); ?></div>
          <?php endif; ?>
        </div>
        <div class="crf-card-body">
          <div class="crf-card-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="crf-card-footer">
            <span class="crf-card-price"><?= sf_currency($price, $store_currency ?? null); ?></span>
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
  function adjustDetailQty(d){
    detailQty = Math.max(1, detailQty + d);
    const el = document.getElementById('detail-qty');
    if(el) el.value = detailQty;
  }
  function addDetailToCart(){
    addToCart(<?= $product->id; ?>, '<?= $pt; ?>', '<?= htmlspecialchars(addslashes($product->item_name)); ?>', <?= $product->effective_price; ?>, '<?= $product->item_image; ?>', detailQty, <?= (int)$product->stock; ?>);
  }
  function sendDetailWhatsApp(){
    let msg = 'Hello, I am interested in: <?= htmlspecialchars(addslashes($product->item_name)); ?> — <?= sf_currency($product->effective_price, $store_currency ?? null); ?>';
    const wnum = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(wnum) window.open('https://wa.me/' + wnum + '?text=' + encodeURIComponent(msg), '_blank');
  }
</script>

<?php include(APPPATH.'views/themes/creator_focus/_footer.php'); ?>
