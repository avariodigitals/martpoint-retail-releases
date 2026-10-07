<?php
/** Shared quotation line-item table. Expects $q (quotation) and $money (formatter). */
if (empty($q->items)) return;
?>
<section class="card"><h2>Items</h2><table><thead><tr><th>Description</th><th>Qty</th><th>Total</th></tr></thead><tbody>
<?php foreach($q->items as $item): ?><tr><td><?= htmlspecialchars($item->item_name ?? 'Item') ?><?php if(!empty($item->description)): ?><br><span class="muted small"><?= htmlspecialchars($item->description) ?></span><?php endif; ?></td><td><?= htmlspecialchars((string)$item->quotation_qty) ?></td><td><?= $money($item->total_cost) ?></td></tr><?php endforeach; ?>
</tbody></table></section>
