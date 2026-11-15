<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $storeTestimonials = $testimonials ?? []; if(!empty($storeTestimonials)): ?>
<section class="mp-store-testimonials" aria-labelledby="mp-store-testimonials-title">
  <div class="mp-store-testimonials-inner">
    <p class="mp-store-testimonials-eyebrow">From our customers</p>
    <h2 id="mp-store-testimonials-title">Customer experiences</h2>
    <div class="mp-store-testimonials-grid">
      <?php foreach(array_slice($storeTestimonials, 0, 6) as $testimonial):
        $name = trim((string)($testimonial->customer_name ?? '')) ?: 'Customer';
        $rating = max(1, min(5, (int)($testimonial->rating ?? 5)));
        $photo = trim((string)($testimonial->customer_photo ?? ''));
        $hasPhoto = $photo !== '' && is_file(FCPATH . ltrim($photo, '/'));
      ?>
      <article class="mp-store-testimonial-card">
        <div class="mp-store-testimonial-heading">
          <?php if($hasPhoto): ?>
            <img src="<?= htmlspecialchars(base_url($photo), ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy">
          <?php else: ?>
            <span class="mp-store-testimonial-initial" aria-hidden="true"><?= htmlspecialchars(mb_strtoupper(mb_substr($name, 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
          <?php endif; ?>
          <div><strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></strong><span class="mp-store-testimonial-stars" aria-label="<?= $rating; ?> out of 5"><?= str_repeat('★', $rating) . str_repeat('☆', 5 - $rating); ?></span></div>
        </div>
        <p><?= nl2br(htmlspecialchars((string)($testimonial->testimonial_text ?? ''), ENT_QUOTES, 'UTF-8')); ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<style>
.mp-store-testimonials{padding:42px 24px 54px;background:var(--mp-soft,#f7f8fa);color:var(--mp-dark,#172033)}.mp-store-testimonials-inner{max-width:1280px;margin:0 auto}.mp-store-testimonials-eyebrow{margin:0 0 6px;color:var(--mp-primary,#155eef);font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}.mp-store-testimonials h2{margin:0 0 20px;font-size:clamp(22px,3vw,30px);line-height:1.2}.mp-store-testimonials-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,260px),1fr));gap:14px}.mp-store-testimonial-card{padding:18px;border:1px solid var(--mp-border,#e4e7ec);border-radius:12px;background:#fff}.mp-store-testimonial-heading{display:flex;align-items:center;gap:11px}.mp-store-testimonial-heading img,.mp-store-testimonial-initial{width:42px;height:42px;flex:0 0 42px;border-radius:50%;object-fit:cover}.mp-store-testimonial-initial{display:grid;place-items:center;background:var(--mp-primary,#155eef);color:#fff;font-weight:700}.mp-store-testimonial-heading strong,.mp-store-testimonial-stars{display:block}.mp-store-testimonial-stars{margin-top:3px;color:#c27a12;letter-spacing:.08em;font-size:13px}.mp-store-testimonial-card>p{margin:14px 0 0;color:var(--mp-gray,#667085);font-size:14px;line-height:1.65;overflow-wrap:anywhere}@media(max-width:700px){.mp-store-testimonials{padding:32px 16px 40px}}
</style>
<?php endif; ?>
