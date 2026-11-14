<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Recipe costing and margin analysis</div>
  </div>
  <div class="mp-report-actions">
    <?php $this->load->view('components/export_btn', ['tableId' => 'report-data']); ?>
  </div>
</div>

<form class="form-horizontal" id="report-form" onkeypress="return event.keyCode != 13;">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">

  <div class="mp-report-filter">
    <div class="mp-card-head"><h3>Report Filters</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-grid">

        <?php if(store_module() && is_admin()): ?>
        <div class="mp-form-group full">
          <?php $this->load->view('store/store_code', ['show_store_select_box' => true, 'store_id' => get_current_store_id(), 'div_length' => '', 'show_all' => 'true', 'form_group_remove' => 'true']); ?>
        </div>
        <?php else: ?>
        <input type="hidden" name="store_id" id="store_id" value="<?= get_current_store_id(); ?>">
        <?php endif; ?>

        <div class="mp-form-group">
          <label for="recipe_id">Recipe</label>
          <select class="form-control select2" id="recipe_id" name="recipe_id" style="width:100%;">
            <option value="">All Recipes</option>
            <?php
            $recipes = $this->db->where('store_id', get_current_store_id())->where('status',1)->order_by('name','ASC')->get('db_recipes')->result();
            foreach($recipes as $r){ echo '<option value="'.$r->id.'">'.htmlspecialchars($r->name).'</option>'; }
            ?>
          </select>
        </div>

      </div>

      <div class="mp-report-filter-actions" style="margin-top:20px;">
        <button type="button" id="view" class="mp-btn-primary" title="Show Report"><i class="fa fa-eye"></i> Show</button>
        <a href="<?= base_url('dashboard'); ?>">
          <button type="button" class="mp-btn-secondary close_btn" title="Go Dashboard"><i class="fa fa-times"></i> Close</button>
        </a>
      </div>
    </div>
  </div>
</form>

<div class="mp-report-results">
  <div class="mp-card-head"><h3>Records</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table class="table table-bordered table-hover" id="report-data" style="width:100%;">
        <thead>
          <tr class="bg-blue">
            <th>#</th>
            <?php if(store_module() && is_admin()): ?><th>Store</th><?php endif; ?>
            <th>Recipe</th>
            <th>Category</th>
            <th>Product</th>
            <th class="text-right">Yield</th>
            <th class="text-right">Total Cost</th>
            <th class="text-right">Cost/Unit</th>
            <th class="text-right">Sales Price</th>
            <th class="text-right">Margin</th>
          </tr>
        </thead>
        <tbody id="tbodyid"></tbody>
      </table>
    </div>
  </div>
</div>

<script src="<?php echo $theme_link; ?>js/sheetjs.js"></script>
<script src="<?php echo $theme_link; ?>js/report-recipe-costing.js"></script>
<script>$(".report-recipe-costing-active-li").addClass("active");</script>
