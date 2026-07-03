<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Validate warranty by serial or IMEI</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title">Warranty Lookup</h3></div>
          <div class="box-body">
            <form method="get" class="form-inline" style="margin-bottom:20px;">
              <div class="form-group">
                <label>Serial / IMEI:</label>
                <input type="text" name="search" class="form-control" placeholder="Enter serial or IMEI number" value="<?= htmlspecialchars($search ?? ''); ?>" style="width:300px;">
              </div>
              <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
              <a href="<?= base_url('operations/warranty_lookup'); ?>" class="btn btn-default">Clear</a>
            </form>

            <?php if(!empty($search)): ?>
            <?php if(!empty($results)): ?>
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Serial Number</th>
                  <th>IMEI</th>
                  <th>Sale Date</th>
                  <th>Customer</th>
                  <th>Warranty (Months)</th>
                  <th>Expiry</th>
                  <th>Status</th>
                  <th>Invoice</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($results as $r): 
                  $warranty_months = (int)($r->warranty_months ?? 0);
                  $sale_date = $r->sales_date;
                  $expiry_date = '';
                  $status = '<span class="label label-default">No Warranty</span>';
                  if($warranty_months > 0 && !empty($sale_date) && $sale_date != '0000-00-00'){
                    $expiry = date('Y-m-d', strtotime($sale_date . ' + ' . $warranty_months . ' months'));
                    $expiry_date = $expiry;
                    if(strtotime($expiry) >= strtotime(date('Y-m-d'))){
                      $status = '<span class="label label-success">Under Warranty</span>';
                    } else {
                      $status = '<span class="label label-danger">Expired</span>';
                    }
                  }
                ?>
                <tr>
                  <td><?= htmlspecialchars($r->item_name ?? ''); ?></td>
                  <td><?= htmlspecialchars($r->sold_serial_number ?? ''); ?></td>
                  <td><?= htmlspecialchars($r->sold_imei_number ?? ''); ?></td>
                  <td><?= !empty($sale_date) && $sale_date != '0000-00-00' ? date('d-m-Y', strtotime($sale_date)) : ''; ?></td>
                  <td><?= htmlspecialchars($r->customer_name ?? 'Walk-in'); ?> <?= !empty($r->mobile) ? '<br><small>'.htmlspecialchars($r->mobile).'</small>' : ''; ?></td>
                  <td><?= $warranty_months; ?></td>
                  <td><?= !empty($expiry_date) ? date('d-m-Y', strtotime($expiry_date)) : '-'; ?></td>
                  <td><?= $status; ?></td>
                  <td>
                    <?php if(!empty($r->sales_id) && $r->sales_id != 0): ?>
                      <a href="<?= base_url('sales/print_invoice_pos/'.$r->sales_id); ?>" target="_blank" class="btn btn-xs btn-default"><?= htmlspecialchars($r->sales_code ?? ''); ?></a>
                    <?php else: ?>
                      <span class="label label-info">In Stock</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> No records found for "<strong><?= htmlspecialchars($search); ?></strong>".</div>
            <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<?php $this->load->view('footer.php'); ?>
<div class="control-sidebar-bg"></div>
</div>
<?php $this->load->view('comman/code_js_language.php'); ?>
<?php $this->load->view('comman/code_js_sound.php'); ?>
<?php $this->load->view('comman/code_js.php'); ?>
<script>$(".warranty-lookup-active-li").addClass("active");</script>
</body>
</html>
