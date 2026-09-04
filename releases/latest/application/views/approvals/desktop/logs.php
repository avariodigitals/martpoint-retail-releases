<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Review approval requests and decisions</div>
  </div>
</div>

<form method="get" class="form-horizontal" id="approval-logs-form" action="<?= base_url('approvals/logs'); ?>">
  <div class="mp-report-filter">
    <div class="mp-card-head"><h3>Filter Logs</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-grid">

        <div class="mp-form-group">
          <label for="type">Approval Type</label>
          <select name="type" id="type" class="form-control select2" style="width:100%;">
            <option value="">All Types</option>
            <?php foreach($approval_types as $k => $v): ?>
            <option value="<?= $k; ?>" <?= ($filters['approval_type'] == $k) ? 'selected' : ''; ?>><?= $v; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="status">Status</label>
          <select name="status" id="status" class="form-control select2" style="width:100%;">
            <option value="">All Status</option>
            <option value="pending" <?= ($filters['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="approved" <?= ($filters['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="rejected" <?= ($filters['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
            <option value="cancelled" <?= ($filters['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="date_from">From Date</label>
          <input type="date" name="date_from" id="date_from" class="form-control" value="<?= $filters['date_from']; ?>">
        </div>

        <div class="mp-form-group">
          <label for="date_to">To Date</label>
          <input type="date" name="date_to" id="date_to" class="form-control" value="<?= $filters['date_to']; ?>">
        </div>

      </div>

      <div class="mp-report-filter-actions" style="margin-top:20px;">
        <button type="submit" class="mp-btn-primary" title="Filter"><i class="fa fa-filter"></i> Filter</button>
        <a href="<?= base_url('approvals/logs'); ?>">
          <button type="button" class="mp-btn-secondary" title="Reset"><i class="fa fa-refresh"></i> Reset</button>
        </a>
      </div>
    </div>
  </div>
</form>

<div class="mp-report-results">
  <div class="mp-card-head">
    <h3>Approval Logs</h3>
    <span class="mp-status-pill-inline muted"><?= number_format($total); ?> records</span>
  </div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" id="approval-logs-table" style="width:100%;">
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Requester</th>
            <th>Approver</th>
            <th>Status</th>
            <th>Reason</th>
            <th class="text-right">Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($logs as $log): ?>
          <?php
          $badgeClass = ['pending'=>'warn','approved'=>'ok','rejected'=>'danger','cancelled'=>'muted'][$log->status] ?? 'muted';
          ?>
          <tr>
            <td><?= date('Y-m-d H:i', strtotime($log->created_at)); ?></td>
            <td><?= $approval_types[$log->approval_type] ?? $log->approval_type; ?></td>
            <td><?= htmlspecialchars($log->requesting_user_name); ?></td>
            <td><?= htmlspecialchars($log->approving_user_name ?: '-'); ?></td>
            <td><span class="mp-status-pill-inline <?= $badgeClass; ?>"><?= ucfirst($log->status); ?></span></td>
            <td><?= htmlspecialchars($log->reason ?: '-'); ?></td>
            <td class="text-right amt"><?= $log->amount ? number_format($log->amount, 2) : '-'; ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($logs)): ?>
          <tr><td colspan="7" class="text-center text-muted" style="padding:32px;">No approval logs found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if($pages > 1): ?>
<div class="mp-report-results" style="margin-top:16px;">
  <div class="box-body" style="padding:12px 20px;">
    <div class="mp-report-filter-actions" style="justify-content:center;gap:6px;">
      <?php for($i = 1; $i <= $pages; $i++): ?>
      <a href="<?= base_url('approvals/logs') ?>?page=<?= $i; ?>&amp;type=<?= urlencode($filters['approval_type'] ?? ''); ?>&amp;status=<?= urlencode($filters['status'] ?? ''); ?>&amp;date_from=<?= urlencode($filters['date_from'] ?? ''); ?>&amp;date_to=<?= urlencode($filters['date_to'] ?? ''); ?>">
        <button type="button" class="<?= ($i == $page) ? 'mp-btn-primary' : 'mp-btn-secondary'; ?>" style="min-width:38px;padding:8px 12px;"><?= $i; ?></button>
      </a>
      <?php endfor; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<script>$(".approvals-logs-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
