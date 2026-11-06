<?php defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance(); ?>
<!-- Top-selling items card. Shared between the general dashboard row and the
     tyre-shop promoted row so the same five lines render in either lane. -->
<div class="mp-card">
  <div class="mp-card-head">
    <h3><?= htmlspecialchars($card_title); ?></h3>
    <a href="<?= base_url('reports/item_sales'); ?>" class="mp-card-link"><?= htmlspecialchars($range_label); ?></a>
  </div>
  <div class="mp-card-body">
    <?php if(!empty($top_products)): ?>
    <?php $i=1; foreach(array_slice($top_products, 0, 5) as $prod): ?>
    <div class="mp-top-prod">
      <div class="mp-top-prod-rank <?= $i <= 3 ? 'r'.$i : ''; ?>"><?= $i ?></div>
      <div class="mp-top-prod-info"><div class="mp-top-prod-name"><?= htmlspecialchars($prod['name']) ?></div><div class="mp-top-prod-meta"><?= number_format($prod['qty'] ?? 0); ?> sold</div></div>
      <div class="mp-top-prod-amt"><?= $CI->currency($prod['revenue']) ?></div>
    </div>
    <?php $i++; endforeach; ?>
    <?php else: ?>
    <div class="mp-empty-state">Not Enough Data Yet</div>
    <?php endif; ?>
  </div>
</div>
