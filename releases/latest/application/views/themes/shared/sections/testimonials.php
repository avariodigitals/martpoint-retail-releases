<?php $tlist = $testimonials ?? []; ?>
<?php if(!empty($tlist)): ?>
<div class="mp-section">
  <div class="mp-section-title">What Our Customers Say</div>
  <div class="mp-grid" style="grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));">
    <?php foreach($tlist as $t): ?>
    <div style="background:var(--mp-white); border:1px solid var(--mp-border); border-radius:var(--mp-radius-sm); padding:24px; display:flex; flex-direction:column; gap:12px;">
      <div style="display:flex; align-items:center; gap:12px;">
        <?php if($t->customer_photo && file_exists($t->customer_photo)): ?>
          <img src="<?= base_url($t->customer_photo); ?>" alt="<?= htmlspecialchars($t->customer_name); ?>" style="width:48px; height:48px; border-radius:50%; object-fit:cover; border:2px solid var(--mp-border);">
        <?php else: ?>
          <div style="width:48px; height:48px; border-radius:50%; background:var(--mp-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:18px;"><?= strtoupper(substr($t->customer_name, 0, 1)); ?></div>
        <?php endif; ?>
        <div>
          <div style="font-weight:700; font-size:14px; color:var(--mp-dark);"><?= htmlspecialchars($t->customer_name); ?></div>
          <div style="color:#F59E0B; font-size:14px;"><?= str_repeat('&#9733;', $t->rating) . str_repeat('&#9734;', 5 - $t->rating); ?></div>
        </div>
      </div>
      <div style="font-size:14px; color:#334155; line-height:1.6; font-style:italic;">"<?= htmlspecialchars($t->testimonial_text); ?>"</div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
