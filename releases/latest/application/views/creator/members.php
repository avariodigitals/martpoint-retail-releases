<?php $this->load->view('creator/_styles'); $today = date('Y-m-d'); ?>

<div class="mp-page-head">
  <div>
    <h2>Members</h2>
    <div class="mp-page-sub"><?= count($rows); ?> subscriptions</div>
  </div>
  <a href="<?= base_url('creator/products/membership'); ?>" class="mp-btn"><i class="fa fa-star"></i> Manage Plans</a>
</div>

<?php if(empty($rows)): ?>
  <div class="cr-empty">
    <div class="icon"><i class="fa fa-id-badge"></i></div>
    <h3>No members yet</h3>
    <p>When a customer buys a membership their subscription appears here with the renewal date.</p>
    <a href="<?= base_url('creator/products/membership'); ?>" class="mp-btn mp-btn-primary">View Membership Plans</a>
  </div>
<?php else: ?>
<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Subscriptions</h3></div>
  <div class="mp-dt-scroll">
    <table class="mp-dt-table">
      <thead>
        <tr><th>Member</th><th>Plan</th><th>Started</th><th>Renews / Ends</th><th>Payment</th><th>Status</th><th style="width:100px;">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r):
          $expired = !empty($r->end_date) && $r->end_date < $today;
          $status = $expired && $r->status === 'active' ? 'expired' : $r->status;
        ?>
        <tr>
          <td>
            <div class="row-name"><?= htmlspecialchars($r->customer_name ?: 'Customer #'.$r->customer_id); ?></div>
            <div class="row-meta"><?= htmlspecialchars($r->email ?: $r->mobile ?: ''); ?></div>
          </td>
          <td>
            <div class="row-name"><?= htmlspecialchars($r->membership_name ?: '—'); ?></div>
            <div class="row-meta">billed <?= htmlspecialchars($r->billing_interval ?: 'monthly'); ?></div>
          </td>
          <td class="row-meta"><?= show_date($r->start_date); ?></td>
          <td class="row-meta"><?= !empty($r->end_date) ? show_date($r->end_date) : '—'; ?></td>
          <td><span class="cr-pill <?= htmlspecialchars($r->payment_status); ?>"><?= htmlspecialchars($r->payment_status); ?></span></td>
          <td><span class="cr-pill <?= htmlspecialchars($status); ?>"><?= htmlspecialchars($status); ?></span></td>
          <td>
            <div class="cr-table-actions">
              <?php if(!empty($r->customer_id)): ?><a href="<?= base_url('customers/update/' . $r->customer_id); ?>">Profile</a><?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
