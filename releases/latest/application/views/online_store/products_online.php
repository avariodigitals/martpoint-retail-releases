<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('online_store');?>">Online Store</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Products Online Status</h3>
              <div class="box-tools">
                <form method="get" class="form-inline">
                  <input type="text" name="search" class="form-control input-sm" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Search products...">
                  <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
                </form>
              </div>
            </div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr><th>Image</th><th>Product</th><th>Category</th><th>Stock</th><th>Sales Price</th><th>Online Price</th><th>Online</th></tr>
                </thead>
                <tbody>
                  <?php foreach($products as $p): ?>
                  <tr>
                    <td style="width:50px;">
                      <?php if($p->item_image && file_exists($p->item_image)): ?>
                        <img src="<?= base_url($p->item_image); ?>" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                      <?php else: ?><div style="width:40px;height:40px;background:#F1F5F9;border-radius:4px;"></div><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($p->item_name); ?></td>
                    <td><?= htmlspecialchars($p->category_name ?: '-'); ?></td>
                    <td><?= (int)$p->stock; ?></td>
                    <td><?= store_number_format($p->sales_price); ?></td>
                    <td>
                      <input type="number" class="form-control input-sm" style="width:100px;" value="<?= $p->online_price > 0 ? $p->online_price : ''; ?>" placeholder="Same" onchange="updateOnlinePrice(<?= $p->id; ?>, this.value)">
                    </td>
                    <td>
                      <input type="checkbox" class="toggle-online" data-id="<?= $p->id; ?>" <?= $p->publish_online ? 'checked' : ''; ?>>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($products)): ?><tr><td colspan="7" class="text-center text-muted">No products found.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php $this->load->view('footer'); ?>
</div>

<?php $this->load->view('comman/code_js_sound.php');?>
<?php $this->load->view('comman/code_js.php');?>
<script>
$('.toggle-online').change(function(){
  var id = $(this).data('id');
  var checkbox = $(this);
  $.post('<?=base_url("online_store/toggle_product_online");?>', {
    product_id: id,
    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status === 'success'){
      toastr.success('Product ' + (res.publish_online ? 'published' : 'unpublished') + ' online');
    } else {
      toastr.error(res.message);
      checkbox.prop('checked', !checkbox.prop('checked'));
    }
  }, 'json');
});

function updateOnlinePrice(id, price){
  $.post('<?=base_url("online_store/update_online_price");?>', {
    product_id: id,
    online_price: price,
    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status === 'success') toastr.success('Price updated');
    else toastr.error(res.message);
  }, 'json');
}
</script>
<script>$(".online-store-products-active-li").addClass("active");</script>
</body>
</html>
