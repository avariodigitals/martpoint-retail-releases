<?php $this->load->view('reports/desktop/_styles'); $CI =& get_instance(); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></h2>
    <div class="mp-page-sub">Compare sales charged at customer group prices with retail sales. Group names and pricing type come from the saved sale-line snapshot; older sales without a snapshot are shown as retail rather than inferred from today’s customer assignment.</div>
  </div>
  <div class="mp-report-actions">
    <a class="mp-btn-secondary" id="export-csv" href="#"><i class="fa fa-file-text-o"></i> CSV</a>
    <a class="mp-btn-secondary" id="export-excel" href="#"><i class="fa fa-file-excel-o"></i> Excel</a>
    <a class="mp-btn-secondary" id="export-pdf" href="#"><i class="fa fa-file-pdf-o"></i> PDF</a>
  </div>
</div>

<?php if(empty($customer_pricing_ready)): ?>
  <div class="alert alert-warning">Customer group pricing is not installed yet. Apply migration 4.0.9.239 to enable group filters and member sales classification.</div>
<?php endif; ?>

<form id="customer-group-sales-filters" class="mp-report-filter">
  <div class="mp-card-head"><h3>Report filters</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid">
      <div class="mp-form-group"><label for="from_date">From date</label><input type="date" class="form-control" id="from_date" name="from_date" value="<?= date('Y-m-01'); ?>"></div>
      <div class="mp-form-group"><label for="to_date">To date</label><input type="date" class="form-control" id="to_date" name="to_date" value="<?= date('Y-m-d'); ?>"></div>
      <div class="mp-form-group"><label for="sale_type">Sale price type</label><select class="form-control" id="sale_type" name="sale_type"><option value="all">All price types</option><option value="member">Member sales only</option><option value="retail">Retail sales only</option><option value="other">Individual adjustments</option></select></div>
      <div class="mp-form-group"><label for="group_id">Customer group</label><select class="form-control" id="group_id" name="group_id"><option value="">All groups</option><?php foreach((array)$customer_pricing_groups as $group): ?><option value="<?= (int)$group->id; ?>"><?= htmlspecialchars($group->name . (empty($group->active) ? ' (inactive)' : ''), ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
      <div class="mp-form-group"><label for="warehouse_id">Branch / location</label><select class="form-control" id="warehouse_id" name="warehouse_id"><option value="">All locations</option><?php foreach((array)$sales_report_warehouses as $warehouse): ?><option value="<?= (int)$warehouse->id; ?>"><?= htmlspecialchars($warehouse->warehouse_name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
      <div class="mp-form-group"><label for="report-search">Search</label><input type="search" class="form-control" id="report-search" placeholder="Invoice, customer, item, or group"></div>
    </div>
    <div class="mp-report-filter-actions" style="margin-top:18px;display:flex;gap:10px;">
      <button type="submit" class="mp-btn-primary"><i class="fa fa-filter"></i> Apply filters</button>
      <button type="button" class="mp-btn-secondary" id="reset-filters">Reset</button>
    </div>
  </div>
</form>

<div class="row" style="margin:16px -8px;">
  <div class="col-sm-3" style="padding:8px;"><div class="mp-card-body" style="background:#eef6ff;border-radius:8px;"><small>Member group sales</small><h3 id="member-sales-total">—</h3></div></div>
  <div class="col-sm-3" style="padding:8px;"><div class="mp-card-body" style="background:#fff7ed;border-radius:8px;"><small>Individual adjustments</small><h3 id="individual-sales-total">—</h3></div></div>
  <div class="col-sm-3" style="padding:8px;"><div class="mp-card-body" style="background:#f3f7f4;border-radius:8px;"><small>Retail sales</small><h3 id="retail-sales-total">—</h3></div></div>
  <div class="col-sm-3" style="padding:8px;"><div class="mp-card-body" style="background:#f5f3ff;border-radius:8px;"><small>Total sales in result</small><h3 id="all-sales-total">—</h3></div></div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Sales lines <small id="result-count"></small></h3></div>
  <div class="mp-card-body" style="overflow-x:auto;">
    <table class="table table-bordered table-hover" style="width:100%;min-width:1050px;">
      <thead><tr class="bg-blue"><th>Date</th><th>Invoice</th><th>Customer</th><th>Item</th><th>Qty</th><th>Unit price</th><th>Line total</th><th>Price type</th><th>Customer group</th><th>Branch</th><th>Cashier</th></tr></thead>
      <tbody id="customer-group-sales-rows"><tr><td colspan="11" class="text-center">Loading sales…</td></tr></tbody>
    </table>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:12px;">
      <label>Rows per page <select id="page-size" class="form-control" style="display:inline-block;width:auto;margin-left:6px;"><option>25</option><option>50</option><option>100</option></select></label>
      <div style="display:flex;align-items:center;gap:10px;"><span id="page-status"></span><button type="button" class="mp-btn-secondary" id="page-prev">Previous</button><button type="button" class="mp-btn-secondary" id="page-next">Next</button></div>
    </div>
  </div>
</div>

<script>
(function(){
  var form=document.getElementById('customer-group-sales-filters'), body=document.getElementById('customer-group-sales-rows');
  var page=0, size=25, search='', total=0, busy=false;
  var endpoint='<?= base_url('reports/customer_group_sales_data'); ?>';
  var exportBase='<?= base_url('reports/customer_group_sales_export'); ?>';
  var csrfName='<?= $this->security->get_csrf_token_name(); ?>', csrfHash='<?= $this->security->get_csrf_hash(); ?>';
  var currency='<?= htmlspecialchars($CI->currency(), ENT_QUOTES, 'UTF-8'); ?>';
  function esc(value){return String(value==null?'':value).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];});}
  function money(value){return currency+' '+Number(value||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});}
  function params(){var p=new URLSearchParams(new FormData(form));p.set('search',search);return p;}
  function updateExports(){var p=params();['csv','excel','pdf'].forEach(function(type){document.getElementById('export-'+type).href=exportBase+'/'+type+'?'+p.toString();});}
  async function load(){
    if(busy)return;busy=true;body.innerHTML='<tr><td colspan="11" class="text-center">Loading…</td></tr>';
    var p=params();p.set('start',String(page*size));p.set('length',String(size));p.set('draw',String(page+1));p.set(csrfName,csrfHash);
    try{
      var response=await fetch(endpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:p.toString()});
      var data=await response.json();if(data.csrf_hash)csrfHash=data.csrf_hash;if(!response.ok||!Array.isArray(data.data))throw new Error('Could not load this report.');
      total=Number(data.recordsFiltered||0);
      document.getElementById('member-sales-total').textContent=money(data.summary.member_sales);
      document.getElementById('individual-sales-total').textContent=money(data.summary.individual_sales);
      document.getElementById('retail-sales-total').textContent=money(data.summary.retail_sales);
      document.getElementById('all-sales-total').textContent=money(data.summary.total_sales);
      document.getElementById('result-count').textContent='('+Number(data.summary.invoices||0).toLocaleString()+' invoices · '+total.toLocaleString()+' lines)';
      body.innerHTML=data.data.length?data.data.map(function(r){var member=r.pricing_source==='group',individual=r.pricing_source==='customer';var type=member?'Member':(individual?'Individual':'Retail');var invoice=<?= json_encode(base_url('sales/invoice/')); ?>+encodeURIComponent(r.sale_id);return '<tr><td>'+esc(r.sales_date)+'</td><td><a href="'+invoice+'" target="_blank" rel="noopener">'+esc(r.sales_code)+'</a></td><td>'+esc(r.customer_name||'Walk-in')+'</td><td>'+esc(r.item_name)+'</td><td class="text-right">'+esc(r.qty)+'</td><td class="text-right">'+money(r.unit_price)+'</td><td class="text-right">'+money(r.line_total)+'</td><td><span class="label '+(member?'label-primary':(individual?'label-warning':'label-default'))+'">'+type+'</span></td><td>'+esc(member?(r.pricing_group_name||'Member group'):'—')+'</td><td>'+esc(r.warehouse_name||'')+'</td><td>'+esc(r.created_by||'')+'</td></tr>';}).join(''):'<tr><td colspan="11" class="text-center">No sales match these filters.</td></tr>';
      var pages=Math.max(1,Math.ceil(total/size));document.getElementById('page-status').textContent='Page '+(page+1)+' of '+pages;document.getElementById('page-prev').disabled=page<=0;document.getElementById('page-next').disabled=(page+1)*size>=total;
      updateExports();
    }catch(e){body.innerHTML='<tr><td colspan="11" class="text-center text-danger">'+esc(e.message||'Could not load this report.')+'</td></tr>';}
    finally{busy=false;}
  }
  form.addEventListener('submit',function(e){e.preventDefault();page=0;load();});
  document.getElementById('page-size').addEventListener('change',function(){size=Math.max(1,Math.min(200,Number(this.value)||25));page=0;load();});
  document.getElementById('page-prev').addEventListener('click',function(){if(page>0){page--;load();}});
  document.getElementById('page-next').addEventListener('click',function(){if((page+1)*size<total){page++;load();}});
  document.getElementById('reset-filters').addEventListener('click',function(){form.reset();document.getElementById('report-search').value='';search='';page=0;load();});
  var timer;document.getElementById('report-search').addEventListener('input',function(){clearTimeout(timer);search=this.value.trim();timer=setTimeout(function(){page=0;load();},350);});
  updateExports();load();
})();
</script>
