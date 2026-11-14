<?php $this->load->view('customer_history/_screen'); ?>
<?php $this->load->view('customer_history/_styles'); $history_suffix=!empty($history_mobile)?'&mobile=1':''; ?>
<div class="history-card">
  <h2>Quote &amp; Invoice History</h2>
  <p><?= htmlspecialchars($customer->customer_name,ENT_QUOTES,'UTF-8'); ?></p>
  <div class="history-note">Rose documents are preserved as read-only history. Their products are the original recorded text. Historical amounts do not change current stock or the current MartPoint balance.</div>
  <div class="history-actions">
    <a href="<?= base_url((!empty($history_mobile)?'mobile/customer_profile/':'customers/profile/').(int)$customer->id); ?>">Customer profile</a>
    <a href="<?= base_url((!empty($history_mobile)?'mobile/statement/':'customers/statement/').(int)$customer->id); ?>">Statement</a>
    <?php foreach(array(''=>'All history','quote'=>'Rose quotes','invoice'=>'Rose invoices') as $filter=>$label): ?>
    <a href="<?= base_url('customer_history/index/'.(int)$customer->id).'?type='.$filter.$history_suffix; ?>"><?= $label; ?></a>
    <?php endforeach; ?>
  </div>
  <?php if(!empty($native_quotes)): ?>
  <h3>MartPoint quotations</h3>
  <div class="history-table-wrap"><table class="history-table"><thead><tr><th>Date</th><th>Quotation</th><th>Status</th><th class="amount">Total</th></tr></thead><tbody>
  <?php foreach($native_quotes as $quote): ?><tr><td><?= show_date($quote->quotation_date); ?></td><td><a href="<?= base_url((!empty($history_mobile)?'mobile/quotation_view/':'quotation/invoice/').(int)$quote->id); ?>"><?= htmlspecialchars($quote->quotation_code,ENT_QUOTES,'UTF-8'); ?></a></td><td><?= htmlspecialchars($quote->quotation_status,ENT_QUOTES,'UTF-8'); ?></td><td class="amount"><?= store_number_format($quote->grand_total); ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
  <h3>Rose documents · <?= (int)$total; ?> records</h3>
  <div class="history-table-wrap"><table class="history-table"><thead><tr><th>Date</th><th>Document</th><th>Status</th><th class="amount">Historical total</th></tr></thead><tbody>
  <?php foreach($rows as $record): ?><tr>
    <td><?= $record->document_date?show_date($record->document_date):'Date needs review'; ?></td>
    <td><a href="<?= base_url('customer_history/document/'.(int)$record->id).(!empty($history_mobile)?'?mobile=1':''); ?>"><?= htmlspecialchars(ucfirst($record->document_type).' '.$record->document_number,ENT_QUOTES,'UTF-8'); ?></a><div class="history-source">Rose ID <?= (int)$record->source_id; ?></div></td>
    <td><?= htmlspecialchars($record->source_status,ENT_QUOTES,'UTF-8'); ?><?= $record->review_reason?' · Review flagged':''; ?></td>
    <td class="amount"><?= store_number_format($record->total); ?></td>
  </tr><?php endforeach; ?>
  <?php if(!$rows): ?><tr><td colspan="4">No Rose documents in this selection.</td></tr><?php endif; ?>
  </tbody></table></div>
  <div class="history-actions">
  <?php if($history_page>1): ?><a href="<?= base_url('customer_history/index/'.(int)$customer->id).'?type='.urlencode($history_type).'&page='.($history_page-1).$history_suffix; ?>">Previous</a><?php endif; ?>
  <?php if($history_page*50<$total): ?><a href="<?= base_url('customer_history/index/'.(int)$customer->id).'?type='.urlencode($history_type).'&page='.($history_page+1).$history_suffix; ?>">Next</a><?php endif; ?>
  </div>
</div>
