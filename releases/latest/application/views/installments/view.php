<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php $this->load->view('sidebar');?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Installment Plan <small><?= $plan->plan_code; ?></small></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo $base_url; ?>installments">Installments</a></li>
        <li class="active">Plan #<?= $plan->plan_code; ?></li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-4">
          <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-user"></i> Customer</h3></div>
            <div class="box-body">
              <p><strong><?= $plan->customer_name; ?></strong></p>
              <p><?= $plan->mobile; ?></p>
              <p>Current Due: <?= number_format($plan->sales_due, 2); ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> Plan Summary</h3></div>
            <div class="box-body no-padding">
              <table class="table table-bordered">
                <tr><td>Total Amount</td><td class="text-right text-bold"><?= number_format($plan->total_amount, 2); ?></td></tr>
                <tr><td>Down Payment</td><td class="text-right"><?= number_format($plan->down_payment_amount, 2); ?> <?= $plan->down_payment_paid ? '<i class="fa fa-check text-green"></i>' : ''; ?></td></tr>
                <tr><td>Total Paid</td><td class="text-right"><?= number_format($plan->total_paid, 2); ?></td></tr>
                <tr><td>Balance</td><td class="text-right text-bold text-red"><?= number_format($plan->total_amount - $plan->total_paid, 2); ?></td></tr>
                <tr><td>Frequency</td><td class="text-right"><?= ucfirst($plan->frequency); ?></td></tr>
                <tr><td>Status</td><td class="text-right"><span class="label label-<?= $plan->status == 'active' ? 'info' : ($plan->status == 'completed' ? 'success' : 'danger'); ?>"><?= ucfirst($plan->status); ?></span></td></tr>
              </table>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bolt"></i> Actions</h3></div>
            <div class="box-body">
              <?php if($plan->status == 'active'){ ?>
                <a href="<?= base_url('installments/pay/'.$plan->id); ?>" class="btn btn-success btn-block"><i class="fa fa-money"></i> Record Payment</a>
              <?php } ?>
              <a href="<?= base_url('sales/invoice/'.$plan->sales_id); ?>" target="_blank" class="btn btn-primary btn-block"><i class="fa fa-eye"></i> View Sale Invoice</a>
              <a href="<?= base_url('installments'); ?>" class="btn btn-default btn-block"><i class="fa fa-arrow-left"></i> Back to Plans</a>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-list"></i> Installment Schedule</h3></div>
            <div class="box-body table-responsive no-padding">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Due Date</th>
                    <th class="text-right">Amount Due</th>
                    <th class="text-right">Amount Paid</th>
                    <th class="text-right">Late Fee</th>
                    <th>Status</th>
                    <th>Paid Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($plan->payments as $p){ ?>
                  <tr>
                    <td><?= $p->installment_number; ?></td>
                    <td><?= show_date($p->due_date); ?></td>
                    <td class="text-right"><?= number_format($p->amount_due, 2); ?></td>
                    <td class="text-right"><?= number_format($p->amount_paid, 2); ?></td>
                    <td class="text-right"><?= number_format($p->late_fee, 2); ?></td>
                    <td><span class="label label-<?= $p->status == 'paid' ? 'success' : ($p->status == 'overdue' ? 'danger' : ($p->status == 'partial' ? 'warning' : 'default')); ?>"><?= ucfirst($p->status); ?></span></td>
                    <td><?= $p->paid_date ? show_date($p->paid_date) : '-'; ?></td>
                    <td>
                      <?php if($p->status != 'paid' && $plan->status == 'active'){ ?>
                        <a href="<?= base_url('installments/pay/'.$plan->id.'?payment_id='.$p->id); ?>" class="btn btn-xs btn-success"><i class="fa fa-money"></i> Pay</a>
                      <?php } ?>
                    </td>
                  </tr>
                  <?php } ?>
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
<?php $this->load->view('comman/code_js');?>
<!-- Make sidebar menu highlighter/selector -->
<script>$(".installments-active-li").addClass("active");</script>
</body>
</html>
