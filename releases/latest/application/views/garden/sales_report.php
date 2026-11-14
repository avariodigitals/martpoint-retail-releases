<?php $r=$sales_report;$t=$r['totals'];$p=$r['payments'];$esc=function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}; ?>
<section class="garden-panel garden-sales-report">
  <div class="garden-panel-heading"><div><span class="garden-kicker">BUSINESS PERFORMANCE</span><h2>Report period</h2></div></div>
  <p class="garden-muted">All customers are included automatically. Choose a period and branch to see invoices, collections and balances.</p>
  <form method="get" action="<?= base_url('garden/sales_report'); ?>" class="garden-report-filters">
    <?php if($this->input->get('screen')==='mobile'): ?><input type="hidden" name="screen" value="mobile"><?php endif; ?>
    <label for="report-from">From date<input id="report-from" type="date" name="date_from" value="<?= $esc($report_from); ?>" required></label>
    <label for="report-to">To date<input id="report-to" type="date" name="date_to" value="<?= $esc($report_to); ?>" required></label>
    <label for="report-branch">Branch<select id="report-branch" name="branch_id" data-garden-select><option value="">All available branches</option><?php foreach($report_branches as $branch): ?><option value="<?= (int)$branch->id; ?>" <?= $report_branch===(string)$branch->id?'selected':''; ?>><?= $esc($branch->warehouse_name); ?></option><?php endforeach; ?></select></label>
    <button type="submit" class="garden-button">Generate report</button>
  </form>
  <nav class="garden-actions" aria-label="Report periods"><?php foreach(['Today'=>[date('Y-m-d'),date('Y-m-d')],'This month'=>[date('Y-m-01'),date('Y-m-d')],'Last month'=>[date('Y-m-01',strtotime('first day of last month')),date('Y-m-t',strtotime('first day of last month'))]] as $label=>$dates): $params=['date_from'=>$dates[0],'date_to'=>$dates[1],'branch_id'=>$report_branch];if($this->input->get('screen')==='mobile')$params['screen']='mobile'; ?><a class="garden-button garden-button-secondary" href="<?= $esc(base_url('garden/sales_report').'?'.http_build_query($params)); ?>"><?= $label; ?></a><?php endforeach; ?></nav>
</section>
<div class="garden-grid garden-report-kpis">
  <?php foreach([['Invoiced in period',$t->invoiced,((int)$t->invoice_count).' final invoices'],['Collected in period',$p->collected,'Receipts dated within this period'],['Paid against these invoices',$t->paid,'Current paid amount, including later payments'],['Outstanding on these invoices',$t->outstanding,'Current balance on invoices in this period']] as $card): ?>
  <div class="garden-card"><span class="garden-muted"><?= $esc($card[0]); ?></span><strong><?= mp_format_money($card[1]); ?></strong><small class="garden-muted"><?= $esc($card[2]); ?></small></div>
  <?php endforeach; ?>
</div>
<section class="garden-panel garden-sales-report"><div class="garden-panel-heading"><h2>Invoices</h2><button type="button" class="garden-button garden-button-secondary" data-report-csv="garden-invoices">Export CSV</button></div><p class="garden-muted">Final invoices dated <?= $esc(show_date($report_from)); ?> – <?= $esc(show_date($report_to)); ?>. Paid amounts and balances reflect the current ledger; returns remain in the sales return report.</p>
<div class="garden-table-wrap" data-server-paged><table id="garden-invoices"><thead><tr><th>Date</th><th>Invoice</th><th>Customer</th><th>Branch</th><th>Invoiced</th><th>Paid to date</th><th>Outstanding</th></tr></thead><tbody>
<?php foreach($r['invoices'] as $row): ?><tr><td data-label="Date"><?= $esc(show_date($row->sales_date)); ?></td><td class="garden-cell-title"><a href="<?= base_url('sales/invoice/'.(int)$row->id); ?>"><?= $esc($row->sales_code); ?></a></td><td data-label="Customer"><?= $esc($row->customer_name ?: 'Walk-in customer'); ?></td><td data-label="Branch"><?= $esc($row->warehouse_name ?: 'Branch unavailable'); ?></td><td data-label="Invoiced"><?= mp_format_money($row->grand_total); ?></td><td data-label="Paid to date"><?= mp_format_money($row->paid_amount); ?></td><td data-label="Outstanding"><?= mp_format_money(max(0,$row->grand_total-$row->paid_amount)); ?></td></tr><?php endforeach; ?>
<?php if(!$r['invoices']): ?><tr><td colspan="7" class="garden-empty">No final invoices in this period.</td></tr><?php endif; ?>
</tbody></table></div>
<?php if($r['invoice_pages']>1): $pager_params=['date_from'=>$report_from,'date_to'=>$report_to,'branch_id'=>$report_branch,'screen'=>$this->input->get('screen')]; ?>
<nav class="garden-report-pager" aria-label="Invoice pages">
  <?php if($r['invoice_page']>1): ?><a class="garden-button garden-button-secondary" href="<?= $esc(base_url('garden/sales_report').'?'.http_build_query(array_filter($pager_params + ['invoice_page'=>$r['invoice_page']-1],function($v){return $v!==null && $v!=='';}))); ?>">Previous</a><?php endif; ?>
  <span class="garden-muted" role="status">Page <?= (int)$r['invoice_page']; ?> of <?= (int)$r['invoice_pages']; ?> · <?= (int)$t->invoice_count; ?> invoices</span>
  <?php if($r['invoice_page']<$r['invoice_pages']): ?><a class="garden-button garden-button-secondary" href="<?= $esc(base_url('garden/sales_report').'?'.http_build_query(array_filter($pager_params + ['invoice_page'=>$r['invoice_page']+1],function($v){return $v!==null && $v!=='';}))); ?>">Next</a><?php endif; ?>
