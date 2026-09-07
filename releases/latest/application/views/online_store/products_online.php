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
  </div>
  <button class="os-batch-clear" onclick="clearSelection()"><i class="fa fa-times"></i> Clear</button>
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
