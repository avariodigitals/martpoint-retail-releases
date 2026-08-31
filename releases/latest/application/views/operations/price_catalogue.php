<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?>
<style>
.pc-filter-bar { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:16px; }
.pc-filter-bar select, .pc-filter-bar input { border-radius:8px; border:1px solid #E2E8F0; padding:8px 12px; font-size:14px; }
.pc-filter-bar select { min-width:160px; }
.pc-filter-bar input { min-width:200px; }
.pc-stat-card { background:#fff; border-radius:12px; padding:16px 20px; border:1px solid #E2E8F0; display:flex; align-items:center; gap:14px; }
.pc-stat-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; }
.pc-stat-icon.blue { background:#DBEAFE; color:#0057FF; }
.pc-stat-icon.green { background:#D1FAE5; color:#059669; }
.pc-stat-icon.orange { background:#FEF3C7; color:#D97706; }
.pc-stat-value { font-size:22px; font-weight:800; color:#1E293B; }
.pc-stat-label { font-size:12px; color:#64748B; font-weight:600; }
.price-edit-input { width:90px; padding:4px 8px; border:1px solid #3B82F6; border-radius:6px; font-size:13px; }
.price-display { cursor:pointer; }
.price-display:hover { text-decoration:underline; color:#3B82F6; }
.pc-section-title { font-size:15px; font-weight:700; color:#1E293B; margin:20px 0 10px; display:flex; align-items:center; gap:8px; }
.pc-service-row { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; border-bottom:1px solid #F1F5F9; }
.pc-service-row:last-child { border-bottom:none; }
.pc-service-name { font-size:14px; font-weight:600; color:#1E293B; }
.pc-service-price { font-size:14px; font-weight:700; color:#0057FF; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-3 col-sm-6">
        <div class="pc-stat-card">
          <div class="pc-stat-icon blue"><i class="fa fa-tags"></i></div>
          <div><div class="pc-stat-value" id="stat-total">-</div><div class="pc-stat-label">Total Items</div></div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="pc-stat-card">
          <div class="pc-stat-icon green"><i class="fa fa-check-circle"></i></div>
          <div><div class="pc-stat-value" id="stat-active">-</div><div class="pc-stat-label">Active Items</div></div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="pc-stat-card">
          <div class="pc-stat-icon orange"><i class="fa fa-percent"></i></div>
          <div><div class="pc-stat-value" id="stat-discount">-</div><div class="pc-stat-label">Discounted</div></div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="pc-stat-card">
          <div class="pc-stat-icon blue"><i class="fa fa-scissors"></i></div>
          <div><div class="pc-stat-value"><?= count($services ?? []); ?></div><div class="pc-stat-label">Services</div></div>
        </div>
      </div>
    </div>

    <div class="box box-info" style="margin-top:16px;border-radius:12px;">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-tags"></i> Product Price List</h3>
        <div class="box-tools pull-right">
          <button class="btn btn-default btn-sm" onclick="exportCSV()"><i class="fa fa-file-excel-o"></i> Export CSV</button>
          <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
      </div>
      <div class="box-body">
        <div class="pc-filter-bar">
          <select id="filter-category" class="form-control">
            <option value="0">All Categories</option>
            <?php foreach($categories as $cat): ?>
            <option value="<?= $cat->id; ?>"><?= htmlspecialchars($cat->category_name); ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" id="filter-search" class="form-control" placeholder="Search by name or code...">
          <button class="btn btn-primary btn-sm" onclick="loadPriceList()"><i class="fa fa-search"></i> Filter</button>
          <button class="btn btn-default btn-sm" onclick="clearFilters()"><i class="fa fa-times"></i> Clear</button>
        </div>

        <table id="price-table" class="table table-bordered table-striped" style="font-size:14px;">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Item Name</th>
              <th>Category</th>
              <th>Brand</th>
              <th>Price</th>
              <th>Discount</th>
              <th>Effective Price</th>
              <th>Stock</th>
            </tr>
          </thead>
          <tbody id="price-tbody">
            <tr><td colspan="9" style="text-align:center;padding:30px;color:#94A3B8;">Click "Filter" to load items.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <?php if(!empty($services)): ?>
    <div class="box box-success" style="margin-top:16px;border-radius:12px;">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-scissors"></i> Service Price List</h3>
      </div>
      <div class="box-body" style="padding:0;">
        <?php foreach($services as $svc): ?>
        <div class="pc-service-row">
          <div>
            <div class="pc-service-name"><?= htmlspecialchars($svc->service_name); ?></div>
            <?php if(!empty($svc->description)): ?><small style="color:#64748B;"><?= htmlspecialchars(substr($svc->description, 0, 80)); ?></small><?php endif; ?>
          </div>
          <div class="pc-service-price"><?= store_number_format($svc->price); ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
$(".price-catalogue-active-li").addClass("active");

var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
var canEdit = <?= !empty($can_edit) ? 'true' : 'false'; ?>;

function loadPriceList(){
  var category = $('#filter-category').val();
  var search = $('#filter-search').val().trim();
  $('#price-tbody').html('<tr><td colspan="9" style="text-align:center;padding:30px;color:#94A3B8;"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');

  $.post('<?= base_url("operations/price_catalogue_ajax"); ?>', {
    [csrfName]: csrfHash,
    category: category,
    search: search
  }, function(resp){
    try { resp = JSON.parse(resp); } catch(e) { resp = resp; }
    csrfHash = resp.csrfHash || csrfHash;
    if(!resp.data || resp.data.length === 0){
      $('#price-tbody').html('<tr><td colspan="9" style="text-align:center;padding:30px;color:#94A3B8;">No items found.</td></tr>');
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
      // Check if discounted (column index 6)
      if(row[6] && row[6].indexOf('label-warning') !== -1) discounted++;
    });
    $('#price-tbody').html(html);
    updateStats(resp.data.length, resp.data.length, discounted);
  }).fail(function(){
    $('#price-tbody').html('<tr><td colspan="9" style="text-align:center;padding:30px;color:#EF4444;">Failed to load. Please try again.</td></tr>');
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
    alert('Please enter a valid price.');
    return;
  }
  $.post('<?= base_url("operations/price_catalogue_update_price"); ?>', {
    [csrfName]: csrfHash,
    item_id: itemId,
    new_price: newPrice
  }, function(resp){
    try { resp = JSON.parse(resp); } catch(e) { resp = resp; }
    if(resp.success){
      csrfHash = resp.csrf_hash || csrfHash;
      var span = $('#edit-'+itemId).parent();
      span.text(resp.new_price);
      span.attr('onclick', 'editPrice(this)');
      toastr.success('Price updated successfully');
      loadPriceList();
    } else {
      alert(resp.message || 'Failed to update price.');
      loadPriceList();
    }
  }).fail(function(){ alert('Server error.'); loadPriceList(); });
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
    $(this).find('td').each(function(){
      row.push($(this).text().trim());
    });
    rows.push(row);
  });
  var csv = rows.map(function(r){ return r.map(function(c){ return '"'+c.replace(/"/g,'""')+'"'; }).join(','); }).join('\n');
  var blob = new Blob([csv], {type:'text/csv'});
  var link = document.createElement('a');
  link.href = window.URL.createObjectURL(blob);
  link.download = 'price_catalogue_'+new Date().toISOString().slice(0,10)+'.csv';
  link.click();
}

$(document).ready(function(){ loadPriceList(); });
</script>
</body>
</html>
