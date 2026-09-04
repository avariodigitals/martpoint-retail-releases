<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
?>

<style>
.mp-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 16px; box-shadow: var(--mp-shadow-sm); overflow: hidden; margin-bottom: 24px; }
.mp-card .mp-card-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 14px; border-bottom: 1px solid var(--mp-border); }
.mp-card .mp-card-head h3 { font-size: 15px; font-weight: 700; margin: 0; color: var(--mp-text); }
.mp-card .mp-card-body { padding: 20px; }
.mp-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
.mp-tbl th { text-align: left; font-size: 11px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: .04em; padding: 10px 16px; border-bottom: 1px solid var(--mp-border); }
.mp-tbl td { padding: 12px 16px; color: var(--mp-ink); border-bottom: 1px solid var(--mp-border); }
.mp-tbl tr:last-child td { border-bottom: none; }
.mp-tbl tr:hover td { background: var(--mp-bg); }
.mp-empty-state { text-align: center; padding: 30px 16px; color: var(--mp-muted); font-size: 13px; }
.mp-empty-state.error { color: var(--mp-danger); }

.pc-filter-bar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 16px; }
.pc-filter-bar select { min-width: 180px; }
.pc-filter-bar input { min-width: 240px; flex: 1; }

.price-edit-input { width: 100px; padding: 6px 10px; border: 1px solid var(--mp-primary); border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--mp-ink); background: var(--mp-surface); }
.price-edit-input:focus { outline: none; box-shadow: 0 0 0 3px rgba(0,87,255,.15); }
.price-display { cursor: pointer; font-weight: 700; color: var(--mp-primary); }
.price-display:hover { text-decoration: underline; }

.pc-service-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
.pc-service-row:last-child { border-bottom: none; }
.pc-service-name { font-size: 14px; font-weight: 600; color: var(--mp-ink); }
.pc-service-meta { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
.pc-service-price { font-size: 14px; font-weight: 700; color: var(--mp-primary); font-variant-numeric: tabular-nums; }

.mp-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; }
.mp-pill.warn { background: rgba(245,158,11,.1); color: var(--mp-warning); }
.mp-pill.ok { background: rgba(5,150,105,.1); color: var(--mp-success); }

@media (max-width:767px){
  .pc-filter-bar { flex-direction: column; align-items: stretch; }
  .pc-filter-bar select, .pc-filter-bar input { min-width: 0; width: 100%; }
}
</style>

<div class="mp-section">
  <?php include "comman/code_flashdata.php"; ?>
</div>

<!-- Page Header -->
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; View and update prices across your catalogue</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <button class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);" onclick="exportCSV()">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Export CSV
    </button>
    <button class="mp-qa-btn blue" onclick="window.print()">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      Print
    </button>
  </div>
</div>

<!-- KPI Cards -->
<div class="mp-kpi-grid">
  <div class="mp-kpi-card summary">
    <div class="mp-kpi-icon"><i class="fa fa-tags"></i></div>
    <div class="mp-kpi-label">Total Items</div>
    <div class="mp-kpi-value" id="stat-total">-</div>
  </div>
  <div class="mp-kpi-card profit">
    <div class="mp-kpi-icon"><i class="fa fa-check-circle"></i></div>
    <div class="mp-kpi-label">Active Items</div>
    <div class="mp-kpi-value" id="stat-active">-</div>
  </div>
  <div class="mp-kpi-card stock">
    <div class="mp-kpi-icon"><i class="fa fa-percent"></i></div>
    <div class="mp-kpi-label">Discounted</div>
    <div class="mp-kpi-value" id="stat-discount">-</div>
  </div>
  <div class="mp-kpi-card cash">
    <div class="mp-kpi-icon"><i class="fa fa-scissors"></i></div>
    <div class="mp-kpi-label">Services</div>
    <div class="mp-kpi-value"><?= count($services ?? []); ?></div>
  </div>
</div>

<!-- Product Price List -->
<div class="mp-card-form">
  <div class="mp-card-head">
    <h3>Product Price List</h3>
  </div>
  <div class="mp-card-body">
    <div class="pc-filter-bar">
      <select id="filter-category" class="mp-form-control">
        <option value="0">All Categories</option>
        <?php foreach($categories as $cat): ?>
        <option value="<?= $cat->id; ?>"><?= htmlspecialchars($cat->category_name); ?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" id="filter-search" class="mp-form-control" placeholder="Search by name or code...">
      <button class="mp-qa-btn blue" onclick="loadPriceList()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Filter
      </button>
      <button class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);" onclick="clearFilters()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Clear
      </button>
    </div>

    <div class="mp-dt-scroll">
      <table id="price-table" class="mp-tbl">
        <thead>
          <tr>
            <th>#</th><th>Code</th><th>Item Name</th><th>Category</th><th>Brand</th><th>Price</th><th>Discount</th><th>Effective Price</th><th>Stock</th>
          </tr>
        </thead>
        <tbody id="price-tbody">
          <tr><td colspan="9" class="mp-empty-state">Loading items…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Service Price List -->
