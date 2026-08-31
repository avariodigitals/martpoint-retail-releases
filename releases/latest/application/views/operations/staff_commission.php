<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Track commissions by staff</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row"><div class="col-md-12">

      <!-- Filters -->
      <div class="box box-default">
        <div class="box-body">
          <form method="get" action="<?= base_url('operations/staff_commission'); ?>">
            <div class="row">
              <div class="form-group col-md-3">
                <label>From Date</label>
                <input type="date" name="from_date" class="form-control" value="<?= $from_date; ?>">
              </div>
              <div class="form-group col-md-3">
                <label>To Date</label>
                <input type="date" name="to_date" class="form-control" value="<?= $to_date; ?>">
              </div>
              <div class="form-group col-md-3">
                <label>Staff</label>
                <select name="staff_id" class="form-control select2">
                  <option value="">All Staff</option>
                  <?php foreach($staff_list as $s): ?>
                  <option value="<?= $s->id; ?>" <?= ($selected_staff_id == $s->id) ? 'selected' : ''; ?>><?= htmlspecialchars($s->username); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group col-md-3" style="padding-top:24px;">
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                <a href="<?= base_url('operations/staff_commission'); ?>" class="btn btn-default">Reset</a>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Summary Cards -->
      <?php if(!empty($summary)): ?>
      <div class="row">
        <?php foreach($summary as $sum): ?>
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-purple"><i class="fa fa-user"></i></span>
            <div class="info-box-content">
              <span class="info-box-text"><?= htmlspecialchars($sum->staff_name); ?></span>
              <span class="info-box-number"><?= store_number_format($sum->total_commission); ?></span>
              <span class="text-muted"><?= $sum->invoice_count; ?> invoices &middot; <?= $sum->total_qty; ?> items</span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Commission Table -->
      <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title">Commission Details</h3></div>
        <div class="box-body table-responsive">
          <table class="table table-bordered table-striped" id="commissionTable">
            <thead class="bg-gray">
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
              <tr><td colspan="8" class="text-center text-muted">No commission records found for this period.</td></tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>

    </div></div>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>$(".staff-commission-active-li").addClass("active");</script>
</body>
</html>
