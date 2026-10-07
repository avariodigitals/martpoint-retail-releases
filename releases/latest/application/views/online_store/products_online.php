<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-thumb{width:40px!important;height:40px!important;object-fit:cover!important;border-radius:8px!important;border:1px solid var(--mp-border)!important}
.os-thumb-ph{width:40px!important;height:40px!important;border-radius:8px!important;background:var(--mp-bg)!important;display:inline-block!important}
.os-price-input{width:110px!important;padding:7px 10px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;background:var(--mp-surface)!important}
.os-price-input:focus{outline:none!important;border-color:var(--mp-primary)!important;box-shadow:0 0 0 3px rgba(0,87,255,.1)!important}
.os-toggle{width:42px!important;height:24px!important;border-radius:12px!important;background:var(--mp-border)!important;position:relative!important;cursor:pointer!important;transition:background .15s ease!important;border:none!important;flex-shrink:0!important}
.os-toggle.on{background:var(--mp-success)!important}
.os-toggle::after{content:''!important;position:absolute!important;top:2px!important;left:2px!important;width:20px!important;height:20px!important;border-radius:50%!important;background:#fff!important;transition:transform .15s ease!important;box-shadow:0 1px 3px rgba(0,0,0,.2)!important}
.os-toggle.on::after{transform:translateX(18px)!important}
.os-search{display:flex!important;gap:8px!important;align-items:center!important}
.os-search input{padding:9px 14px!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;font-size:13px!important;min-width:240px!important;background:var(--mp-surface)!important;color:var(--mp-text)!important}
.os-search input:focus{outline:none!important;border-color:var(--mp-primary)!important;box-shadow:0 0 0 3px rgba(0,87,255,.1)!important}
.os-search button{padding:9px 16px!important;border-radius:10px!important;border:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;font-weight:600!important;cursor:pointer!important}
.os-search button:hover{background:var(--mp-bg)!important}
.os-sync-btn{padding:9px 18px!important;border-radius:10px!important;border:none!important;background:var(--mp-primary)!important;color:#fff!important;font-weight:600!important;cursor:pointer!important;font-size:13px!important;display:inline-flex!important;align-items:center!important;gap:6px!important}
.os-sync-btn:hover{background:var(--mp-primary-dark,#0044CC)!important}
.os-sync-btn:disabled{opacity:.6!important;cursor:not-allowed!important}
.os-toggle.na{background:#6366F1!important}
.os-toggle.na.on{background:#4F46E5!important}
/* Batch toolbar */
.os-batch-bar{position:sticky!important;top:0!important;z-index:50!important;background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-primary,#0057FF)!important;border-radius:12px!important;padding:10px 16px!important;margin-bottom:16px!important;display:none!important;align-items:center!important;gap:10px!important;flex-wrap:wrap!important;box-shadow:0 4px 12px rgba(0,87,255,.12)!important}
.os-batch-bar.show{display:flex!important}
.os-batch-count{font-size:14px!important;font-weight:700!important;color:var(--mp-primary,#0057FF)!important;white-space:nowrap!important}
.os-batch-actions{display:flex!important;gap:6px!important;flex-wrap:wrap!important;margin-left:auto!important}
.os-batch-btn{padding:7px 14px!important;border-radius:8px!important;border:1px solid var(--mp-border,#E2E8F0)!important;background:var(--mp-surface,#fff)!important;color:var(--mp-ink,#1E293B)!important;font-size:12px!important;font-weight:600!important;cursor:pointer!important;display:inline-flex!important;align-items:center!important;gap:5px!important;transition:all .15s!important}
.os-batch-btn:hover{background:var(--mp-bg,#F1F5F9)!important}
.os-batch-btn.green{border-color:#10B981!important;color:#065F46!important}
.os-batch-btn.green:hover{background:#D1FAE5!important}
.os-batch-btn.red{border-color:#EF4444!important;color:#991B1B!important}
.os-batch-btn.red:hover{background:#FEF2F2!important}
.os-batch-btn.indigo{border-color:#6366F1!important;color:#4338CA!important}
.os-batch-btn.indigo:hover{background:#E0E7FF!important}
.os-batch-btn.blue{border-color:var(--mp-primary,#0057FF)!important;color:var(--mp-primary,#0057FF)!important}
.os-batch-btn.blue:hover{background:#EFF6FF!important}
.os-batch-btn:disabled{opacity:.5!important;cursor:not-allowed!important}
.os-batch-clear{margin-left:4px!important;padding:7px 10px!important;border:none!important;background:transparent!important;color:var(--mp-muted,#64748B)!important;cursor:pointer!important;font-size:13px!important}
.os-batch-clear:hover{color:var(--mp-ink,#1E293B)!important}
.os-row-check{width:16px!important;height:16px!important;cursor:pointer!important}
</style>

<!-- Batch action toolbar -->
<div class="os-batch-bar" id="batchBar">
  <span class="os-batch-count"><i class="fa fa-check-square"></i> <span id="batchCount">0</span> selected</span>
  <div class="os-batch-actions">
    <button class="os-batch-btn green" onclick="batchAction('publish')"><i class="fa fa-globe"></i> Publish</button>
    <button class="os-batch-btn red" onclick="batchAction('unpublish')"><i class="fa fa-ban"></i> Unpublish</button>
    <button class="os-batch-btn indigo" onclick="batchAction('mark_new')"><i class="fa fa-star"></i> Mark New</button>
    <button class="os-batch-btn indigo" onclick="batchAction('unmark_new')"><i class="fa fa-star-o"></i> Unmark New</button>
    <button class="os-batch-btn blue" onclick="batchAction('mark_featured')"><i class="fa fa-thumbs-up"></i> Mark Featured</button>
    <button class="os-batch-btn blue" onclick="batchAction('unmark_featured')"><i class="fa fa-thumbs-o-down"></i> Unmark Featured</button>
    <button class="os-batch-btn indigo" onclick="openBulkEdit()"><i class="fa fa-edit"></i> Edit Fields</button>
  </div>
  <button class="os-batch-clear" onclick="clearSelection()"><i class="fa fa-times"></i> Clear</button>
</div>

<!-- Bulk field edit modal (preview → apply) -->
<div id="bulkEditModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.45);">
  <div style="background:var(--mp-surface,#fff);max-width:760px;width:92%;margin:4vh auto;border-radius:14px;padding:20px 22px;max-height:88vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
      <h3 style="margin:0;font-size:16px;">Bulk Edit Selected Products</h3>
      <button type="button" onclick="closeBulkEdit()" style="border:none;background:none;font-size:20px;cursor:pointer;color:var(--mp-muted,#64748B);">&times;</button>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;margin-bottom:10px;">
      <div>
        <label style="font-size:12px;font-weight:600;color:var(--mp-muted,#64748B);">Price field</label>
        <select id="be_price_field" class="form-control" onchange="beSync()">
          <option value="">— do not change —</option>
          <option value="sales_price">Sales price</option>
          <option value="price">Purchase price</option>
          <option value="online_price">Online price</option>
        </select>
      </div>
      <div>
        <label style="font-size:12px;font-weight:600;color:var(--mp-muted,#64748B);">Change</label>
        <select id="be_price_mode" class="form-control">
          <option value="set">Set to</option>
          <option value="increase_pct">Increase by %</option>
          <option value="decrease_pct">Decrease by %</option>
          <option value="increase_amt">Increase by amount</option>
          <option value="decrease_amt">Decrease by amount</option>
        </select>
      </div>
      <div>
        <label style="font-size:12px;font-weight:600;color:var(--mp-muted,#64748B);">Value</label>
        <input type="number" step="0.01" min="0" id="be_price_value" class="form-control" placeholder="e.g. 10">
      </div>
      <div>
        <label style="font-size:12px;font-weight:600;color:var(--mp-muted,#64748B);">Category</label>
        <select id="be_category_id" class="form-control">
          <option value="">— do not change —</option>
          <?php foreach($categories as $cat): ?>
          <option value="<?= (int)$cat->id; ?>"><?= htmlspecialchars($cat->category_name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label style="font-size:12px;font-weight:600;color:var(--mp-muted,#64748B);">Stock alert threshold</label>
        <input type="number" step="1" min="0" id="be_alert_qty" class="form-control" placeholder="Leave empty to keep">
      </div>
      <div>
        <label style="font-size:12px;font-weight:600;color:var(--mp-muted,#64748B);">Stock quantity</label>
        <div style="display:flex;gap:6px;">
          <select id="be_stock_mode" class="form-control" style="flex:1;">
            <option value="">— do not change —</option>
            <option value="set">Set to</option>
            <option value="increase_amt">Increase by</option>
            <option value="decrease_amt">Decrease by</option>
          </select>
          <input type="number" step="1" min="0" id="be_stock_value" class="form-control" style="flex:1;" placeholder="qty">
        </div>
      </div>
    </div>
    <div style="font-size:11px;color:var(--mp-muted,#64748B);margin-bottom:12px;">
      <i class="fa fa-info-circle"></i> Stock quantity changes are recorded through the stock-adjustment ledger. Items changed by someone else after preview are skipped and reported as conflicts.
    </div>

    <div id="be_preview_area" style="display:none;margin-bottom:12px;">
      <table class="table mp-dt-table" style="width:100%;font-size:12px;">
        <thead><tr><th>Product</th><th>Change</th></tr></thead>
        <tbody id="be_preview_rows"></tbody>
      </table>
    </div>
    <div id="be_result_area" style="display:none;margin-bottom:12px;">
      <div id="be_result_summary" style="font-size:13px;font-weight:600;margin-bottom:6px;"></div>
      <table class="table mp-dt-table" style="width:100%;font-size:12px;">
        <tbody id="be_result_rows"></tbody>
      </table>
    </div>

    <div style="display:flex;gap:8px;justify-content:flex-end;">
      <button type="button" class="os-batch-btn" onclick="bulkEditPreview()" id="be_preview_btn"><i class="fa fa-eye"></i> Preview</button>
      <button type="button" class="os-batch-btn green" onclick="bulkEditApply()" id="be_apply_btn" disabled><i class="fa fa-check"></i> Apply</button>
    </div>
  </div>
</div>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Publish products to your storefront and manage online prices, featured & new arrival items</div>
  </div>
  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
    <form method="get" class="os-search" action="<?= base_url('online_store/products_online'); ?>">
      <input type="text" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Search products...">
      <select name="category" onchange="this.form.submit()" style="padding:9px 14px!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;font-size:13px!important;background:var(--mp-surface)!important;color:var(--mp-text)!important;">
        <option value="0">All Categories</option>
        <?php foreach($categories as $cat): ?>
        <option value="<?= (int)$cat->id; ?>" <?= ($category_id == (int)$cat->id) ? 'selected' : ''; ?>><?= htmlspecialchars($cat->category_name); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit"><i class="fa fa-search"></i> Search</button>
    </form>
    <button type="button" class="os-sync-btn" onclick="syncAllOnline()" id="sync-btn">
      <i class="fa fa-refresh"></i> Sync All Products
    </button>
  </div>
</div>

<?php if($msg = $this->session->flashdata('success')): ?>
<div class="alert alert-success" style="border-radius:10px;border:1px solid var(--mp-border);background:rgba(5,150,105,.08);color:var(--mp-success);padding:12px 16px;margin-bottom:16px;">
  <i class="fa fa-check-circle"></i> <?= htmlspecialchars($msg); ?>
</div>
<?php endif; ?>
<?php if($msg = $this->session->flashdata('error')): ?>
<div class="alert alert-danger" style="border-radius:10px;border:1px solid var(--mp-border);background:rgba(220,38,38,.08);color:var(--mp-danger);padding:12px 16px;margin-bottom:16px;">
  <i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars($msg); ?>
</div>
<?php endif; ?>

<div class="mp-table-wrap">
  <div class="mp-card-head">
    <h3>Products Online Status</h3>
    <span style="font-size:12px;color:var(--mp-muted,#64748B);">Select products below to batch-update, or toggle individually</span>
  </div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="os-products-table" class="table mp-dt-table" width="100%">
        <thead>
          <tr>
            <th style="width:30px;"><input type="checkbox" id="selectAll" class="os-row-check" title="Select all"></th>
            <th>Image</th><th>Product</th><th>Category</th><th>Stock</th><th>Sales Price</th><th>Online Price</th><th>Online</th><th>New Arrival</th><th>Featured</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($products as $p): ?>
          <tr data-id="<?= (int)$p->id; ?>">
            <td><input type="checkbox" class="os-row-check os-row-select" value="<?= (int)$p->id; ?>"></td>
            <td>
              <?php if($p->item_image && file_exists($p->item_image)): ?>
                <img src="<?= base_url($p->item_image); ?>" class="os-thumb" alt="">
              <?php else: ?><span class="os-thumb-ph"></span><?php endif; ?>
            </td>
            <td class="row-name"><?= htmlspecialchars($p->item_name); ?></td>
            <td><?= htmlspecialchars($p->category_name ?: '-'); ?></td>
            <td><?= (int)$p->stock; ?></td>
            <td class="amt"><?= store_number_format($p->sales_price); ?></td>
            <td>
              <input type="number" class="os-price-input" value="<?= $p->online_price > 0 ? $p->online_price : ''; ?>" placeholder="Same" onchange="updateOnlinePrice(<?= (int)$p->id; ?>, this.value)">
            </td>
            <td>
              <button type="button" class="os-toggle <?= $p->publish_online ? 'on' : ''; ?>" data-id="<?= (int)$p->id; ?>" onclick="toggleOnline(this)" title="Toggle online publication"></button>
            </td>
            <td>
              <button type="button" class="os-toggle na <?= $p->is_new_arrival ? 'on' : ''; ?>" data-id="<?= (int)$p->id; ?>" onclick="toggleNewArrival(this)" title="Toggle New Arrival"></button>
            </td>
            <td>
              <button type="button" class="os-toggle <?= $p->is_featured ? 'on' : ''; ?>" data-id="<?= (int)$p->id; ?>" onclick="toggleFeatured(this)" title="Toggle Featured" style="background:#F59E0B!important"></button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($products)): ?><tr><td colspan="10" class="mp-empty-state">No products found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

function toggleOnline(btn){
  var id = $(btn).data('id');
  $.post('<?=base_url("online_store/toggle_product_online");?>', {
    product_id: id,
    [csrfName]: csrfHash
  }, function(res){
    if(res.status === 'success'){
      $(btn).toggleClass('on', res.publish_online == 1);
      toastr.success('Product ' + (res.publish_online ? 'published' : 'unpublished') + ' online');
    } else { toastr.error(res.message); }
  }, 'json');
}

function toggleNewArrival(btn){
  var id = $(btn).data('id');
  $.post('<?=base_url("online_store/toggle_new_arrival");?>', {
    product_id: id,
    [csrfName]: csrfHash
  }, function(res){
    if(res.status === 'success'){
      $(btn).toggleClass('on', res.is_new_arrival == 1);
      toastr.success(res.is_new_arrival ? 'Marked as New Arrival' : 'Removed from New Arrivals');
    } else { toastr.error(res.message); }
  }, 'json');
}

function toggleFeatured(btn){
  var id = $(btn).data('id');
  $.post('<?=base_url("online_store/toggle_featured");?>', {
    product_id: id,
    [csrfName]: csrfHash
  }, function(res){
    if(res.status === 'success'){
      $(btn).toggleClass('on', res.is_featured == 1);
      toastr.success(res.is_featured ? 'Marked as Featured' : 'Removed from Featured');
    } else { toastr.error(res.message); }
  }, 'json');
}

function updateOnlinePrice(id, price){
  $.post('<?=base_url("online_store/update_online_price");?>', {
    product_id: id, online_price: price,
    [csrfName]: csrfHash
  }, function(res){
    if(res.status === 'success') toastr.success('Price updated');
    else toastr.error(res.message);
  }, 'json');
}

function syncAllOnline(){
  var btn = document.getElementById('sync-btn');
  if(!confirm('This will publish ALL eligible offline products to your online store (respecting your plan quota). Continue?')) return;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Syncing...';
  $.post('<?=base_url("online_store/sync_all_online");?>', {
    category_id: <?= (int)($category_id ?? 0); ?>,
    [csrfName]: csrfHash
  }, function(res){
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-refresh"></i> Sync All Products';
    if(res.status === 'success'){
      toastr.success(res.message);
      setTimeout(function(){ window.location.reload(); }, 1500);
    } else { toastr.error(res.message); }
  }, 'json').fail(function(){
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-refresh"></i> Sync All Products';
    toastr.error('Sync failed. Please try again.');
  });
}

// === Batch selection ===
function getSelectedIds(){
  return $('.os-row-select:checked').map(function(){ return $(this).val(); }).get();
}
function updateBatchBar(){
  var ids = getSelectedIds();
  var count = ids.length;
  $('#batchCount').text(count);
  $('#batchBar').toggleClass('show', count > 0);
}
function clearSelection(){
  $('.os-row-select').prop('checked', false);
  $('#selectAll').prop('checked', false);
  updateBatchBar();
}
function batchAction(action){
  var ids = getSelectedIds();
  if(ids.length === 0){ toastr.warning('No products selected'); return; }
  var labels = {
    'publish':'Publish '+ids.length+' products online?',
    'unpublish':'Unpublish '+ids.length+' products? (They will be excluded from sync)',
    'mark_new':'Mark '+ids.length+' products as New Arrival?',
    'unmark_new':'Remove '+ids.length+' products from New Arrivals?',
    'mark_featured':'Mark '+ids.length+' products as Featured?',
    'unmark_featured':'Remove '+ids.length+' products from Featured?'
  };
  if(!confirm(labels[action] || 'Apply '+action+' to '+ids.length+' products?')) return;
  $.post('<?=base_url("online_store/batch_update");?>', {
    product_ids: ids,
    action: action,
    [csrfName]: csrfHash
  }, function(res){
    if(res.status === 'success'){
      toastr.success(res.message);
      clearSelection();
      setTimeout(function(){ window.location.reload(); }, 1200);
    } else {
      toastr.error(res.message);
    }
  }, 'json').fail(function(){
    toastr.error('Batch update failed. Please try again.');
  });
}

// === Bulk field edit (preview → apply) ===
var beHashes = {};
function openBulkEdit(){
  if(getSelectedIds().length === 0){ toastr.warning('No products selected'); return; }
  beHashes = {};
  $('#be_preview_area,#be_result_area').hide();
  $('#be_preview_rows,#be_result_rows').empty();
  $('#be_apply_btn').prop('disabled', true);
  $('#bulkEditModal').show();
}
function closeBulkEdit(){ $('#bulkEditModal').hide(); }
function beSync(){
  var on = $('#be_price_field').val() !== '';
  $('#be_price_mode,#be_price_value').prop('disabled', !on);
}
function beSpec(){
  var d = {
    price_field: $('#be_price_field').val(),
    price_mode:  $('#be_price_mode').val(),
    price_value: $('#be_price_value').val(),
    category_id: $('#be_category_id').val(),
    alert_qty:   $('#be_alert_qty').val(),
    stock_mode:  $('#be_stock_mode').val(),
    stock_value: $('#be_stock_value').val()
  };
  d[csrfName] = csrfHash;
  return d;
}
function beDescribe(changes){
  var labels = {sales_price:'Sales price',price:'Purchase price',online_price:'Online price',category_id:'Category',alert_qty:'Alert qty'};
  var parts = [];
  $.each(changes, function(k,v){
    if(k === '_stock_error'){ parts.push('<span style="color:#991B1B;">Stock: '+v+'</span>'); return; }
    if(k === '_stock_delta'){ parts.push('Stock: '+v.from+' → '+v.to); return; }
    parts.push((labels[k]||k)+': '+v.from+' → '+v.to);
  });
  return parts.length ? parts.join('<br>') : '<span style="color:#64748B;">no change</span>';
}
function bulkEditPreview(){
  var ids = getSelectedIds();
  var btn = $('#be_preview_btn').prop('disabled',true);
  $('#be_result_area').hide(); $('#be_result_rows').empty();
  $.post('<?=base_url("online_store/batch_edit_preview");?>', $.extend({product_ids:ids}, beSpec()), function(res){
    btn.prop('disabled',false);
    if(res.csrf_hash){ csrfHash = res.csrf_hash; }
    if(res.status !== 'success'){ toastr.error(res.message); return; }
    beHashes = {};
    var rows = '';
    $.each(res.items, function(i,it){
      if(it.hash){ beHashes[it.id] = it.hash; }
      rows += '<tr><td>'+ $('<div>').text(it.name).html() +'</td><td>'+ beDescribe(it.changes) +'</td></tr>';
    });
    $('#be_preview_rows').html(rows);
    $('#be_preview_area').show();
    $('#be_apply_btn').prop('disabled', false);
  }, 'json').fail(function(){ btn.prop('disabled',false); toastr.error('Preview failed. Please try again.'); });
}
function bulkEditApply(){
  var ids = getSelectedIds();
  var btn = $('#be_apply_btn').prop('disabled',true);
  var post = $.extend({product_ids:ids, expect:beHashes}, beSpec());
  $.post('<?=base_url("online_store/batch_edit_apply");?>', post, function(res){
    btn.prop('disabled',false);
    if(res.csrf_hash){ csrfHash = res.csrf_hash; }
    if(res.status !== 'success'){ toastr.error(res.message); return; }
    var rows = '';
    var colors = {ok:'#065F46',conflict:'#92400E',skipped:'#64748B',error:'#991B1B'};
    $.each(res.results, function(i,r){
      rows += '<tr><td>'+ $('<div>').text(r.name).html() +'</td><td style="color:'+(colors[r.status]||'#333')+';">'+r.status+'</td><td>'+ $('<div>').text(r.detail).html() +'</td></tr>';
    });
    $('#be_result_rows').html(rows);
    $('#be_result_summary').text(res.ok+' updated, '+res.conflicts+' conflict'+(res.conflicts==1?'':'s')+', '+res.skipped+' skipped, '+res.errors+' error'+(res.errors==1?'':'s'));
    $('#be_preview_area').hide();
    $('#be_result_area').show();
    if(res.ok > 0){ setTimeout(function(){ window.location.reload(); }, 2500); }
  }, 'json').fail(function(){ btn.prop('disabled',false); toastr.error('Apply failed. Please try again.'); });
}
beSync();

$(document).ready(function(){
  var dt = $('#os-products-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[2,"asc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [0,1,6,7,8,9], "orderable": false }]
  });

  // Select all (visible rows only)
  $('#selectAll').on('click', function(){
    var checked = this.checked;
    $('.os-row-select').each(function(){
      // Only check visible rows (respecting DataTables filter)
      if($(this).closest('tr').is(':visible')){
        this.checked = checked;
      }
    });
    updateBatchBar();
  });

  // Individual checkbox
  $('.os-row-select').on('change', updateBatchBar);

  // Update select-all state when individual checkboxes change
  $(document).on('change', '.os-row-select', function(){
    var allVisible = $('.os-row-select').filter(function(){
      return $(this).closest('tr').is(':visible');
    });
    var allChecked = allVisible.length > 0 && allVisible.filter(':checked').length === allVisible.length;
    $('#selectAll').prop('checked', allChecked);
  });

  // Re-sync checkboxes when DataTable redraws (pagination/search)
  dt.on('draw', function(){
    updateBatchBar();
  });
});
</script>
<script>$(".online_store-products-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