</nav>
<?php endif; ?></section>
<section class="garden-panel garden-sales-report"><div class="garden-panel-heading"><h2>Collections</h2><button type="button" class="garden-button garden-button-secondary" data-report-csv="garden-collections">Export CSV</button></div><p class="garden-muted">Payments received in this period, including payments for invoices raised earlier. These totals can differ from payments against the period’s invoices.</p>
<div class="garden-table-wrap"><table id="garden-collections"><thead><tr><th>Received</th><th>Invoice</th><th>Customer</th><th>Branch</th><th>Payment method</th><th>Amount</th></tr></thead><tbody>
<?php foreach($r['receipts'] as $row): ?><tr><td data-label="Received"><?= $esc(show_date($row->payment_date)); ?></td><td class="garden-cell-title"><a href="<?= base_url('sales/invoice/'.(int)$row->sales_id); ?>"><?= $esc($row->sales_code); ?></a></td><td data-label="Customer"><?= $esc($row->customer_name ?: 'Walk-in customer'); ?></td><td data-label="Branch"><?= $esc($row->warehouse_name ?: 'Branch unavailable'); ?></td><td data-label="Payment method"><?= $esc($row->payment_type ?: 'Unspecified'); ?></td><td data-label="Amount"><?= mp_format_money($row->payment); ?></td></tr><?php endforeach; ?>
<?php if(!$r['receipts']): ?><tr><td colspan="6" class="garden-empty">No collections in this period.</td></tr><?php endif; ?>
</tbody></table></div><?php if($p->receipt_count>250): ?><p class="garden-muted">Showing the latest 250 of <?= (int)$p->receipt_count; ?> receipts. Summary cards include every matching receipt. Choose a shorter period for a complete table export.</p><?php endif; ?></section>
<style>
.garden-report-filters{display:grid;grid-template-columns:1fr 1fr 1.25fr auto;align-items:end;gap:16px;margin:20px 0}.garden-report-filters label{margin:0}.garden-report-filters input,.garden-report-filters .garden-select>button{width:100%;min-height:46px;margin-top:7px;box-sizing:border-box}.garden-report-filters>button{min-height:46px}.garden-report-kpis{margin-bottom:20px}.garden-report-kpis .garden-card{margin:0;min-width:0}.garden-report-kpis strong{display:block;font-size:25px;margin:8px 0;overflow-wrap:anywhere}.garden-sales-report+.garden-sales-report{margin-top:20px}.garden-sales-report table{width:100%;border-collapse:collapse;font-size:14px}.garden-sales-report td,.garden-sales-report th{padding:14px 12px;text-align:left;border-bottom:1px solid #dce5df}.garden-sales-report th{background:#f6f9f3;font-size:12px;color:#52634e}.garden-sales-report .garden-table-wrap{overflow-x:auto}.garden-sales-report .garden-table-wrap td{white-space:nowrap}.garden-sales-report .garden-panel-heading{flex-wrap:wrap}.garden-sales-report .garden-actions{margin:0}.garden-report-filters .garden-select-options{margin-top:4px}.garden-report-pager{display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;margin:16px 0 4px}.garden-report-pager .garden-button{min-height:44px}
@media(max-width:1100px){.garden-report-filters{grid-template-columns:1fr 1fr}.garden-report-kpis{grid-template-columns:1fr 1fr}}
.garden-mobile .garden-report-filters{grid-template-columns:1fr}.garden-mobile .garden-report-kpis{grid-template-columns:1fr}.garden-mobile .garden-report-filters input{font-size:16px}
</style>

<script>
(function(){document.querySelectorAll('[data-report-csv]').forEach(function(button){button.addEventListener('click',function(){
  var table=document.getElementById(button.dataset.reportCsv);if(!table)return;
  var rows=Array.from(table.rows).map(function(row){return Array.from(row.cells).map(function(cell){var value=cell.textContent.trim();if(/^[=+\-@\t\r]/.test(value))value="'"+value;return '"'+value.replace(/"/g,'""')+'"';}).join(',');});
  var blob=new Blob(['\uFEFF'+rows.join('\r\n')],{type:'text/csv;charset=utf-8;'}),url=URL.createObjectURL(blob),link=document.createElement('a');
  link.href=url;link.download=button.dataset.reportCsv+'-<?= $esc($report_from); ?>-<?= $esc($report_to); ?>.csv';document.body.appendChild(link);link.click();link.remove();setTimeout(function(){URL.revokeObjectURL(url);},1000);
});});})();
</script>
