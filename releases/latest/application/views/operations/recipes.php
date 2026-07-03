<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Ingredient Costing & Yield Tracking</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-3 col-xs-6"><div class="small-box bg-aqua"><div class="inner"><h3><?= $total_recipes ?? 0; ?></h3><p>Total Recipes</p></div><div class="icon"><i class="fa fa-book"></i></div></div></div>
      <div class="col-md-3 col-xs-6"><div class="small-box bg-green"><div class="inner"><h3>Active</h3><p>Production Ready</p></div><div class="icon"><i class="fa fa-check-circle"></i></div></div></div>
    </div>
    <div class="row"><div class="col-md-12">
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">Recipe Book</h3>
          <div class="box-tools pull-right">
            <a href="<?= base_url('operations/recipe'); ?>" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> New Recipe</a>
          </div>
        </div>
        <div class="box-body">
          <div class="table-responsive">
            <table id="recipes-table" class="table table-bordered table-striped">
              <thead><tr><th>#</th><th>Code</th><th>Name</th><th>Category</th><th>Product</th><th>Yield</th><th>Cost/Unit</th><th>Total Cost</th><th>Status</th><th>Action</th></tr></thead>
            </table>
          </div>
        </div>
      </div>
    </div></div>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script src="<?= base_url(); ?>theme/plugins/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>theme/plugins/DataTables-1.10.18/js/dataTables.bootstrap.min.js"></script>
<script>
$(function(){
  $('#recipes-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: "<?= base_url('operations/recipes_ajax'); ?>", type: "POST",
      data: { "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>" }
    },
    columnDefs: [{ orderable: false, targets: [9] }],
    autoWidth: false
  });
});
function delete_recipe(id) {
  if(!confirm('Delete this recipe?')) return;
  $.post('<?= base_url('operations/recipe_delete'); ?>', {
    id: id, "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
  }, function(res){
    if(res.success) { toastr.success(res.message); $('#recipes-table').DataTable().ajax.reload(); }
    else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
</script>
</body>
</html>
