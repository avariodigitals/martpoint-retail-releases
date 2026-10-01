<?php
/**
 * Verdant — catalogue listing. Markup only; CSS/JS in header.php.
 */
include APPPATH . 'views/themes/verdant/_theme.php';

$slug  = $settings->store_slug ?? '';
$cur   = $store_currency ?? null;
$waNum = ($settings->allow_whatsapp ?? 1) ? preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '') : '';
?>

<div class="vd-wrap">
  <nav class="vd-crumbs" aria-label="Breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <span>Shop</span>
    <?php if(!empty($search)): ?><span class="sep">/</span><span>&ldquo;<?= htmlspecialchars($search); ?>&rdquo;</span><?php endif; ?>
  </nav>

  <div class="vd-page">
    <div class="vd-page-head">
      <h1 class="vd-display" style="font-size:clamp(38px,8vw,64px);"><?= !empty($search) ? 'Results for ' . htmlspecialchars($search) : 'Shop all'; ?></h1>
      <div class="vd-count"><?= (int)($total ?? 0); ?> products</div>
    </div>

    <div class="vd-filters">
      <?php if($settings->show_search ?? 1): ?>
      <div class="vd-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7.5"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
        <input type="text" placeholder="Search creams, serums, ingredients…" value="<?= htmlspecialchars($search ?? ''); ?>" onkeydown="if(event.key==='Enter'){var u=new URL(location.href);u.searchParams.set('search',this.value);u.searchParams.delete('page');location.href=u.href;}">
      </div>
      <?php endif; ?>
      <?php if(($settings->show_categories ?? 1) && !empty($categories)): ?>
      <div class="vd-chips">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="vd-chip <?= empty($category_id) ? 'active' : ''; ?>">All</a>
        <?php foreach($categories as $cat): ?>
        <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="vd-chip <?= ($category_id ?? 0) == $cat->id ? 'active' : ''; ?>"><?= htmlspecialchars($cat->category_name); ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if(!empty($products)): ?>
    <div class="vd-grid">
      <?php foreach($products as $p) vd_card($p, $cur, $settings, $slug); ?>
    </div>

    <?php if(($total_pages ?? 1) > 1): ?>
    <nav class="vd-pagination" aria-label="Pages">
      <?php if($page > 1): ?>
      <a class="vd-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page-1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo;</a>
      <?php endif; ?>
      <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
      <a class="vd-page-btn <?= $i == $page ? 'active' : ''; ?>" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $i; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?= $i; ?></a>
      <?php endfor; ?>
      <?php if($page < $total_pages): ?>
      <a class="vd-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page+1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&raquo;</a>
      <?php endif; ?>
    </nav>
    <?php endif; ?>

    <?php else: ?>
    <div class="vd-empty">
      <h2 class="vd-h3">Nothing found</h2>
      <p class="vd-lead">Try a different search, or browse the full collection — your skin will thank you.</p>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="vd-btn vd-btn-primary">View all products</a>
      <?php if($waNum): ?>
      <div style="margin-top:16px;"><a href="https://wa.me/<?= $waNum; ?>?text=<?= rawurlencode('Hello ' . ($store->store_name ?? '') . ', I am looking for a skincare product.'); ?>" target="_blank" rel="noopener" class="vd-link" style="color:#25D366;border-color:#25D366;">Ask on WhatsApp</a></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
