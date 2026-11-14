<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $balance = floatval($plan->total_amount) - floatval($plan->total_paid); ?>

<div class="mp-page-head">
  <div>
    <h2>Installment Plan <?= htmlspecialchars($plan->plan_code); ?></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($plan->customer_name); ?> — <?= htmlspecialchars($plan->mobile); ?></div>
  </div>
  <a class="mp-qa-btn" href="<?= base_url('installments'); ?>"><i class="fa fa-arrow-left"></i> Back to Plans</a>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Total Amount</div>
    <div class="mp-kpi-value"><?= store_number_format($plan->total_amount); ?></div>
  </div>
  <div class="mp-kpi-card profit">
    <div class="mp-kpi-icon"><i class="fa fa-check"></i></div>
    <div class="mp-kpi-label">Total Paid</div>
    <div class="mp-kpi-value"><?= store_number_format($plan->total_paid); ?></div>
  </div>
  <div class="mp-kpi-card <?= $balance > 0 ? 'debt' : 'profit'; ?>">
    <div class="mp-kpi-icon"><i class="fa fa-balance-scale"></i></div>
    <div class="mp-kpi-label">Balance</div>
    <div class="mp-kpi-value"><?= store_number_format($balance); ?></div>
  </div>
  <div class="mp-kpi-card cash">
    <div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div>
    <div class="mp-kpi-label">Status</div>
    <div class="mp-kpi-value" style="font-size:16px;text-transform:capitalize;"><?= ucfirst($plan->status); ?></div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><i class="fa fa-user"></i> Customer &amp; Plan Details</h3>
  </div>
  <div class="mp-card-body">
    <div class="mp-form-grid">
      <div class="mp-form-group">
        <label>Customer</label>
        <p class="mp-form-hint"><strong><?= htmlspecialchars($plan->customer_name); ?></strong><br><?= htmlspecialchars($plan->mobile); ?></p>
      </div>
      <div class="mp-form-group">
        <label>Current Due</label>
        <p class="mp-form-hint"><?= store_number_format($plan->sales_due); ?></p>
      </div>
      <div class="mp-form-group">
        <label>Down Payment</label>
        <p class="mp-form-hint"><?= store_number_format($plan->down_payment_amount); ?> <?= $plan->down_payment_paid ? '<span class="text-success"><i class="fa fa-check"></i> Paid</span>' : '<span class="text-warning">Pending</span>'; ?></p>
      </div>
      <div class="mp-form-group">
        <label>Schedule</label>
        <p class="mp-form-hint"><?= $plan->installment_count; ?> x <?= store_number_format($plan->installment_amount); ?> (<?= ucfirst($plan->frequency); ?>)</p>
      </div>
      <div class="mp-form-group">
        <label>First Due Date</label>
        <p class="mp-form-hint"><?= show_date($plan->first_due_date); ?></p>
      </div>
    </div>
  </div>
</div>

<div class="mp-card">
  <div class="mp-card-head">
    <h3><i class="fa fa-list"></i> Installment Schedule</h3>
    <?php if($plan->status == 'active'){ ?>
      <a class="mp-qa-btn green" href="<?= base_url('installments/pay/'.$plan->id); ?>"><i class="fa fa-money"></i> Record Payment</a>
    <?php } ?>
  </div>
  <div class="mp-card-body" style="padding:0;">
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" style="margin:0;" width="100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Due Date</th>
            <th class="text-right">Amount Due</th>
            <th class="text-right">Amount Paid</th>
            <th class="text-right">Late Fee</th>
            <th>Status</th>
            <th>Paid Date</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($plan->payments as $p){ ?>
          <tr>
            <td><?= $p->installment_number; ?></td>
            <td><?= show_date($p->due_date); ?></td>
            <td class="text-right"><?= store_number_format($p->amount_due); ?></td>
            <td class="text-right"><?= store_number_format($p->amount_paid); ?></td>
            <td class="text-right"><?= store_number_format($p->late_fee); ?></td>
            <td><span class="label label-<?= $p->status == 'paid' ? 'success' : ($p->status == 'overdue' ? 'danger' : ($p->status == 'partial' ? 'warning' : 'default')); ?>"><?= ucfirst($p->status); ?></span></td>
            <td><?= $p->paid_date ? show_date($p->paid_date) : '-'; ?></td>
            <td class="text-center">
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

<div class="mp-form-actions" style="justify-content:flex-end;margin-top:0;">
  <a href="<?= base_url('sales/invoice/'.$plan->sales_id); ?>" target="_blank" class="mp-btn-primary"><i class="fa fa-eye"></i> View Sale Invoice</a>
  <a href="<?= base_url('installments'); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Back to Plans</a>
</div>

<script>$(".installments-active-li").addClass("active");</script>
