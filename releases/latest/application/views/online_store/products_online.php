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
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Publish products to your storefront and manage online prices & featured items</div>
  </div>
  <form method="get" class="os-search" action="<?= base_url('online_store/products_online'); ?>">
    <input type="text" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Search products...">
    <button type="submit"><i class="fa fa-search"></i> Search</button>
  </form>
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
    <form method="post" action="<?= base_url('online_store/save_featured'); ?>" style="display:contents;">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <button type="submit" class="mp-qa-btn green"><i class="fa fa-save"></i> Save Featured</button>
  </div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="os-products-table" class="table mp-dt-table" width="100%">
        <thead>
          <tr><th>Image</th><th>Product</th><th>Category</th><th>Stock</th><th>Sales Price</th><th>Online Price</th><th>Online</th><th>Featured</th></tr>
        </thead>
        <tbody>
          <?php foreach($products as $p): ?>
          <tr>
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
              <input type="hidden" name="featured[<?= (int)$p->id; ?>]" value="0">
              <input type="checkbox" name="featured[<?= (int)$p->id; ?>]" value="1" <?= $p->is_featured ? 'checked' : ''; ?>>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($products)): ?><tr><td colspan="8" class="mp-empty-state">No products found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  </form>
</div>

<script>
function toggleOnline(btn){
  var id = $(btn).data('id');
  $.post('<?=base_url("online_store/toggle_product_online");?>', {
    product_id: id,
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status === 'success'){
      $(btn).toggleClass('on', res.publish_online == 1);
      toastr.success('Product ' + (res.publish_online ? 'published' : 'unpublished') + ' online');
    } else {
      toastr.error(res.message);
    }
  }, 'json');
}

function updateOnlinePrice(id, price){
  $.post('<?=base_url("online_store/update_online_price");?>', {
    product_id: id,
    online_price: price,
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status === 'success') toastr.success('Price updated');
    else toastr.error(res.message);
  }, 'json');
}

$(document).ready(function(){
  $('#os-products-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[1,"asc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [0,5,6,7], "orderable": false }]
  });
});
</script>
<script>$(".online_store-products-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
