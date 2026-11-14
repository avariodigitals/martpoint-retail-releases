<?php $this->load->view('marketing/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Customer Tiers'); ?></h2>
    <div class="mp-page-sub">Customer Tiers &amp; Benefits</div>
  </div>
  <div>
    <a href="<?= base_url('loyalty/settings'); ?>" class="mp-qa-btn purple"><i class="fa fa-cog"></i> Manage in Settings</a>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Customer Tiers</h3></div>
  <div class="mp-card-body">
    <div class="mp-table-wrap">
      <table class="mp-static-table">
        <thead>
          <tr>
            <th>#</th><th>Tier Name</th><th>Min Spend</th><th>Min Points</th><th>Discount %</th><th>Bonus %</th><th>Priority</th><th>Birthday</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; foreach ($tiers as $tier) { ?>
          <tr>
            <td><?= $i++; ?></td>
            <td class="row-name"><?= htmlspecialchars($tier->tier_name); ?></td>
            <td class="amt"><?= store_number_format($tier->minimum_spend); ?></td>
            <td><?= htmlspecialchars($tier->minimum_points); ?></td>
            <td><?= htmlspecialchars($tier->discount_percentage); ?>%</td>
            <td><?= htmlspecialchars($tier->bonus_points_percentage); ?>%</td>
            <td><?= $tier->priority_service ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?></td>
            <td><?= htmlspecialchars(ucfirst($tier->birthday_reward_type)); ?> (<?= store_number_format($tier->birthday_reward_value); ?>)</td>
            <td><span class="label label-success">Active</span></td>
          </tr>
          <?php } ?>
          <?php if (empty($tiers)) { ?>
          <tr><td colspan="9" class="mp-empty-state">
            <div class="mp-empty-icon"><i class="fa fa-sitemap"></i></div>
            <h4>No tiers found</h4>
            <p><a href="<?= base_url('loyalty/settings'); ?>">Create tiers in Settings</a></p>
          </td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>$(".loyalty-tiers-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
