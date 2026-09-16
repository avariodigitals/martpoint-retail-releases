<?php
/* Creator theme shared footer — include at the bottom of any creator view. */
$slug = $settings->store_slug ?? '';
$year = date('Y');
?>
<style>
/* Hide default footer and show custom */
.mp-footer,.mp-footer-space,.mp-backtop{display:none !important;}
.crf-footer{background:<?= $c['dark'];?>;color:<?= $isDark?'#A1A1AA':'#CBD5E1';?>;padding:64px 24px 24px;}
.crf-footer-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1.6fr repeat(3,1fr);gap:48px;}
.crf-footer-brand{color:#fff;font-weight:800;font-size:20px;margin-bottom:14px;display:flex;align-items:center;gap:10px;}
.crf-footer-brand img{height:36px;width:36px;object-fit:cover;border-radius:8px;}
.crf-footer-desc{line-height:1.7;font-size:14px;margin-bottom:18px;max-width:320px;}
.crf-footer-heading{color:#fff;font-size:12px;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-bottom:16px;}
.crf-footer-links{list-style:none;}
.crf-footer-links li{margin-bottom:10px;}
.crf-footer-links a{color:inherit;font-size:14px;transition:color .15s;}
.crf-footer-links a:hover{color:#fff;}
.crf-socials{display:flex;gap:10px;margin-top:16px;}
.crf-socials a{width:38px;height:38px;border-radius:999px;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;transition:background .15s;}
.crf-socials a:hover{background:<?= $c['primary'];?>;}
.crf-newsletter{display:flex;gap:8px;margin-top:14px;max-width:320px;}
.crf-newsletter input{flex:1;padding:12px 14px;border:1px solid rgba(255,255,255,.12);border-radius:8px;background:rgba(255,255,255,.05);color:#fff;font-size:14px;outline:none;}
.crf-newsletter button{padding:12px 18px;border-radius:8px;background:<?= $c['primary'];?>;color:#fff;font-weight:700;border:none;cursor:pointer;}
.crf-bottom{border-top:1px solid rgba(255,255,255,.08);max-width:1200px;margin:32px auto 0;padding-top:20px;text-align:center;font-size:13px;}
@media(max-width:900px){
  .crf-footer-inner{grid-template-columns:1fr 1fr;gap:32px;}
}
@media(max-width:640px){
  .crf-footer-inner{grid-template-columns:1fr;}
}
</style>
<footer class="crf-footer">
  <div class="crf-footer-inner">
    <div>
      <div class="crf-footer-brand">
        <?php if($logo): ?><img src="<?= $logo; ?>" alt=""><?php endif; ?>
        <?= htmlspecialchars($store->store_name ?? 'Creator Store'); ?>
      </div>
      <p class="crf-footer-desc">Premium courses, digital products and memberships built for modern creators, educators and entrepreneurs.</p>
      <form class="crf-newsletter" data-source="footer" onsubmit="return mpNewsletterSubmit(event)">
        <input type="email" placeholder="Get updates" required>
        <button type="submit">Join</button>
      </form>
    </div>
    <div>
      <div class="crf-footer-heading">Store</div>
      <ul class="crf-footer-links">
        <li><a href="<?= base_url('store/' . $slug); ?>">Home</a></li>
        <li><a href="<?= base_url('store/' . $slug . '/products?type=course'); ?>">Courses</a></li>
        <li><a href="<?= base_url('store/' . $slug . '/products?type=digital'); ?>">Products</a></li>
        <li><a href="<?= base_url('store/' . $slug . '/products?type=membership'); ?>">Memberships</a></li>
      </ul>
    </div>
    <div>
      <div class="crf-footer-heading">Support</div>
      <ul class="crf-footer-links">
        <li><a href="<?= base_url('store/' . $slug . '/products'); ?>">Browse all</a></li>
        <li><a href="<?= base_url('store/' . $slug . '/cart'); ?>">Cart</a></li>
        <?php if($waNumber): ?><li><a href="https://wa.me/<?= $waNumber; ?>" target="_blank">WhatsApp</a></li><?php endif; ?>
      </ul>
    </div>
    <div>
      <div class="crf-footer-heading">Follow</div>
      <div class="crf-socials">
        <?php $socials = $social_links ?? []; if(!empty($socials['facebook'])): ?><a href="<?= $socials['facebook']; ?>" target="_blank"><i class="fa fa-facebook"></i></a><?php endif; ?>
        <?php if(!empty($socials['instagram'])): ?><a href="<?= $socials['instagram']; ?>" target="_blank"><i class="fa fa-instagram"></i></a><?php endif; ?>
        <?php if(!empty($socials['twitter'])): ?><a href="<?= $socials['twitter']; ?>" target="_blank"><i class="fa fa-twitter"></i></a><?php endif; ?>
        <a href="<?= base_url('store/' . $slug); ?>"><i class="fa fa-globe"></i></a>
      </div>
    </div>
  </div>
  <div class="crf-bottom">&copy; <?= $year; ?> <?= htmlspecialchars($store->store_name ?? 'Creator Store'); ?>. All rights reserved.</div>
</footer>
