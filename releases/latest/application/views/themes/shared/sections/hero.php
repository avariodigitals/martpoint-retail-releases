<?php $banners = $hero_banners ?? []; ?>
<?php if(!empty($banners)): ?>
<div class="mp-hero" id="mp-hero" data-slides="<?= count($banners); ?>">
  <div class="mp-hero-track" id="mp-hero-track">
    <?php foreach($banners as $b): ?>
    <div class="mp-hero-slide">
      <picture>
        <source media="(max-width:767px)" srcset="<?= $b->mobile_image ? base_url($b->mobile_image) : ($b->desktop_image ? base_url($b->desktop_image) : ''); ?>">
        <img src="<?= $b->desktop_image ? base_url($b->desktop_image) : ''; ?>" class="mp-hero-img" alt="<?= htmlspecialchars($b->banner_title); ?>" loading="eager">
      </picture>
      <div class="mp-hero-overlay">
        <div class="mp-hero-content">
          <div class="mp-hero-title"><?= htmlspecialchars($b->banner_title ?: ($settings->store_headline ?: ($store->store_name ?? 'Welcome'))); ?></div>
          <div class="mp-hero-subtitle"><?= htmlspecialchars($b->banner_subtitle ?: ($settings->store_subheadline ?: '')); ?></div>
          <div class="mp-hero-btns">
            <?php if($b->button_text && $b->button_url): ?>
            <a href="<?= htmlspecialchars($b->button_url); ?>" class="mp-hero-btn"><?= htmlspecialchars($b->button_text); ?></a>
            <?php else: ?>
            <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="mp-hero-btn">Shop Now</a>
            <?php endif; ?>
            <?php if($settings->allow_services ?? false): ?>
            <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>" class="mp-hero-btn-secondary">Book a Service</a>
            <?php else: ?>
            <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="mp-hero-btn-secondary">Explore All</a>
            <?php endif; ?>
          </div>
          <div class="mp-hero-trust">
            <div class="mp-hero-trust-item"><span class="dot"></span> 100% Original</div>
            <div class="mp-hero-trust-item"><span class="dot"></span> Fast Delivery</div>
            <div class="mp-hero-trust-item"><span class="dot"></span> Secure Payments</div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php if(count($banners) > 1): ?>
  <div class="mp-hero-dots">
    <?php foreach($banners as $idx => $b): ?>
    <button class="mp-hero-dot<?= $idx===0 ? ' active' : ''; ?>" onclick="goToHeroSlide(<?= $idx; ?>)"></button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php if(count($banners) > 1): ?>
<script>
(function(){
  const track = document.getElementById('mp-hero-track');
  const dots = document.querySelectorAll('.mp-hero-dot');
  const slides = track.querySelectorAll('.mp-hero-slide');
  let current = 0;
  let timer;
  function goToSlide(idx){
    current = idx;
    track.style.transform = 'translateX(-' + (idx * 100) + '%)';
    dots.forEach((d,i) => d.classList.toggle('active', i===idx));
  }
  window.goToHeroSlide = goToSlide;
  function next(){ goToSlide((current + 1) % slides.length); }
  timer = setInterval(next, 5000);
  const hero = document.getElementById('mp-hero');
  hero.addEventListener('mouseenter', () => clearInterval(timer));
  hero.addEventListener('mouseleave', () => timer = setInterval(next, 5000));
})();
</script>
<?php endif; ?>
<?php elseif(!empty($settings->store_headline) || !empty($settings->store_banner)): ?>
<div class="mp-hero">
  <div class="mp-hero-slide">
    <?php if($settings->store_banner && file_exists($settings->store_banner)): ?>
      <img src="<?= base_url($settings->store_banner); ?>" class="mp-hero-img" alt="Banner" loading="eager">
    <?php else: ?>
      <div class="mp-hero-img" style="background:linear-gradient(135deg,var(--mp-primary),var(--mp-secondary)); display:flex; align-items:center; justify-content:center; color:#fff; font-size:28px; font-weight:800; text-align:center; padding:20px;">
        <?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Welcome')); ?>
      </div>
    <?php endif; ?>
    <div class="mp-hero-overlay">
      <div class="mp-hero-content">
        <div class="mp-hero-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Welcome')); ?></div>
        <div class="mp-hero-subtitle"><?= htmlspecialchars($settings->store_subheadline ?: ''); ?></div>
        <div class="mp-hero-btns">
          <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="mp-hero-btn">Shop Now</a>
          <?php if($settings->allow_services ?? false): ?>
          <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>" class="mp-hero-btn-secondary">Book a Service</a>
          <?php else: ?>
          <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="mp-hero-btn-secondary">Explore All</a>
          <?php endif; ?>
        </div>
        <div class="mp-hero-trust">
          <div class="mp-hero-trust-item"><span class="dot"></span> 100% Original</div>
          <div class="mp-hero-trust-item"><span class="dot"></span> Fast Delivery</div>
          <div class="mp-hero-trust-item"><span class="dot"></span> Secure Payments</div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
