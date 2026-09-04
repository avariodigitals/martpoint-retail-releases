<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Ingredient costing & yield tracking</div>
  </div>
  <a href="<?= base_url('operations/recipe'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Recipe</a>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(0,87,255,.1);color:var(--mp-primary)"><i class="fa fa-book"></i></div><div class="mp-kpi-label">Total Recipes</div><div class="mp-kpi-value"><?= $total_recipes ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(5,150,105,.1);color:var(--mp-success)"><i class="fa fa-check-circle"></i></div><div class="mp-kpi-label">Production Ready</div><div class="mp-kpi-value">Active</div></div>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Recipe Book</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="recipes-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>#</th><th>Code</th><th>Name</th><th>Category</th><th>Product</th><th>Yield</th><th>Cost/Unit</th><th>Total Cost</th><th>Status</th><th>Action</th></tr></thead>
      </table>
    </div>
  </div>
</div>

<script>
$(function(){
  $('#recipes-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: "<?= base_url('operations/recipes_ajax'); ?>", type: "POST" },
    columnDefs: [{ orderable: false, targets: [9] }],
    autoWidth: false
  });
});
function delete_recipe(id) {
  if(!confirm('Delete this recipe?')) return;
  $.post('<?= base_url('operations/recipe_delete'); ?>', { id: id }, function(res){
    if(res.success) { toastr.success(res.message); $('#recipes-table').DataTable().ajax.reload(); }
    else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
</script>
