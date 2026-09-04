<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Track expired and expiring inventory</div>
  </div>
  <div class="mp-report-actions">
    <a href="<?= base_url('expiry_settings'); ?>" class="mp-btn-secondary"><i class="fa fa-cog"></i> Settings</a>
    <a href="<?= base_url('expiry_settings/send_email_alert'); ?>" class="mp-btn-secondary" onclick="toastr.success('Email alert triggered!');"><i class="fa fa-envelope"></i> Send Alert</a>
  </div>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-calendar-times-o"></i></div>
    <div style="font-size:24px;font-weight:800;"><?= count($expired); ?></div>
    <div style="font-size:13px;color:var(--mp-muted);">Expired Items</div>
  </div>
  <div class="mp-kpi-card stock">
    <div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div>
    <div style="font-size:24px;font-weight:800;"><?= count($expiring); ?></div>
    <div style="font-size:13px;color:var(--mp-muted);">Expiring Soon</div>
  </div>
  <div class="mp-kpi-card cash">
    <div class="mp-kpi-icon"><i class="fa fa-bell-o"></i></div>
    <div style="font-size:24px;font-weight:800;"><?= $settings->alert_before_days; ?></div>
    <div style="font-size:13px;color:var(--mp-muted);">Alert Days</div>
  </div>
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-exclamation-triangle"></i></div>
    <div style="font-size:24px;font-weight:800;"><?= count($expired) + count($expiring); ?></div>
    <div style="font-size:13px;color:var(--mp-muted);">Total Alerted</div>
  </div>
</div>

<?php if(!empty($expired) || !empty($expiring)): ?>
<div class="mp-report-results">
  <div class="mp-card-head"><h3>Item Details</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" id="expired-items-table" style="width:100%;">
        <thead>
          <tr>
            <th style="width:40px;">#</th>
            <th>Status</th>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Expiry Date</th>
            <th>MFG Date</th>
            <th class="text-right">Stock</th>
            <th>Time Left</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; ?>
          <?php foreach($expired as $item):
            $days = round((strtotime(date('Y-m-d')) - strtotime($item->expire_date)) / 86400);
          ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><span class="mp-status-pill-inline danger"><i class="fa fa-ban"></i> EXPIRED</span></td>
            <td><code><?= $item->item_code; ?></code></td>
            <td><strong><?= $item->item_name; ?></strong></td>
            <td><span class="text-danger"><strong><?= show_date($item->expire_date); ?></strong></span></td>
            <td><?= is_valid_date($item->mfg_date) ? show_date($item->mfg_date) : '-'; ?></td>
            <td class="text-right amt"><?= format_qty($item->stock); ?></td>
            <td><span class="mp-status-pill-inline danger"><?= $days; ?> days past</span></td>
            <td><a href="<?= base_url('items/update/'.$item->id); ?>" class="mp-btn-secondary" style="padding:6px 12px;font-size:12px;"><i class="fa fa-edit"></i> Edit</a></td>
          </tr>
          <?php endforeach; ?>

          <?php foreach($expiring as $item):
            $days = round((strtotime($item->expire_date) - strtotime(date('Y-m-d'))) / 86400);
          ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><span class="mp-status-pill-inline warn"><i class="fa fa-clock-o"></i> EXPIRING</span></td>
            <td><code><?= $item->item_code; ?></code></td>
            <td><strong><?= $item->item_name; ?></strong></td>
            <td><span class="text-warning"><strong><?= show_date($item->expire_date); ?></strong></span></td>
            <td><?= is_valid_date($item->mfg_date) ? show_date($item->mfg_date) : '-'; ?></td>
            <td class="text-right amt"><?= format_qty($item->stock); ?></td>
            <td><span class="mp-status-pill-inline warn"><?= $days; ?> days left</span></td>
            <td><a href="<?= base_url('items/update/'.$item->id); ?>" class="mp-btn-secondary" style="padding:6px 12px;font-size:12px;"><i class="fa fa-edit"></i> Edit</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php else: ?>
<div class="mp-report-results">
  <div class="box-body">
    <div class="mp-empty-state">
      <div class="mp-empty-icon"><i class="fa fa-check-circle"></i></div>
      <h3>All Clear!</h3>
      <p>No expired or expiring items found. Your inventory is currently within safe expiry thresholds.</p>
      <a href="<?= base_url('expiry_settings'); ?>" class="mp-btn-secondary"><i class="fa fa-cog"></i> Manage Settings</a>
    </div>
  </div>
</div>
<?php endif; ?>

<script>$(".report-expired-items-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
