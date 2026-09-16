<?php include(APPPATH.'views/themes/creator_focus/_nav.php'); ?>
<?php
$source = !empty($all_products) ? $all_products : array_merge($featured_products ?? [], $new_arrivals ?? [], $best_sellers ?? []);
$byType = ['course' => [], 'digital' => [], 'membership' => [], 'service' => [], 'physical' => []];
$seen = [];
$totalStudents = 0;
$totalSold = 0;
foreach($source as $p){
  $id = $p->id;
  if(isset($seen[$id])) continue;
  $seen[$id] = true;
  $t = $p->product_type ?? 'physical';
  $byType[$t][] = $p;
  $n = $p->sold_count ?? 0;
  if($t === 'course' || $t === 'membership') $totalStudents += $n;
  else $totalSold += $n;
}
$courses = array_slice($byType['course'], 0, 4);
$digitals = array_slice(array_merge($byType['digital'], $byType['physical'], $byType['service']), 0, 6);
$memberships = array_slice($byType['membership'], 0, 3);
$heroProduct = $courses[0] ?? $digitals[0] ?? ($all_products[0] ?? null);
$heroImage = $heroProduct->item_image ?? ($logo_url ?? base_url('uploads/site/icon.webp'));
$allCount = count($byType['course']) + count($byType['digital']);
$isHeroDark = in_array($THEME, ['focus','bold']);
$heroText = $isHeroDark ? '#fff' : $c['text'];
$heroMuted = $isHeroDark ? 'rgba(255,255,255,.9)' : $c['muted'];
$heroSubtle = $isHeroDark ? 'rgba(255,255,255,.85)' : $c['muted'];
?>
<style>
.crf-hero{background:<?= $c['hero-gradient'];?>;color:<?= $heroText;?>;padding:72px 24px;position:relative;overflow:hidden;}
.crf-hero-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1.2fr 1fr;gap:48px;align-items:center;}
.crf-hero-label{display:inline-block;padding:6px 14px;border-radius:999px;background:<?= $c['primary'];?>;color:#fff;font-size:12px;font-weight:700;margin-bottom:18px;text-transform:uppercase;letter-spacing:1px;}
.crf-hero h1{font-size:clamp(32px,4.5vw,56px);font-weight:900;line-height:1.05;margin-bottom:18px;color:<?= $heroText;?>;}
.crf-hero p{font-size:17px;line-height:1.6;color:<?= $heroMuted;?>;margin-bottom:28px;}
.crf-hero-btns{display:flex;gap:14px;flex-wrap:wrap;}
<?php if($isHeroDark): ?>
.crf-hero .crf-btn-primary{background:#fff;color:<?= $c['primary-dark'];?>;}
.crf-hero .crf-btn-ghost{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25);}
<?php else: ?>
.crf-hero .crf-btn-primary{background:<?= $c['primary'];?>;color:#fff;}
.crf-hero .crf-btn-ghost{background:rgba(0,0,0,.05);color:<?= $heroText;?>;border:1px solid <?= $c['border'];?>;}
<?php endif; ?>
.crf-btn{padding:14px 28px;border-radius:10px;font-weight:800;font-size:15px;cursor:pointer;border:none;transition:transform .1s;display:inline-block;text-align:center;}
.crf-btn:hover{transform:translateY(-2px);}
.crf-hero-stats{display:flex;gap:32px;margin-top:32px;}
.crf-hero-stats div{font-size:13px;color:<?= $heroSubtle;?>;}
.crf-hero-stats b{display:block;font-size:24px;font-weight:800;color:<?= $heroText;?>;}
.crf-hero-img{width:100%;border-radius:16px;box-shadow:0 24px 64px rgba(0,0,0,.25);object-fit:cover;}
.crf-section{max-width:1200px;margin:0 auto;padding:72px 24px;}
.crf-section-title{font-size:28px;font-weight:800;margin-bottom:8px;color:<?= $text;?>;display:flex;align-items:center;justify-content:space-between;}
.crf-section-title a{font-size:14px;color:<?= $c['primary'];?>;font-weight:700;}
.crf-sub{font-size:15px;color:<?= $c['muted'];?>;margin-bottom:32px;}
.crf-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;}
.crf-card{background:<?= $c['surface'];?>;border:1px solid <?= $c['border'];?>;border-radius:14px;overflow:hidden;transition:transform .2s,box-shadow .2s;display:flex;flex-direction:column;}
.crf-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,.1);}
.crf-card-img{aspect-ratio:4/3;overflow:hidden;background:<?= $c['border'];?>;position:relative;}
.crf-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .4s;}
.crf-card:hover .crf-card-img img{transform:scale(1.05);}
.crf-card-badge{position:absolute;top:12px;left:12px;padding:5px 12px;border-radius:999px;background:<?= $c['primary'];?>;color:#fff;font-size:10px;font-weight:800;text-transform:uppercase;}
.crf-card-body{padding:18px;flex:1;display:flex;flex-direction:column;}
.crf-card-name{font-size:16px;font-weight:800;margin-bottom:8px;color:<?= $text;?>;line-height:1.3;}
.crf-card-desc{font-size:13px;color:<?= $c['muted'];?>;margin-bottom:12px;line-height:1.5;flex:1;}
.crf-card-meta{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:12px;font-size:12px;color:<?= $c['muted'];?>;}
.crf-pill{padding:3px 10px;border-radius:999px;background:rgba(5,150,105,.1);color:#047857;font-weight:700;}
.crf-card-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;}
.crf-card-price{font-size:18px;font-weight:800;color:<?= $c['primary'];?>;}
.crf-card-old{font-size:13px;color:<?= $c['muted'];?>;text-decoration:line-through;margin-left:4px;}
.crf-card-btn{width:36px;height:36px;border-radius:50%;border:none;background:<?= $c['primary'];?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;cursor:pointer;}
.crf-card-btn:hover{background:<?= $c['primary-dark'];?>;}
.crf-featured{display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:center;background:<?= $c['surface'];?>;border-radius:24px;border:1px solid <?= $c['border'];?>;padding:48px;}
.crf-featured img{width:100%;border-radius:16px;}
.crf-featured h3{font-size:32px;font-weight:800;margin-bottom:12px;color:<?= $text;?>;}
.crf-featured p{font-size:15px;line-height:1.7;color:<?= $c['muted'];?>;margin-bottom:20px;}
.crf-list{display:flex;flex-direction:column;gap:12px;margin-bottom:24px;}
.crf-list div{font-size:14px;color:<?= $c['muted'];?>;display:flex;align-items:center;gap:10px;}
.crf-check{width:20px;height:20px;background:<?= $c['primary'];?>;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;}
.crf-membership-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;}
.crf-membership{border:2px solid <?= $c['border'];?>;border-radius:20px;padding:32px;background:<?= $c['surface'];?>;display:flex;flex-direction:column;}
.crf-membership.popular{border-color:<?= $c['primary'];?>;position:relative;}
.crf-popular-tag{position:absolute;top:-13px;left:50%;transform:translateX(-50%);background:<?= $c['primary'];?>;color:#fff;padding:4px 16px;border-radius:999px;font-size:11px;font-weight:800;}
.crf-membership h4{font-size:20px;font-weight:800;margin-bottom:8px;}
.crf-membership .price{font-size:36px;font-weight:900;color:<?= $c['primary'];?>;margin-bottom:6px;}
.crf-membership .cycle{font-size:13px;color:<?= $c['muted'];?>;margin-bottom:20px;}
.crf-membership ul{list-style:none;display:flex;flex-direction:column;gap:10px;margin-bottom:24px;flex:1;}
.crf-membership li{font-size:14px;color:<?= $c['muted'];?>;padding-left:24px;position:relative;}
.crf-membership li::before{content:'✓';position:absolute;left:0;color:<?= $c['primary'];?>;font-weight:800;}
.crf-membership .btn{width:100%;padding:14px;border-radius:10px;background:<?= $c['primary'];?>;color:#fff;border:none;font-weight:800;cursor:pointer;}
.crf-membership .btn:hover{background:<?= $c['primary-dark'];?>;}
.crf-testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;}
.crf-testi{background:<?= $c['surface'];?>;border:1px solid <?= $c['border'];?>;border-radius:16px;padding:24px;}
.crf-testi p{font-size:14px;line-height:1.7;margin-bottom:16px;color:<?= $text;?>;}
.crf-testi strong{display:block;font-weight:800;color:<?= $text;?>;}
.crf-testi span{font-size:13px;color:<?= $c['muted'];?>;}
.crf-faq-item{border-bottom:1px solid <?= $c['border'];?>;padding:18px 0;}
.crf-faq-q{font-size:16px;font-weight:800;color:<?= $text;?>;cursor:pointer;display:flex;justify-content:space-between;align-items:center;}
.crf-faq-a{font-size:14px;color:<?= $c['muted'];?>;margin-top:8px;line-height:1.6;display:none;}
.crf-faq-a.open{display:block;}
.crf-newsletter-2{background:<?= $c['primary'];?>;color:#fff;padding:64px 24px;text-align:center;border-radius:0;}
.crf-newsletter-2 h3{font-size:32px;font-weight:800;margin-bottom:10px;}
.crf-newsletter-2 p{opacity:.9;margin-bottom:24px;}
@media(max-width:1024px){
  .crf-hero-inner{grid-template-columns:1fr;}
  .crf-hero-img{max-width:480px;margin:0 auto;}
  .crf-grid{grid-template-columns:repeat(2,1fr);}
  .crf-featured,.crf-membership-grid,.crf-testi-grid{grid-template-columns:1fr;}
}
@media(max-width:640px){
  .crf-grid{grid-template-columns:1fr;}
  .crf-hero-btns{flex-direction:column;}
}
</style>

<section class="crf-hero">
  <div class="crf-hero-inner">
    <div>
      <span class="crf-hero-label">Creator Store</span>
      <h1>Learn, build and grow with premium courses & digital products</h1>
      <p>Everything you need to master your craft — expert-led courses, ready-to-use templates and exclusive memberships.</p>
      <div class="crf-hero-btns">
        <a href="#courses" class="crf-btn crf-btn-primary">Explore Courses</a>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="crf-btn crf-btn-ghost">Browse Store</a>
      </div>
      <div class="crf-hero-stats">
        <div><b><?= number_format($allCount); ?>+</b>Courses & products</div>
        <div><b><?= number_format($totalStudents + $totalSold); ?>+</b>Students & buyers</div>
        <div><b>24/7</b>Instant access</div>
      </div>
    </div>
    <div>
      <?php if($heroImage && file_exists(FCPATH . ($heroProduct->item_image ?? ''))): ?>
        <img src="<?= base_url($heroImage); ?>" class="crf-hero-img" alt="<?= htmlspecialchars($heroProduct->item_name ?? ''); ?>">
      <?php else: ?>
        <img src="<?= $heroImage; ?>" class="crf-hero-img" alt="">
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if(!empty($courses)): ?>
<section class="crf-section" id="courses">
  <div class="crf-section-title">
    <div>Featured Courses</div>
    <a href="<?= base_url('store/' . $slug . '/products?type=course'); ?>">All courses &rarr;</a>
  </div>
  <p class="crf-sub">Self-paced lessons designed to take you from beginner to confident creator.</p>
  <div class="crf-grid">
    <?php foreach($courses as $p):
      $price = $p->effective_price ?? $p->sales_price; $old = $p->original_price ?? $p->sales_price; $disc = $old > $price;
    ?>
    <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="crf-card">
      <div class="crf-card-img">
        <?php if($p->item_image && file_exists($p->item_image)): ?>
          <?= mp_image_tag($p->item_image, ['width' => 600, 'alt' => htmlspecialchars($p->item_name)]); ?>
        <?php else: ?><div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:<?= $c['border'];?>;color:<?= $c['muted'];?>;font-size:60px;font-weight:800;"><?= htmlspecialchars(substr($p->item_name,0,1)); ?></div><?php endif; ?>
        <span class="crf-card-badge">Course</span>
      </div>
      <div class="crf-card-body">
        <div class="crf-card-name"><?= htmlspecialchars($p->item_name); ?></div>
        <div class="crf-card-desc"><?= htmlspecialchars(strip_tags($p->description ?? '')); ?></div>
        <div class="crf-card-meta">
          <span class="crf-pill">Available</span>
          <?php if($p->sold_count > 0): ?><span><?= number_format($p->sold_count); ?> students</span><?php endif; ?>
        </div>
        <div class="crf-card-footer">
          <div><span class="crf-card-price"><?= sf_currency($price, $store_currency ?? null); ?></span><?php if($disc): ?><span class="crf-card-old"><?= sf_currency($old, $store_currency ?? null); ?></span><?php endif; ?></div>
          <button class="crf-card-btn" onclick="event.preventDefault();event.stopPropagation();addToCart(<?= $p->id; ?>,'course','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,0)">+</button>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if(!empty($digitals)): ?>
<section class="crf-section" id="products" style="background:<?= $c['surface'];?>;">
  <div class="crf-section-title">
    <div>Digital Products</div>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">View all &rarr;</a>
  </div>
  <p class="crf-sub">Templates, guides, tools and resources you can download and use instantly.</p>
  <div class="crf-grid">
    <?php foreach($digitals as $p):
      $price = $p->effective_price ?? $p->sales_price; $old = $p->original_price ?? $p->sales_price; $disc = $old > $price;
      $pt = $p->product_type ?? 'digital'; $badge = ['digital'=>'Download','course'=>'Course','membership'=>'Membership','service'=>'Service','physical'=>'Product'][$pt] ?? 'Product';
    ?>
    <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="crf-card">
      <div class="crf-card-img">
        <?php if($p->item_image && file_exists($p->item_image)): ?>
          <?= mp_image_tag($p->item_image, ['width' => 600, 'alt' => htmlspecialchars($p->item_name)]); ?>
        <?php else: ?><div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:<?= $c['border'];?>;color:<?= $c['muted'];?>;font-size:60px;font-weight:800;"><?= htmlspecialchars(substr($p->item_name,0,1)); ?></div><?php endif; ?>
        <span class="crf-card-badge"><?= $badge; ?></span>
      </div>
      <div class="crf-card-body">
        <div class="crf-card-name"><?= htmlspecialchars($p->item_name); ?></div>
        <div class="crf-card-desc"><?= htmlspecialchars(strip_tags($p->description ?? '')); ?></div>
        <div class="crf-card-meta">
          <span class="crf-pill">Available</span>
          <?php if($p->sold_count > 0): ?><span><?= number_format($p->sold_count); ?> sold</span><?php endif; ?>
        </div>
        <div class="crf-card-footer">
          <div><span class="crf-card-price"><?= sf_currency($price, $store_currency ?? null); ?></span><?php if($disc): ?><span class="crf-card-old"><?= sf_currency($old, $store_currency ?? null); ?></span><?php endif; ?></div>
          <button class="crf-card-btn" onclick="event.preventDefault();event.stopPropagation();addToCart(<?= $p->id; ?>,'<?= $pt; ?>','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)">+</button>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if(!empty($heroProduct) && $heroProduct->product_type === 'course'): ?>
<section class="crf-section">
  <div class="crf-featured">
    <div>
      <span class="crf-card-badge" style="position:static;display:inline-block;margin-bottom:16px;">Featured Course</span>
      <h3><?= htmlspecialchars($heroProduct->item_name); ?></h3>
      <p><?= htmlspecialchars(strip_tags($heroProduct->description ?? '')); ?></p>
      <div class="crf-list">
        <div><span class="crf-check">✓</span>Self-paced, on-demand video lessons</div>
        <div><span class="crf-check">✓</span>Lifetime access on any device</div>
        <div><span class="crf-check">✓</span>Certificate of completion</div>
      </div>
      <a href="<?= base_url('store/' . $slug . '/product/' . $heroProduct->id); ?>" class="crf-btn crf-btn-primary" style="background:<?= $c['primary'];?>;color:#fff;">Enroll Now &rarr;</a>
    </div>
    <div>
      <?php if($heroProduct->item_image && file_exists($heroProduct->item_image)): ?>
        <img src="<?= base_url($heroProduct->item_image); ?>" alt="<?= htmlspecialchars($heroProduct->item_name); ?>">
      <?php else: ?>
        <div style="width:100%;height:300px;display:flex;align-items:center;justify-content:center;background:<?= $c['border'];?>;color:<?= $c['muted'];?>;font-size:80px;font-weight:800;"><?= htmlspecialchars(substr($heroProduct->item_name,0,1)); ?></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if(!empty($memberships)): ?>
<section class="crf-section" id="memberships" style="background:<?= $c['surface'];?>;">
  <div class="crf-section-title">
    <div>Memberships</div>
    <a href="<?= base_url('store/' . $slug . '/products?type=membership'); ?>">See options &rarr;</a>
  </div>
  <p class="crf-sub">Join an inner circle of creators and get ongoing access, coaching and resources.</p>
  <div class="crf-membership-grid">
    <?php foreach($memberships as $i => $p):
      $price = $p->effective_price ?? $p->sales_price; $billing = $p->membership_billing ?? 'monthly';
    ?>
    <div class="crf-membership <?= $i === 0 ? 'popular' : ''; ?>">
      <?php if($i === 0): ?><div class="crf-popular-tag">Most Popular</div><?php endif; ?>
      <h4><?= htmlspecialchars($p->item_name); ?></h4>
      <div class="price"><?= sf_currency($price, $store_currency ?? null); ?></div>
      <div class="cycle">per <?= htmlspecialchars($billing); ?></div>
      <ul>
        <li>Instant access to all member content</li>
        <li>New resources every <?= htmlspecialchars($billing); ?></li>
        <li>Community and direct support</li>
      </ul>
      <button class="btn" onclick="addToCart(<?= $p->id; ?>,'membership','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,0)">Join Now</button>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php $testimonials = $testimonials ?? []; if(!empty($testimonials) || count($testimonials) >= 0): ?>
<section class="crf-section">
  <div class="crf-section-title">What students are saying</div>
  <div class="crf-testi-grid">
    <?php foreach(array_slice($testimonials,0,3) as $t): ?>
    <div class="crf-testi">
      <p>"<?= htmlspecialchars($t->content ?? 'Great content, very practical and easy to follow.'); ?>"</p>
      <strong><?= htmlspecialchars($t->name ?? 'Happy Customer'); ?></strong>
      <span><?= htmlspecialchars($t->role ?? 'Student'); ?></span>
    </div>
    <?php endforeach; ?>
    <?php if(count($testimonials) === 0): for($i=0;$i<3;$i++): ?>
    <div class="crf-testi">
      <p>"<?= ['The course completely changed how I approach my business.','Templates saved me hours every week.','Worth every penny — clear, concise and actionable.'][$i]; ?>"</p>
      <strong><?= ['Adebola O.','Chioma N.','Tunde K.'][$i]; ?></strong>
      <span>Student</span>
    </div>
    <?php endfor; endif; ?>
  </div>
</section>
<?php endif; ?>

<section class="crf-section">
  <div class="crf-section-title">Frequently asked questions</div>
  <div class="crf-faq-item">
    <div class="crf-faq-q" onclick="this.nextElementSibling.classList.toggle('open');this.querySelector('span').textContent=this.nextElementSibling.classList.contains('open')?'−':'+'">How do I access my course? <span style="font-size:22px;">+</span></div>
    <div class="crf-faq-a">After payment you get instant lifetime access. Log in to your dashboard to start watching lessons, download resources and track progress.</div>
  </div>
  <div class="crf-faq-item">
    <div class="crf-faq-q" onclick="this.nextElementSibling.classList.toggle('open');this.querySelector('span').textContent=this.nextElementSibling.classList.contains('open')?'−':'+'">Can I get a refund? <span style="font-size:22px;">+</span></div>
    <div class="crf-faq-a">We stand by the quality of our products. Contact support within the refund window shown in the store if you are not satisfied.</div>
  </div>
  <div class="crf-faq-item">
    <div class="crf-faq-q" onclick="this.nextElementSibling.classList.toggle('open');this.querySelector('span').textContent=this.nextElementSibling.classList.contains('open')?'−':'+'">What payment methods are accepted? <span style="font-size:22px;">+</span></div>
    <div class="crf-faq-a">Card, bank transfer, USSD and mobile wallets via Paystack, plus WhatsApp checkout and pay-on-delivery for physical items.</div>
  </div>
</section>

<section class="crf-newsletter-2">
  <h3>Get new drops before anyone else</h3>
  <p>Join the newsletter for early access to courses, discounts and free resources.</p>
  <form style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;max-width:520px;margin:0 auto;" onsubmit="return mpNewsletterSubmit(event)">
    <input type="email" required placeholder="you@example.com" style="padding:14px 18px;border-radius:10px;border:none;min-width:260px;font-size:15px;outline:none;">
    <button type="submit" class="crf-btn" style="background:#fff;color:<?= $c['primary'];?>;">Subscribe</button>
  </form>
</section>

<?php include(APPPATH.'views/themes/creator_focus/_footer.php'); ?>
