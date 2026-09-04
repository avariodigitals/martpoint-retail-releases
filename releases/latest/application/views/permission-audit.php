<?php
/* Permission Audit — content-only view for mp_layout */
defined('BASEPATH') OR exit('No direct script access allowed');
$audit = $audit ?? [];
$summary = $audit['summary'] ?? [];
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.audit-summary { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
.audit-card { background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:16px 20px; min-width:140px; }
.audit-card .num { font-size:28px; font-weight:700; line-height:1; }
.audit-card .lbl { font-size:12px; color:#888; margin-top:4px; text-transform:uppercase; letter-spacing:0.5px; }
.audit-card.ok .num { color:#28a745; }
.audit-card.warn .num { color:#ffc107; }
.audit-card.danger .num { color:#dc3545; }
.audit-card.info .num { color:#17a2b8; }
.audit-table { width:100%; font-size:13px; }
.audit-table th { background:#f4f4f4; padding:8px 10px; text-align:left; border-bottom:2px solid #ddd; }
.audit-table td { padding:8px 10px; border-bottom:1px solid #eee; vertical-align:top; }
.audit-table tr:hover { background:#fafafa; }
.status-badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600; }
.status-badge.ok { background:#d4edda; color:#155724; }
.status-badge.warning { background:#fff3cd; color:#856404; }
.status-badge.danger { background:#f8d7da; color:#721c24; }
.perm-key { display:inline-block; background:#e8f4fd; border:1px solid #b8daff; border-radius:3px; padding:1px 6px; margin:1px; font-size:11px; font-family:monospace; }
.hardcoded-tag { display:inline-block; background:#fff3cd; border:1px solid #ffc107; border-radius:3px; padding:1px 6px; margin:1px; font-size:11px; font-family:monospace; }
.method-name { font-family:monospace; font-size:12px; color:#555; }
.section-title { font-size:16px; font-weight:600; margin:24px 0 12px; padding-bottom:8px; border-bottom:2px solid #eee; }
.filter-bar { margin-bottom:16px; }
.filter-bar input { padding:6px 12px; border:1px solid #ddd; border-radius:4px; width:260px; }
</style>

<?php include 'comman/code_flashdata.php'; ?>

<div class="mp-page-head">
  <h1 class="mp-page-title"><i class="fa fa-shield"></i> Permission Audit</h1>
</div>

<!-- Summary Cards -->
<div class="audit-summary">
  <div class="audit-card info">
    <div class="num"><?= $summary['total_controllers'] ?? 0; ?></div>
    <div class="lbl">Controllers</div>
  </div>
  <div class="audit-card info">
    <div class="num"><?= $summary['total_methods'] ?? 0; ?></div>
    <div class="lbl">Total Methods</div>
  </div>
  <div class="audit-card ok">
    <div class="num"><?= $summary['methods_with_checks'] ?? 0; ?></div>
    <div class="lbl">Protected</div>
  </div>
  <div class="audit-card <?= ($summary['methods_without_checks'] ?? 0) > 0 ? 'warn' : 'ok'; ?>">
    <div class="num"><?= $summary['methods_without_checks'] ?? 0; ?></div>
    <div class="lbl">Unprotected</div>
  </div>
  <div class="audit-card <?= ($summary['controllers_with_hardcoded'] ?? 0) > 0 ? 'warn' : 'ok'; ?>">
    <div class="num"><?= $summary['controllers_with_hardcoded'] ?? 0; ?></div>
    <div class="lbl">Hardcoded Checks</div>
  </div>
  <div class="audit-card <?= ($summary['keys_missing_from_roles_model'] ?? 0) > 0 ? 'danger' : 'ok'; ?>">
    <div class="num"><?= $summary['keys_missing_from_roles_model'] ?? 0; ?></div>
    <div class="lbl">Missing Perm Keys</div>
  </div>
</div>

<?php if(!empty($audit['missing_from_roles_model'])): ?>
<div class="box box-danger">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-exclamation-triangle text-red"></i> Permission Keys Used in Code but Missing from Roles Model</h3>
  </div>
  <div class="box-body">
    <p class="text-muted">These permission keys are checked in controllers but are not listed in <code>Roles_model::set_persmissions()</code>. They will not appear in the role editor and cannot be assigned to custom roles.</p>
    <?php foreach($audit['missing_from_roles_model'] as $key): ?>
      <span class="perm-key" style="background:#f8d7da;border-color:#f5c6cb;"><?= htmlspecialchars($key); ?></span>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if(!empty($audit['unprotected'])): ?>
<div class="box box-warning">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-exclamation-triangle text-yellow"></i> Controllers with Unprotected Methods</h3>
  </div>
  <div class="box-body">
    <p class="text-muted">These public methods have no permission check in the method body or the controller constructor. They may be accessible to any logged-in user.</p>
    <div class="mp-dt-scroll">
      <table class="mp-static-table audit-table">
        <thead>
          <tr><th>Controller</th><th>File</th><th>Unprotected Methods</th></tr>
        </thead>
        <tbody>
          <?php foreach($audit['unprotected'] as $item): ?>
          <tr>
            <td><strong><?= htmlspecialchars($item['classname']); ?></strong></td>
            <td><code><?= htmlspecialchars($item['file']); ?></code></td>
            <td>
              <?php foreach($item['methods'] as $m): ?>
                <span class="method-name"><?= htmlspecialchars($m); ?>()</span>
              <?php endforeach; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if(!empty($audit['hardcoded'])): ?>
<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-lock text-info"></i> Controllers with Hardcoded Role Checks</h3>
  </div>
  <div class="box-body">
    <p class="text-muted">These controllers use hardcoded role IDs or <code>is_admin()</code> / <code>is_store_admin()</code> / <code>special_access()</code> instead of permission-based checks. This is intentional for admin-only features (License, Site Settings, etc.) but should be reviewed.</p>
    <div class="mp-dt-scroll">
      <table class="mp-static-table audit-table">
        <thead>
          <tr><th>Controller</th><th>File</th><th>Hardcoded Checks</th></tr>
        </thead>
        <tbody>
          <?php foreach($audit['hardcoded'] as $item): ?>
          <tr>
            <td><strong><?= htmlspecialchars($item['classname']); ?></strong></td>
            <td><code><?= htmlspecialchars($item['file']); ?></code></td>
            <td>
              <?php foreach($item['checks'] as $c): ?>
                <span class="hardcoded-tag"><?= htmlspecialchars($c); ?></span>
              <?php endforeach; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Full Controller Audit -->
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-list text-blue"></i> All Controllers</h3>
    <div class="box-tools pull-right">
      <input type="text" id="audit-filter" class="form-control" placeholder="Filter controllers..." style="width:220px;">
    </div>
  </div>
  <div class="box-body" style="overflow-x:auto;">
    <div class="mp-dt-scroll">
      <table class="mp-static-table audit-table" id="audit-table">
        <thead>
          <tr>
            <th>Controller</th>
            <th>File</th>
            <th>Status</th>
            <th>Methods</th>
            <th>Protected</th>
            <th>Unprotected</th>
            <th>Constructor Guard</th>
            <th>Permission Keys</th>
            <th>Hardcoded</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($audit['controllers'] as $c): ?>
          <tr class="audit-row" data-controller="<?= strtolower($c['classname']); ?>">
            <td><strong><?= htmlspecialchars($c['classname']); ?></strong></td>
            <td><code style="font-size:11px;"><?= htmlspecialchars($c['file']); ?></code></td>
            <td>
              <?php if($c['status'] === 'ok'): ?>
                <span class="status-badge ok">OK</span>
              <?php elseif($c['status'] === 'warning'): ?>
                <span class="status-badge warning">REVIEW</span>
              <?php else: ?>
                <span class="status-badge danger">ISSUES</span>
              <?php endif; ?>
            </td>
            <td><?= $c['methods_total']; ?></td>
            <td><?= $c['methods_with_checks']; ?></td>
            <td>
              <?php if(!empty($c['methods_without_checks'])): ?>
                <span class="text-yellow"><?= count($c['methods_without_checks']); ?></span>
              <?php else: ?>
                <span class="text-green">0</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if($c['has_constructor_check']): ?>
                <i class="fa fa-check text-green"></i>
              <?php else: ?>
                <span class="text-muted">&mdash;</span>
              <?php endif; ?>
            </td>
            <td>
              <?php foreach($c['permission_keys'] as $p): ?>
                <span class="perm-key"><?= htmlspecialchars($p); ?></span>
              <?php endforeach; ?>
              <?php if(empty($c['permission_keys'])): ?>
                <span class="text-muted">none</span>
              <?php endif; ?>
            </td>
            <td>
              <?php foreach($c['hardcoded_checks'] as $h): ?>
                <span class="hardcoded-tag"><?= htmlspecialchars($h); ?></span>
              <?php endforeach; ?>
              <?php if(empty($c['hardcoded_checks'])): ?>
                <span class="text-muted">none</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(function(){
  $('#audit-filter').on('keyup', function(){
    var q = $(this).val().toLowerCase();
    $('.audit-row').each(function(){
      var ctrl = $(this).data('controller');
      $(this).toggle(ctrl.indexOf(q) !== -1);
    });
  });
});
</script>
