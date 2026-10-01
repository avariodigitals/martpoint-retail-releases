<?php $this->load->view('admin/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Installation, commissioning, calibration, maintenance and repair jobs</div>
  </div>
  <a href="<?= base_url('operations/service_job_form'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Service Job</a>
</div>

<div class="mp-card-form" style="margin-top:16px;">
  <div class="mp-card-body">
    <form method="get" action="<?= base_url('operations/service_jobs'); ?>" class="mp-form-grid" style="margin:0;">
      <div class="mp-form-group">
        <label>Status</label>
        <select name="status" class="mp-form-control" onchange="this.form.submit()">
          <option value="">All jobs</option>
          <option value="open_list" <?= ($status ?? '') === 'open_list' ? 'selected' : ''; ?>>Open / in progress</option>
          <?php foreach(array('open','scheduled','in_progress','awaiting_parts','completed','cancelled') as $st): ?>
          <option value="<?= $st; ?>" <?= ($status ?? '') === $st ? 'selected' : ''; ?>><?= ucwords(str_replace('_',' ',$st)); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>
</div>

<div class="mp-table-wrap" style="margin-top:16px;">
  <div class="mp-card-head"><h3><i class="fa fa-wrench"></i> Jobs</h3></div>
  <div class="mp-card-body" style="padding:0;">
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" style="margin:0;">
        <thead>
          <tr><th>#</th><th>Job</th><th>Type</th><th><?= mp_label('customer'); ?></th><th>Equipment</th><th>Site</th><th>Scheduled</th><th>Engineer</th><th>Priority</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php if(!empty($jobs)): $i=1; foreach($jobs as $j):
            $overdue = is_valid_date($j->scheduled_date) && $j->scheduled_date < date('Y-m-d') && !in_array($j->status, array('completed','cancelled'));
          ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><code><?= htmlspecialchars($j->job_code); ?></code><br><small><?= $j->title ?? ''; ?></small></td>
            <td><span class="mp-badge" style="background:var(--mp-surface);border:1px solid var(--mp-border);"><?= ucfirst($j->job_type); ?></span></td>
            <td><a href="<?= base_url('customers/profile/'.$j->customer_id); ?>"><?= htmlspecialchars($j->customer_name ?? '-'); ?></a></td>
            <td><?= $j->equipment_serial ? '<code>'.htmlspecialchars($j->equipment_serial).'</code><br><small>'.htmlspecialchars($j->equipment_item ?? '').'</small>' : '<span class="text-muted">—</span>'; ?></td>
            <td><?= htmlspecialchars($j->site_name ?? '—'); ?></td>
            <td><?= is_valid_date($j->scheduled_date) ? show_date($j->scheduled_date) : '—'; ?> <?= $overdue ? '<span class="mp-pill unpaid">overdue</span>' : ''; ?></td>
            <td><?= htmlspecialchars($j->engineer ?? '<span class="text-muted">Unassigned</span>'); ?></td>
            <td><?= $j->priority === 'urgent' ? '<span class="mp-pill unpaid">Urgent</span>' : ($j->priority === 'high' ? '<span class="mp-pill partial">High</span>' : ucfirst($j->priority ?? 'normal')); ?></td>
            <td><span class="mp-pill <?= $j->status === 'completed' ? 'paid' : ($j->status === 'cancelled' ? 'unpaid' : 'partial'); ?>"><?= ucwords(str_replace('_',' ',$j->status)); ?></span></td>
            <td><a href="<?= base_url('operations/service_job_view/'.$j->id); ?>" class="mp-qa-btn teal" style="padding:5px 10px;"><i class="fa fa-eye"></i> Open</a></td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="11" class="text-center text-muted" style="padding:40px;">No service jobs found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
