<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Track ingredient usage in production runs</div>
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
          <label for="from_date">From Date</label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" value="<?php echo show_date(date('d-m-Y', strtotime('-30 days'))); ?>">
          </div>
        </div>

        <div class="mp-form-group">
          <label for="to_date">To Date</label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" value="<?php echo show_date(date('d-m-Y')); ?>">
          </div>
        </div>

        <div class="mp-form-group">
          <label for="item_id">Ingredient</label>
          <select class="form-control select2" id="item_id" name="item_id" style="width:100%;">
            <option value="">All Ingredients</option>
            <?php
            $items = $this->db->where('store_id', get_current_store_id())->where('status',1)->order_by('item_name','ASC')->get('db_items')->result();
            foreach($items as $itm){ echo '<option value="'.$itm->id.'">'.htmlspecialchars($itm->item_name).'</option>'; }
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
            <th>Ingredient</th>
            <th>Code</th>
            <th>Recipe</th>
            <th>Batch #</th>
            <th>Date</th>
            <th class="text-right">Qty Used</th>
            <th class="text-right">Cost/Unit</th>
            <th class="text-right">Line Cost</th>
          </tr>
        </thead>
        <tbody id="tbodyid"></tbody>
      </table>
    </div>
  </div>
</div>

<script src="<?php echo $theme_link; ?>js/sheetjs.js"></script>
<script src="<?php echo $theme_link; ?>js/report-ingredient-usage.js"></script>
<script>$(".report-ingredient-usage-active-li").addClass("active");</script>
