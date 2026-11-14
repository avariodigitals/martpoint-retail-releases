<?php if(!empty($row['document_url'])): ?><div><a href="<?= htmlspecialchars($row['document_url'],ENT_QUOTES,'UTF-8'); ?>">View quotation</a> · Informational</div><?php endif; ?>
<?php if(!empty($row['historical'])): ?>
<div class="history-row-meta" style="font-size:12px;line-height:1.6;color:#536372;white-space:normal">
 <span>Read-only history · excluded from current balance</span>
 <?php if(isset($row['historical_amount'])): ?><div>Historical total: <?= store_number_format($row['historical_amount']); ?></div><?php endif; ?>
 <?php if(isset($row['historical_debit'])): ?><div>Source debit: <?= store_number_format($row['historical_debit']); ?> · Source credit: <?= store_number_format($row['historical_credit']); ?></div><?php endif; ?>
 <?php if(!empty($row['history_document_id'])): ?><a href="<?= base_url('customer_history/document/'.(int)$row['history_document_id']).(!empty($history_mobile)?'?mobile=1':''); ?>">View original record</a><?php endif; ?>
 <?php if(!empty($row['review_reason'])): ?><div style="color:#8a4b08"><?= htmlspecialchars($row['review_reason'],ENT_QUOTES,'UTF-8'); ?></div><?php endif; ?>
 <?php if(empty($row['date'])): ?><div>Date unavailable · Source: <?= htmlspecialchars($row['raw_date']??'',ENT_QUOTES,'UTF-8'); ?></div><?php endif; ?>
 <?php if(!empty($row['history_lines'])): ?><details data-history-products><summary>Products as recorded (<?= count($row['history_lines']); ?>)</summary><ul style="padding-left:18px">
 <?php foreach($row['history_lines'] as $history_line): ?><li><?= htmlspecialchars(trim($history_line['product_name'])?:(trim($history_line['description'])?:'Description unavailable in source'),ENT_QUOTES,'UTF-8'); ?><?php if(trim($history_line['description'])!=='' && trim($history_line['product_name'])!==''): ?> · <?= htmlspecialchars($history_line['description'],ENT_QUOTES,'UTF-8'); ?><?php endif; ?> · Qty <?= htmlspecialchars($history_line['quantity'],ENT_QUOTES,'UTF-8'); ?></li><?php endforeach; ?>
 </ul></details><?php endif; ?>
</div>
<?php endif; ?>

<?php if(!empty($row['historical']) && !defined('MP_HISTORY_PRINT_BINDING')): define('MP_HISTORY_PRINT_BINDING',true); ?>
<script>
addEventListener('beforeprint',function(){document.querySelectorAll('details[data-history-products]').forEach(function(el){if(!el.open){el.dataset.historyPrintClosed='1';el.open=true;}});});
addEventListener('afterprint',function(){document.querySelectorAll('details[data-history-print-closed]').forEach(function(el){el.open=false;delete el.dataset.historyPrintClosed;});});
</script>
<?php endif; ?>
