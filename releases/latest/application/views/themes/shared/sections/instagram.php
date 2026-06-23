<?php $igList = $instagram_posts ?? []; ?>
<?php if(!empty($igList)): ?>
<div class="mp-section">
  <div class="mp-section-title">Follow Us on Instagram</div>
  <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
    <?php foreach($igList as $p): ?>
    <?php if($p->link_url): ?>
    <a href="<?= htmlspecialchars($p->link_url); ?>" target="_blank" style="text-decoration:none; display:block; position:relative; overflow:hidden; border-radius:var(--mp-radius-sm); aspect-ratio:1;">
    <?php else: ?>
    <div style="position:relative; overflow:hidden; border-radius:var(--mp-radius-sm); aspect-ratio:1;">
    <?php endif; ?>
      <img src="<?= base_url($p->image_url); ?>" style="width:100%; height:100%; object-fit:cover; transition:transform .3s;" loading="lazy" alt="<?= htmlspecialchars($p->caption ?? ''); ?>">
      <?php if($p->caption): ?>
      <div style="position:absolute; bottom:0; left:0; right:0; padding:12px; background:linear-gradient(transparent, rgba(0,0,0,0.7)); color:#fff; font-size:12px; opacity:0; transition:opacity .3s;" class="ig-caption"><?= htmlspecialchars($p->caption); ?></div>
      <?php endif; ?>
    <?php if($p->link_url): ?></a><?php else: ?></div><?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>
<style>
.mp-section a:hover .ig-caption { opacity:1 !important; }
.mp-section a:hover img { transform:scale(1.05); }
</style>
<?php endif; ?>
