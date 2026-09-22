<?php
/**
 * Parfum Core — catalogue listing. Markup only; CSS/JS in header.php.
 */
include APPPATH . 'views/themes/parfum_core/_skin.php';

$slug  = $settings->store_slug ?? '';
$cur   = $store_currency ?? null;
$waNum = ($settings->allow_whatsapp ?? 1) ? preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '') : '';
?>

<div class="pf-wrap">
  <div class="pf-crumbs">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <span>Shop</span>
    <?php if(!empty($search)): ?><span class="sep">/</span><span>&ldquo;<?= htmlspecialchars($search); ?>&rdquo;</span><?php endif; ?>
  </div>

  <div class="pf-page">
    <h1 class="pf-h1"><?= !empty($search) ? 'Search: ' . htmlspecialchars($search) : 'Shop'; ?></h1>
    <div class="pf-count"><?= (int)($total ?? 0); ?> fragrances</div>

    <div class="pf-filters">
      <?php if($settings->show_search ?? 1): ?>
      <div class="pf-search">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search fragrances, notes, houses..." value="<?= htmlspecialchars($search ?? ''); ?>" onkeydown="if(event.key==='Enter'){const u=new URL(location.href);u.searchParams.set('search',this.value);u.searchParams.delete('page');location.href=u.href;}">
      </div>
      <?php endif; ?>
      <?php if(($settings->show_categories ?? 1) && !empty($categories)): ?>
      <div class="pf-chips">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pf-chip <?= empty($category_id) ? 'active' : ''; ?>">All</a>
        <?php foreach($categories as $cat): ?>
        <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pf-chip <?= ($category_id ?? 0) == $cat->id ? 'active' : ''; ?>"><?= htmlspecialchars($cat->category_name); ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if(!empty($products)): ?>
    <div class="pf-grid">
      <?php foreach($products as $p) pf_card($p, $cur, $settings, $slug); ?>
    </div>

    <?php if(($total_pages ?? 1) > 1): ?>
    <div class="pf-pagination">
      <?php if($page > 1): ?>
      <a class="pf-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page-1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo;</a>
      <?php endif; ?>
      <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
      <a class="pf-page-btn <?= $i == $page ? 'active' : ''; ?>" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $i; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?= $i; ?></a>
      <?php endfor; ?>
      <?php if($page < $total_pages): ?>
      <a class="pf-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page+1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&raquo;</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="pf-empty">
      <div class="pf-empty-title">Nothing found</div>
      <p class="pf-empty-text">Try a different search or browse the full collection.</p>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pf-btn pf-btn-accent">View All Fragrances</a>
      <?php if($waNum): ?>
      <div style="margin-top:18px;"><a href="https://wa.me/<?= $waNum; ?>?text=<?= rawurlencode('Hello ' . ($store->store_name ?? '') . ', I am looking for a fragrance.'); ?>" target="_blank" class="pf-sec-link" style="color:#25D366;">Ask on WhatsApp</a></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
