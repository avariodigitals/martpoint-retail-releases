<?php
$this->load->model('legacy_history_model','legacy_history');
$history_counts=$this->legacy_history->counts($customer->id,get_current_store_id());
if($history_counts || $this->legacy_history->has_customer($customer->id,get_current_store_id())):
 $history_mobile=isset($history_mobile)&&$history_mobile;
?>
<div class="history-note" style="padding:14px;margin:12px 0;border:1px solid #c9d7e2;border-radius:10px;background:#f0f6ff;color:#294368">
 <strong>Quote &amp; Invoice History</strong><br>
 <?php foreach($history_counts as $history_count): ?>
 <a style="display:inline-block;margin:8px 12px 4px 0" href="<?= base_url('customer_history/index/'.(int)$customer->id).'?type='.urlencode($history_count['document_type']).($history_mobile?'&mobile=1':''); ?>"><?= (int)$history_count['n']; ?> historical <?= $history_count['document_type']==='quote'?'quotes':'invoices'; ?></a>
 <?php endforeach; ?>
 <a style="display:inline-block;margin:8px 0" href="<?= base_url('customer_history/index/'.(int)$customer->id).($history_mobile?'?mobile=1':''); ?>">View history &amp; current quotations</a>
 <div style="font-size:12px;margin-top:6px">Read-only Rose records · Product text preserved · Current stock and balance unaffected</div>
</div>
<?php endif; ?>
