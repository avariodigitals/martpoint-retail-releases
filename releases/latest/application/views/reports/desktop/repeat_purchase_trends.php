<?php $this->load->view('reports/desktop/_styles'); ?>
<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2><div class="mp-page-sub">Monthly returning-customer activity from completed, customer-linked sales. Walk-in sales are excluded.</div></div>
  <div class="mp-report-actions"><?php $this->load->view('components/export_btn', ['tableId' => 'repeat-purchase-data']); ?></div>
</div>
<form id="repeat-purchase-form" class="mp-report-filter">
  <div class="mp-card-head"><h3>Report period</h3></div>
  <div class="mp-card-body"><div class="mp-form-grid">
    <div class="mp-form-group"><label for="from_date">From</label><input type="date" id="from_date" name="from_date" value="<?= date('Y-m-01', strtotime('-11 months')); ?>" required></div>
    <div class="mp-form-group"><label for="to_date">To</label><input type="date" id="to_date" name="to_date" value="<?= date('Y-m-d'); ?>" required></div>
  </div><div class="mp-report-filter-actions" style="margin-top:18px"><button class="mp-btn-primary" type="submit"><i class="fa fa-line-chart"></i> View trends</button></div></div>
</form>
<div class="mp-card-form"><div class="mp-card-head"><h3>Repeat-purchase trend</h3></div><div class="mp-card-body" style="overflow-x:auto"><table class="table table-bordered table-hover" id="repeat-purchase-data" style="width:100%"><thead><tr><th>#</th><th>Month</th><th>Active customers</th><th>Returning customers</th><th>Repeat-customer rate</th><th>Repeat orders</th><th>Repeat-order revenue</th><th>Completed orders</th></tr></thead><tbody id="repeat-purchase-results"><tr><td colspan="8">Choose a period and select View trends.</td></tr></tbody></table></div></div>
<script>
(function(){
  var form=document.getElementById('repeat-purchase-form'), body=document.getElementById('repeat-purchase-results');
  form.addEventListener('submit',function(e){e.preventDefault();body.innerHTML='<tr><td colspan="8">Loading…</td></tr>';var data=new FormData(form);data.append('<?= $this->security->get_csrf_token_name(); ?>','<?= $this->security->get_csrf_hash(); ?>');fetch('<?= base_url('reports/show_repeat_purchase_trends'); ?>',{method:'POST',body:data,credentials:'same-origin'}).then(function(r){if(!r.ok)throw new Error();return r.text()}).then(function(html){body.innerHTML=html}).catch(function(){body.innerHTML='<tr><td colspan="8">Could not load this report. Please try again.</td></tr>'});});
})();
</script>