<?php if(!empty($services)): ?>
<div class="mp-card-form">
  <div class="mp-card-head">
    <h3>Service Price List</h3>
  </div>
  <div class="mp-card-body" style="padding:0;">
    <?php foreach($services as $svc): ?>
    <div class="pc-service-row">
      <div>
        <div class="pc-service-name"><?= htmlspecialchars($svc->service_name); ?></div>
        <?php if(!empty($svc->description)): ?>
        <div class="pc-service-meta"><?= htmlspecialchars(mb_substr($svc->description, 0, 80)); ?></div>
        <?php endif; ?>
      </div>
      <div class="pc-service-price"><?= store_number_format($svc->price); ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<script>
var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
var canEdit = <?= !empty($can_edit) ? 'true' : 'false'; ?>;

// Debounce search input
var searchTimer;
$('#filter-search').on('input', function(){
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadPriceList, 350);
});
$('#filter-category').on('change', loadPriceList);

function loadPriceList(){
  var category = $('#filter-category').val();
  var search = $('#filter-search').val().trim();
  $('#price-tbody').html('<tr><td colspan="9" class="mp-empty-state"><i class="fa fa-spinner fa-spin"></i> Loading…</td></tr>');

  $.post('<?= base_url("operations/price_catalogue_ajax"); ?>', {
    [csrfName]: (typeof window.csrfHash !== 'undefined') ? window.csrfHash : csrfHash,
    category: category,
    search: search
  }, function(resp){
    if(typeof resp === 'string'){ try { resp = JSON.parse(resp); } catch(e){ resp = {data: []}; } }
    if(resp.csrfHash) csrfHash = resp.csrfHash;
    if(window.csrfHash && resp.csrfHash) window.csrfHash = resp.csrfHash;
    if(!resp.data || resp.data.length === 0){
      $('#price-tbody').html('<tr><td colspan="9" class="mp-empty-state">No items found. Adjust your filters and try again.</td></tr>');
      updateStats(0, 0, 0);
      return;
    }
    var html = '';
    var discounted = 0;
    resp.data.forEach(function(row){
      html += '<tr>';
      row.forEach(function(cell, idx){
        if(idx === 5 && canEdit){
          var itemId = $(cell).attr('data-item-id');
          var priceText = $(cell).text();
          html += '<td><span class="price-display" data-item-id="'+itemId+'" onclick="editPrice(this)">'+priceText+'</span></td>';
        } else {
          html += '<td>'+cell+'</td>';
        }
      });
      html += '</tr>';
      if(row[6] && row[6].indexOf('label-warning') !== -1) discounted++;
    });
    $('#price-tbody').html(html);
    updateStats(resp.data.length, resp.data.length, discounted);
  }).fail(function(){
    $('#price-tbody').html('<tr><td colspan="9" class="mp-empty-state error">Failed to load. Please try again.</td></tr>');
  });
}

function updateStats(total, active, discounted){
  $('#stat-total').text(total);
  $('#stat-active').text(active);
  $('#stat-discount').text(discounted);
}

function editPrice(span){
  if(!canEdit) return;
  var itemId = $(span).data('item-id');
  var currentText = $(span).text().replace(/[^0-9.]/g, '');
  var currentPrice = parseFloat(currentText) || 0;

  var input = '<input type="number" class="price-edit-input" id="edit-'+itemId+'" value="'+currentPrice+'" step="0.01" min="0" onkeydown="if(event.key===\'Enter\')savePrice('+itemId+',this);if(event.key===\'Escape\')cancelEdit(this)">';
  $(span).html(input);
  $('#edit-'+itemId).focus().select();
  $(span).removeAttr('onclick');
}

function savePrice(itemId, input){
  var newPrice = parseFloat($(input).val());
  if(isNaN(newPrice) || newPrice < 0){
    toastr['error']('Please enter a valid price.');
    return;
  }
  $.post('<?= base_url("operations/price_catalogue_update_price"); ?>', {
    [csrfName]: (typeof window.csrfHash !== 'undefined') ? window.csrfHash : csrfHash,
    item_id: itemId,
    new_price: newPrice
  }, function(resp){
    if(typeof resp === 'string'){ try { resp = JSON.parse(resp); } catch(e){ resp = {success: false}; } }
    if(resp.success){
      if(resp.csrf_hash) csrfHash = resp.csrf_hash;
      toastr['success']('Price updated successfully.');
      loadPriceList();
    } else {
      toastr['error'](resp.message || 'Failed to update price.');
      loadPriceList();
    }
  }).fail(function(){ toastr['error']('Server error.'); loadPriceList(); });
}

function cancelEdit(input){
  loadPriceList();
}

function clearFilters(){
  $('#filter-category').val(0);
  $('#filter-search').val('');
  loadPriceList();
}

function exportCSV(){
  var rows = [];
  var headers = [];
  $('#price-table thead th').each(function(){ headers.push($(this).text()); });
  rows.push(headers);
  $('#price-table tbody tr').each(function(){
    var row = [];
    $(this).find('td').each(function(){ row.push($(this).text().trim()); });
    rows.push(row);
  });
  var csv = rows.map(function(r){ return r.map(function(c){ return '"'+c.replace(/"/g,'""')+'"'; }).join(','); }).join('\n');
  var blob = new Blob([csv], {type:'text/csv'});
  var link = document.createElement('a');
  link.href = window.URL.createObjectURL(blob);
  link.download = 'price_catalogue_'+new Date().toISOString().slice(0,10)+'.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

$(document).ready(function(){ loadPriceList(); });
</script>
