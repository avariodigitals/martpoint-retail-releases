<?php
/* Audit Trail — content-only view for mp_layout */
defined('BASEPATH') OR exit('No direct script access allowed');
$filters = $filters ?? [];
$entries = $entries ?? [];
$modules = $modules ?? [];
$f = function($k) use ($filters) { return $filters[$k] ?? ''; };
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.at-filters { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; margin-bottom:16px; }
.at-filters .fg { display:flex; flex-direction:column; gap:4px; }
.at-filters label { font-size:12px; color:#888; text-transform:uppercase; letter-spacing:0.4px; }
.at-filters input, .at-filters select { padding:7px 10px; border:1px solid #ddd; border-radius:6px; font-size:13px; }
.at-table { width:100%; font-size:13px; border-collapse:collapse; }
.at-table th { background:#f4f4f4; padding:8px 10px; text-align:left; border-bottom:2px solid #ddd; }
.at-table td { padding:8px 10px; border-bottom:1px solid #eee; vertical-align:top; }
.at-table tr:hover { background:#fafafa; }
.at-badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600; background:#e8f4fd; color:#1a5c8a; }
.at-badge.act-delete, .at-badge.act-failed { background:#f8d7da; color:#721c24; }
.at-badge.act-create, .at-badge.act-login { background:#d4edda; color:#155724; }
.at-badge.act-update, .at-badge.act-logout { background:#fff3cd; color:#856404; }
.at-muted { color:#999; font-size:12px; }
</style>

<?php include 'comman/code_flashdata.php'; ?>

<div class="mp-page-head">
  <h1 class="mp-page-title"><i class="fa fa-history"></i> Audit Trail</h1>
</div>

<?php if(!empty($table_missing)): ?>
<div class="box box-warning">
  <div class="box-body">
    <p><i class="fa fa-exclamation-triangle text-yellow"></i> The audit trail table (<code>db_audit_trail</code>) does not exist yet. Run the latest migration from <code>updates/migrations/</code> to enable audit logging.</p>
  </div>
</div>
<?php else: ?>

<form method="get" action="<?= base_url('audit_trail'); ?>" class="at-filters">
  <div class="fg">
    <label>From</label>
    <input type="date" name="from" value="<?= htmlspecialchars($f('from')); ?>">
  </div>
  <div class="fg">
    <label>To</label>
    <input type="date" name="to" value="<?= htmlspecialchars($f('to')); ?>">
  </div>
  <div class="fg">
    <label>Module</label>
    <select name="module">
      <option value="">All</option>
      <?php foreach($modules as $m): ?>
      <option value="<?= htmlspecialchars($m->module); ?>" <?= $f('module') === $m->module ? 'selected' : ''; ?>><?= htmlspecialchars($m->module); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="fg">
    <label>Search</label>
    <input type="text" name="q" value="<?= htmlspecialchars($f('q')); ?>" placeholder="User, description, ref">
  </div>
  <div class="fg">
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
  </div>
</form>

<div class="box">
  <div class="box-body table-responsive" style="padding:0;">
    <table class="at-table">
      <thead>
        <tr>
          <th>Date / Time</th>
          <th>User</th>
          <th>Module</th>
          <th>Action</th>
          <th>Ref</th>
          <th>Description</th>
          <th>IP</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($entries)): ?>
        <tr><td colspan="7" class="at-muted" style="text-align:center;padding:24px;">No audit events recorded yet.</td></tr>
        <?php else: foreach($entries as $e): ?>
        <tr>
          <td><?= htmlspecialchars(($e->created_date ?: '') . ' ' . ($e->created_time ?: '')); ?></td>
          <td><?= htmlspecialchars($e->username ?: '—'); ?></td>
          <td><span class="at-badge"><?= htmlspecialchars($e->module); ?></span></td>
          <td><span class="at-badge act-<?= htmlspecialchars(strtolower($e->action)); ?>"><?= htmlspecialchars($e->action); ?></span></td>
          <td class="at-muted"><?= htmlspecialchars($e->ref_id ?: '—'); ?></td>
          <td><?= htmlspecialchars($e->description ?: ''); ?></td>
          <td class="at-muted"><?= htmlspecialchars($e->ip_address ?: '—'); ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div class="box-footer at-muted" style="padding:8px 12px;">Showing latest <?= count($entries); ?> event(s) (max 500).</div>
</div>
<?php endif; ?>
