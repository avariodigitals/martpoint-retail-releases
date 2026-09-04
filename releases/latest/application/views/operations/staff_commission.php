<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Track commissions by staff</div>
  </div>
</div>

<!-- Filters -->
<div class="mp-card-form">
  <div class="mp-card-head">
    <h3>Filter Report</h3>
  </div>
  <div class="mp-card-body">
    <form method="get" action="<?= base_url('operations/staff_commission'); ?>">
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label>From Date</label>
          <input type="date" name="from_date" class="mp-form-control" value="<?= $from_date; ?>">
        </div>
        <div class="mp-form-group">
          <label>To Date</label>
          <input type="date" name="to_date" class="mp-form-control" value="<?= $to_date; ?>">
        </div>
        <div class="mp-form-group">
          <label>Staff</label>
          <select name="staff_id" class="mp-form-control select2">
            <option value="">All Staff</option>
            <?php foreach($staff_list as $s): ?>
            <option value="<?= $s->id; ?>" <?= ($selected_staff_id == $s->id) ? 'selected' : ''; ?>><?= htmlspecialchars($s->username); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group" style="justify-content:flex-end;">
          <label>&nbsp;</label>
          <div class="mp-form-actions">
            <button type="submit" class="mp-btn-primary"><i class="fa fa-filter"></i> Filter</button>
            <a href="<?= base_url('operations/staff_commission'); ?>" class="mp-btn-secondary">Reset</a>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Summary Cards -->
<?php if(!empty($summary)): ?>
<div class="mp-kpi-grid">
  <?php foreach($summary as $sum): ?>
  <div class="mp-kpi-card purple">
    <div class="mp-kpi-icon"><i class="fa fa-user"></i></div>
    <div class="mp-kpi-label"><?= htmlspecialchars($sum->staff_name); ?></div>
    <div class="mp-kpi-value"><?= store_number_format($sum->total_commission); ?></div>
    <div class="mp-kpi-label"><?= $sum->invoice_count; ?> invoices &middot; <?= $sum->total_qty; ?> items</div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Commission Table -->
<div class="mp-table-wrap">
  <div class="mp-card-head">
    <h3>Commission Details</h3>
  </div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table class="mp-static-table" id="commissionTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Invoice</th>
            <th>Staff</th>
            <th>Item</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Commission</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($commissions)){ $i=1; foreach($commissions as $c){ ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><?= show_date($c->sales_date); ?></td>
            <td><a href="<?= base_url('sales/invoice/'.$c->sales_id); ?>" target="_blank"><?= $c->sales_code; ?></a></td>
            <td><?= htmlspecialchars($c->staff_name); ?></td>
            <td><?= htmlspecialchars($c->item_name); ?></td>
            <td><?= $c->sales_qty; ?></td>
            <td><?= store_number_format($c->price_per_unit); ?></td>
            <td><span class="label label-success"><?= store_number_format($c->commission_amount); ?></span></td>
          </tr>
          <?php } } else { ?>
          <tr><td colspan="8" class="mp-empty-state">No commission records found for this period.</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
