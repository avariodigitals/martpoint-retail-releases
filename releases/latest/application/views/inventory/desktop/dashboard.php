<?php $this->load->view('inventory/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Manage stock levels, adjustments and transfers</div>
  </div>
</div>

<div class="mp-quick-actions">
  <?php if($CI->permissions('stock_adjustment_add')): ?>
  <a href="<?= base_url('stock_adjustment/add'); ?>" class="mp-qa-btn orange">
    <i class="fa fa-plus"></i> New Adjustment
  </a>
  <?php endif; ?>

  <?php if($CI->permissions('stock_transfer_add') && warehouse_module()): ?>
  <a href="<?= base_url('stock_transfer/add'); ?>" class="mp-qa-btn blue">
    <i class="fa fa-plus"></i> New Transfer
  </a>
  <?php endif; ?>

  <?php if($CI->permissions('stock_adjustment_view')): ?>
  <a href="<?= base_url('stock_adjustment'); ?>" class="mp-qa-btn green">
    <i class="fa fa-list"></i> Adjustments
  </a>
  <?php endif; ?>

  <?php if($CI->permissions('stock_transfer_view') && warehouse_module()): ?>
  <a href="<?= base_url('stock_transfer/view'); ?>" class="mp-qa-btn teal">
    <i class="fa fa-exchange"></i> Transfers
  </a>
  <?php endif; ?>

  <?php if($CI->permissions('stock_report')): ?>
  <a href="<?= base_url('reports/show_stock_report'); ?>" class="mp-qa-btn purple">
    <i class="fa fa-bar-chart"></i> Stock Report
  </a>
  <?php endif; ?>
</div>

<div class="inv-kpi-grid">
  <div class="inv-kpi-card primary">
    <div class="inv-kpi-icon"><i class="fa fa-cubes"></i></div>
    <div class="inv-kpi-label">Total Items</div>
    <div class="inv-kpi-value"><?= number_format((int)$total_items); ?></div>
  </div>

  <div class="inv-kpi-card warn">
    <div class="inv-kpi-icon"><i class="fa fa-exclamation-triangle"></i></div>
    <div class="inv-kpi-label">Low Stock</div>
    <div class="inv-kpi-value"><?= number_format((int)$low_stock_count); ?></div>
  </div>

  <div class="inv-kpi-card danger">
    <div class="inv-kpi-icon"><i class="fa fa-ban"></i></div>
    <div class="inv-kpi-label">Out of Stock</div>
    <div class="inv-kpi-value"><?= number_format((int)$out_of_stock_count); ?></div>
  </div>

  <div class="inv-kpi-card success">
    <div class="inv-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="inv-kpi-label">Stock Value</div>
    <div class="inv-kpi-value"><?= $CI->currency($total_stock_value); ?></div>
  </div>

  <div class="inv-kpi-card teal">
    <div class="inv-kpi-icon"><i class="fa fa-refresh"></i></div>
    <div class="inv-kpi-label">Adjustments</div>
    <div class="inv-kpi-value"><?= number_format((int)$adjustment_count); ?></div>
  </div>

  <?php if(warehouse_module()): ?>
  <div class="inv-kpi-card primary">
    <div class="inv-kpi-icon"><i class="fa fa-exchange"></i></div>
    <div class="inv-kpi-label">Transfers</div>
    <div class="inv-kpi-value"><?= number_format((int)$transfer_count); ?></div>
  </div>

  <div class="inv-kpi-card success">
    <div class="inv-kpi-icon"><i class="fa fa-building"></i></div>
    <div class="inv-kpi-label">Warehouses</div>
    <div class="inv-kpi-value"><?= number_format((int)$warehouse_count); ?></div>
  </div>
  <?php endif; ?>
</div>

<div class="mp-row r-equal">
  <div class="mp-card">
    <div class="mp-card-head">
      <h3>Recent Adjustments</h3>
      <?php if($CI->permissions('stock_adjustment_view')): ?>
      <a href="<?= base_url('stock_adjustment'); ?>" class="mp-card-link">View All</a>
      <?php endif; ?>
    </div>
    <div class="mp-card-body" style="padding:0;">
      <table class="inv-recent-table">
        <thead>
          <tr>
            <th><?= $this->lang->line('adjustment_date'); ?></th>
            <th><?= $this->lang->line('reference_no'); ?></th>
            <th><?= $this->lang->line('created_by'); ?></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($recent_adjustments)): foreach($recent_adjustments as $adj): ?>
          <tr>
            <td><?= show_date($adj->adjustment_date); ?></td>
            <td><?= htmlspecialchars($adj->reference_no); ?></td>
            <td><?= htmlspecialchars($adj->created_by); ?></td>
            <td>
              <?php if($CI->permissions('stock_adjustment_view')): ?>
              <a href="<?= base_url('stock_adjustment/details/'.$adj->id); ?>" title="View"><i class="fa fa-eye"></i></a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="4" class="mp-empty-state">No recent adjustments</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="mp-card">
    <div class="mp-card-head">
      <h3>Recent Transfers</h3>
      <?php if($CI->permissions('stock_transfer_view')): ?>
      <a href="<?= base_url('stock_transfer/view'); ?>" class="mp-card-link">View All</a>
      <?php endif; ?>
    </div>
    <div class="mp-card-body" style="padding:0;">
      <table class="inv-recent-table">
        <thead>
          <tr>
            <th><?= $this->lang->line('transfer_date'); ?></th>
            <th><?= $this->lang->line('from_warehouse'); ?></th>
            <th><?= $this->lang->line('to_warehouse'); ?></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($recent_transfers)): foreach($recent_transfers as $tr): ?>
          <tr>
            <td><?= show_date($tr->transfer_date); ?></td>
            <td><?= htmlspecialchars(get_warehouse_name($tr->warehouse_from)); ?></td>
            <td><?= htmlspecialchars(get_warehouse_name($tr->warehouse_to)); ?></td>
            <td>
              <?php if($CI->permissions('stock_transfer_view')): ?>
              <a href="<?= base_url('stock_transfer/info/'.$tr->id); ?>" title="View"><i class="fa fa-eye"></i></a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr>
            <td colspan="4" class="mp-empty-state">No recent transfers</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>$(".inventory-active-li").addClass("active");</script>
