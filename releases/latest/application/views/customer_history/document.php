<?php $this->load->view('customer_history/_screen'); ?>
<?php $this->load->view('customer_history/_styles'); $source=json_decode($document->source_json,true)?:array(); ?>
<div class="history-card">
  <h2>Historical <?= htmlspecialchars(ucfirst($document->document_type),ENT_QUOTES,'UTF-8'); ?> <?= htmlspecialchars($document->document_number,ENT_QUOTES,'UTF-8'); ?></h2>
  <div class="history-note">Read-only Rose history. These recorded products and amounts do not affect current stock or the current MartPoint balance.</div>
  <p><strong><?= htmlspecialchars($customer->customer_name,ENT_QUOTES,'UTF-8'); ?></strong></p>
  <p>Date: <?= $document->document_date?show_date($document->document_date):'Needs review (source: '.htmlspecialchars($document->raw_date,ENT_QUOTES,'UTF-8').')'; ?><br>Status: <?= htmlspecialchars($document->source_status,ENT_QUOTES,'UTF-8'); ?><br><span class="history-source">Rose ID <?= (int)$document->source_id; ?> · Source class <?= (int)$document->source_class; ?></span></p>
  <?php if($document->review_reason): ?><div class="history-review"><?= htmlspecialchars($document->review_reason,ENT_QUOTES,'UTF-8'); ?></div><?php endif; ?>
  <div class="history-actions"><a href="<?= base_url('customer_history/index/'.(int)$customer->id).(!empty($history_mobile)?'?mobile=1':''); ?>">Back to history</a><button type="button" onclick="window.print()">Print</button></div>
  <div class="history-table-wrap"><table class="history-table"><thead><tr><th>Product as recorded</th><th>Code / Unit</th><th class="amount">Qty</th><th class="amount">Unit price</th><th class="amount">Tax</th><th class="amount">Discount</th><th class="amount">Line total</th></tr></thead><tbody>
  <?php foreach($document->lines as $line): ?><tr>
    <td><?= htmlspecialchars(trim($line->product_name)?:(trim($line->description)?:'Description unavailable in source'),ENT_QUOTES,'UTF-8'); ?><?php if(trim($line->description)!=='' && trim($line->product_name)!==''): ?><div class="history-row-meta"><?= nl2br(htmlspecialchars($line->description,ENT_QUOTES,'UTF-8')); ?></div><?php endif; ?></td>
    <td><?= htmlspecialchars(($line->product_code?:'').' / '.($line->unit_label?:''),ENT_QUOTES,'UTF-8'); ?></td>
    <td class="amount"><?= htmlspecialchars(rtrim(rtrim($line->quantity,'0'),'.'),ENT_QUOTES,'UTF-8'); ?></td>
    <td class="amount"><?= store_number_format($line->unit_price); ?></td><td class="amount"><?= store_number_format($line->tax); ?></td><td class="amount"><?= store_number_format($line->discount); ?></td><td class="amount"><?= store_number_format($line->line_total); ?></td>
  </tr><?php endforeach; ?>
  </tbody></table></div>
  <div class="history-totals">
    <?php foreach(array('subtotal'=>'Source subtotal','tax'=>'Source tax','discount'=>'Source discount','shipping'=>'Source shipping','total'=>'Historical total','paid_amount'=>'Source paid amount') as $field=>$label): ?>
    <div><span><?= $label; ?></span><strong><?= store_number_format($document->$field); ?></strong></div>
    <?php endforeach; ?>
  </div>
  <?php if($document->notes!==''): ?><p><?= nl2br(htmlspecialchars($document->notes,ENT_QUOTES,'UTF-8')); ?></p><?php endif; ?>
</div>
