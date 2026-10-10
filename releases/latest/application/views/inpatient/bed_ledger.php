<?php $CI =& get_instance(); $esc=function($v){ return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }; ?>
<style>
.ba-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin:16px 0}.ba-card{padding:18px;background:var(--mp-surface,#fff);border-radius:12px;color:var(--mp-text,#1e2d28)}.ba-card strong{display:block;font-size:22px}.ba-muted{color:var(--mp-muted,#687a72);font-size:13px}.ba-table-wrap{overflow-x:auto;margin:16px 0}.ba-table{width:100%;border-collapse:collapse;font-size:13px}.ba-table th,.ba-table td{text-align:left;padding:10px 12px;border-bottom:1px solid var(--mp-border,#d8e2dc);vertical-align:top}.ba-links{display:flex;flex-wrap:wrap;gap:8px}.ba-links a{display:block;padding:10px 14px;border-radius:8px;background:var(--mp-surface,#fff);color:var(--mp-primary,#176753);text-decoration:none}.ba-links a.active{background:var(--mp-primary,#176753);color:#fff}
</style>
<div class="mp-page-head"><div><h2>Bed accounts</h2><p class="ba-muted">Each charge and payment stays on the account recorded when it was posted.</p></div></div>
<p class="ba-muted">New account recording: <strong><?= $recording_enabled ? 'On' : 'Off'; ?></strong>. <?= !$recording_enabled ? 'Recorded history remains available. Enable recording in Billing & ward policy.' : ''; ?></p>
<nav class="ba-links" aria-label="Payment accounts">
<?php foreach($accounts as $a): ?><a class="<?= $ledger && $ledger['account']->id==$a->id ? 'active' : ''; ?>" href="<?= base_url('inpatient/bed_ledger/'.(int)$a->id); ?>"><?= $esc($a->bed_id ? $a->ward_name.' / '.$a->bed_label : 'Outpatient'); ?> <small><?= $esc($a->account_code); ?></small></a><?php endforeach; ?>
</nav>
<?php if(!$ledger): ?><p style="margin-top:20px">Choose a bed to view its account.</p><?php else: $t=$ledger['totals']; ?>
<h3><?= $esc($ledger['account']->bed_id ? $ledger['account']->ward_name.' / '.$ledger['account']->bed_label : 'Outpatient'); ?> — <?= $esc($ledger['account']->account_code); ?></h3>
<div class="ba-grid">
<?php foreach(array('billed'=>'Service charges','settled'=>'Invoice payments credited','net_collected'=>'Net money collected','ledger_balance'=>'Charges less invoice payments') as $k=>$label): ?><div class="ba-card"><span class="ba-muted"><?= $label; ?></span><strong><?= $CI->currency($t[$k]); ?></strong></div><?php endforeach; ?>
</div>
<p class="ba-muted">After a transfer, a payment credits the new bed even when the bill includes charges from the previous bed. This account's net balance is therefore separate from the patient's outstanding invoice. Wallet settlements appear as invoice payments; wallet deposits are counted once in money collected.</p>
<h4>Patient invoices — outstanding balances</h4>
<div class="ba-table-wrap"><table class="ba-table"><thead><tr><th>Patient</th><th>Invoice</th><th>Total bill</th><th>Paid across all accounts</th><th>Pending</th></tr></thead><tbody>
<?php foreach($ledger['invoices'] as $r): ?><tr><td><?= $esc($r->customer_name); ?></td><td><a href="<?= base_url('patient_billing/view/'.(int)$r->sales_id); ?>"><?= $esc($r->sales_code); ?></a></td><td><?= $CI->currency($r->grand_total); ?></td><td><?= $CI->currency($r->paid_amount); ?></td><td><?= $CI->currency(max(0,$r->grand_total-$r->paid_amount)); ?></td></tr><?php endforeach; ?>
<?php if(!$ledger['invoices']): ?><tr><td colspan="5">No recorded invoices on this account.</td></tr><?php endif; ?>
</tbody></table></div>
<h4>Service charges</h4>
<div class="ba-table-wrap"><table class="ba-table"><thead><tr><th>Date</th><th>Patient</th><th>Invoice</th><th>Service</th><th>Charged</th></tr></thead><tbody>
<?php foreach($ledger['charges'] as $r): ?><tr><td><?= $esc($r->date); ?></td><td><?= $esc($r->customer_name); ?></td><td><?= $esc($r->sales_code); ?></td><td><?= $esc($r->description); ?></td><td><?= $CI->currency($r->amount); ?></td></tr><?php endforeach; ?>
<?php if(!$ledger['charges']): ?><tr><td colspan="5">No recorded charges.</td></tr><?php endif; ?>
</tbody></table></div>
<h4>Payments credited to this account</h4>
<div class="ba-table-wrap"><table class="ba-table"><thead><tr><th>Date</th><th>Patient</th><th>Receipt / invoice</th><th>Payment type</th><th>Amount</th></tr></thead><tbody>
<?php foreach($ledger['payments'] as $r): ?><tr><td><?= $esc($r->date); ?></td><td><?= $esc($r->customer_name); ?></td><td><?= $esc($r->payment_code.' / '.$r->sales_code); ?></td><td><?= $esc($r->payment_type); ?></td><td><?= $CI->currency($r->amount); ?></td></tr><?php endforeach; ?>
<?php if(!$ledger['payments']): ?><tr><td colspan="5">No recorded payments.</td></tr><?php endif; ?>
</tbody></table></div>
<h4>Wallet deposits and refunds</h4>
<div class="ba-table-wrap"><table class="ba-table"><thead><tr><th>Date</th><th>Patient</th><th>Type</th><th>Amount</th></tr></thead><tbody>
<?php foreach($ledger['deposits'] as $r): ?><tr><td><?= $esc($r->date); ?></td><td><?= $esc($r->customer_name); ?></td><td><?= $esc($r->txn_type); ?></td><td><?= $CI->currency($r->txn_type==='refund' ? -$r->amount : $r->amount); ?></td></tr><?php endforeach; ?>
<?php if(!$ledger['deposits']): ?><tr><td colspan="4">No recorded deposits or refunds.</td></tr><?php endif; ?>
</tbody></table></div>
<?php endif; ?>
<p class="ba-muted">Earlier transactions without a recorded payment account are excluded; they are not reassigned to the patient's current bed.</p>
<script>
if(!location.search.includes('mobile=1') && (matchMedia('(max-width:768px)').matches || matchMedia('(max-width:1024px) and (orientation:portrait)').matches)){
 var baUrl=new URL(location.href);baUrl.searchParams.set('mobile','1');location.replace(baUrl.href);
}
</script>
