<?php /* Audit Trail — content-only view for mp_layout */
defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
$filters  = $filters ?? [];
$entries  = $entries ?? [];
$modules  = $modules ?? [];
$actions  = $actions ?? [];
$page     = (int)($page ?? 1);
$perPage  = (int)($per_page ?? 50);
$total    = (int)($total ?? count($entries));
$f = function($k) use ($filters) { return htmlspecialchars($filters[$k] ?? '', ENT_QUOTES, 'UTF-8'); };
$hasFilter = (bool) trim(($filters['from'] ?? '') . ($filters['to'] ?? '') . ($filters['module'] ?? '') . ($filters['action'] ?? '') . ($filters['q'] ?? ''));
$pages = max(1, (int)ceil($total / max(1, $perPage)));

/* One link per page, keeping every active filter. */
$pageUrl = function($n) use ($filters) {
    $qs = array_filter(array(
        'from'   => $filters['from']   ?? '',
        'to'     => $filters['to']     ?? '',
        'module' => $filters['module'] ?? '',
        'action' => $filters['action'] ?? '',
        'q'      => $filters['q']      ?? '',
        'page'   => $n,
    ), function($v){ return $v !== '' && $v !== null; });
    return base_url('audit_trail') . ($qs ? '?' . http_build_query($qs) : '');
};

/* Readable action words — a log of "update" is less useful than "Changed". */
$verb = function($a) {
    $a = strtolower(trim((string)$a));
    $map = array(
        'login' => 'Signed in', 'logout' => 'Signed out', 'failed' => 'Failed', 'login_failed' => 'Sign-in failed',
        'create' => 'Added', 'insert' => 'Added', 'add' => 'Added', 'new' => 'Added',
        'update' => 'Changed', 'edit' => 'Changed', 'modified' => 'Changed',
        'delete' => 'Removed', 'remove' => 'Removed',
        'approve' => 'Approved', 'reject' => 'Rejected', 'review' => 'Reviewed',
        'post' => 'Posted', 'void' => 'Voided', 'cancel' => 'Cancelled', 'print' => 'Printed',
    );
    return $map[$a] ?? ucfirst(str_replace('_', ' ', $a));
};
$tone = function($a) {
    $a = strtolower(trim((string)$a));
    if (strpos($a, 'fail') !== false || strpos($a, 'delete') !== false || strpos($a, 'remove') !== false || strpos($a, 'void') !== false || strpos($a, 'reject') !== false) return 'bad';
    if (strpos($a, 'login') !== false || strpos($a, 'create') !== false || strpos($a, 'add') !== false || strpos($a, 'approve') !== false || strpos($a, 'post') !== false) return 'good';
    return 'warn';
};
$dayLabel = function($d) {
    $t = strtotime($d . ' 00:00:00');
    if (!$t) return (string)$d;
    if ($d === date('Y-m-d')) return 'Today';
    if ($d === date('Y-m-d', strtotime('-1 day'))) return 'Yesterday';
    return date('l, j F Y', $t);
};
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.at-head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap}
.at-count{font-size:12px;color:var(--mp-muted)}
.at-count b{color:var(--mp-text,#111)}
.at-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.at-filters{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;align-items:end}
.at-filters .fg{display:flex;flex-direction:column;gap:4px;min-width:0}
.at-filters label{font-size:10px;font-weight:800;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.5px}
.at-filters input,.at-filters select{width:100%;padding:7px 10px;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:12px!important;background:var(--mp-bg,#fff)!important;color:inherit!important}
.at-filters .acts{display:flex;gap:8px;align-items:center}
.at-day{margin:18px 0 8px;display:flex;align-items:center;gap:10px}
.at-day h3{margin:0;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--mp-muted)}
.at-day .rule{flex:1;height:1px;background:var(--mp-border)}
.at-day .n{font-size:10px;font-weight:800;color:var(--mp-muted);background:#F1F5F9;border-radius:10px;padding:2px 8px}
.at-row{display:grid;grid-template-columns:76px 92px 1fr 150px;gap:12px;align-items:start;padding:11px 12px;border:1px solid var(--mp-border)!important;border-radius:12px!important;background:var(--mp-surface,#fff)!important;margin-bottom:8px}
.at-row:hover{border-color:#CBD5E1!important}
.at-time{font-size:11px;font-weight:700;color:var(--mp-muted);padding-top:2px;white-space:nowrap}
.at-pill{display:inline-block;padding:3px 10px;border-radius:10px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
.at-good{background:#D1FAE5;color:#065F46}
.at-warn{background:#FEF3C7;color:#92400E}
.at-bad{background:#FEE2E2;color:#991B1B}
.at-main{min-width:0}
.at-what{font-size:13px;font-weight:700;line-height:1.35;overflow-wrap:anywhere}
.at-what .who{font-weight:800}
.at-what .mod{display:inline-block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#475569;background:#F1F5F9;border-radius:8px;padding:2px 7px;margin-right:6px;vertical-align:1px}
.at-desc{font-size:12px;color:#475569;line-height:1.45;margin-top:3px;overflow-wrap:anywhere}
.at-ref{font-size:11px;color:var(--mp-muted);text-align:right;overflow-wrap:anywhere;line-height:1.4}
.at-ref .lbl{display:block;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.5px}
.at-pager{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin:16px 0 4px}
.at-pager .nav{display:flex;gap:6px;flex-wrap:wrap}
.at-pager a,.at-pager span.cur{display:inline-block;padding:6px 11px;border:1px solid var(--mp-border)!important;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;color:inherit}
.at-pager a:hover{background:#F1F5F9}
.at-pager span.cur{background:#0F172A;color:#fff;border-color:#0F172A!important}
.at-pager span.off{opacity:.45}
.at-empty{padding:38px 18px;text-align:center;color:var(--mp-muted);font-size:13px}
.at-empty .big{font-size:15px;font-weight:800;color:#475569;margin-bottom:6px}
@media (max-width:820px){
  .at-row{grid-template-columns:70px 1fr}
  .at-row .at-main{grid-column:1 / -1}
  .at-ref{text-align:left}
}
</style>

<div class="mp-page-head at-head">
  <div>
    <h2><i class="fa fa-history"></i> <?= htmlspecialchars($page_title ?? 'Audit Trail'); ?></h2>
    <div class="mp-page-sub">Every important action taken in this store — who did it, what changed and when.</div>
  </div>
  <div class="at-count">
    <?php if($total > 0): ?>
      Showing <b><?= count($entries); ?></b> of <b><?= number_format($total); ?></b> event(s)
      <?php if($pages > 1): ?> &middot; page <b><?= $page; ?></b> of <b><?= $pages; ?></b><?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?php if(!empty($table_missing)): ?>
<div class="at-box">
  <div class="at-empty">
    <div class="big">The audit table is not installed yet</div>
    Run the latest migration from <code>updates/migrations/</code> to switch audit logging on.
  </div>
</div>
<?php else: ?>

<?php if(!empty($audit_disabled)): ?>
<div class="at-box" role="status">Audit recording is disabled for this installation. Existing events remain visible. Ask your administrator to enable Record audit trail in Fleet settings.</div>
<?php endif; ?>

<div class="at-box">
  <form method="get" action="<?= base_url('audit_trail'); ?>" class="at-filters">
    <div class="fg">
      <label>From</label>
      <input type="date" name="from" value="<?= $f('from'); ?>">
    </div>
    <div class="fg">
      <label>To</label>
      <input type="date" name="to" value="<?= $f('to'); ?>">
    </div>
    <div class="fg">
      <label>Screen</label>
      <select name="module">
        <option value="">Every screen</option>
        <?php foreach($modules as $m): ?>
        <option value="<?= htmlspecialchars($m->module); ?>" <?= ($filters['module'] ?? '') === $m->module ? 'selected' : ''; ?>><?= htmlspecialchars(mp_code_label($m->module)); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="fg">
      <label>What happened</label>
      <select name="action">
        <option value="">Anything</option>
        <?php foreach($actions as $a): ?>
        <option value="<?= htmlspecialchars($a->action); ?>" <?= ($filters['action'] ?? '') === $a->action ? 'selected' : ''; ?>><?= htmlspecialchars($verb($a->action)); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="fg" style="min-width:210px">
      <label>Search</label>
      <input type="text" name="q" value="<?= $f('q'); ?>" placeholder="Name, detail or reference">
    </div>
    <div class="fg acts">
      <button type="submit" class="mp-qa-btn blue"><i class="fa fa-filter"></i> Filter</button>
      <?php if($hasFilter): ?><a class="mp-qa-btn" href="<?= base_url('audit_trail'); ?>">Clear</a><?php endif; ?>
    </div>
  </form>
</div>

<?php if(empty($entries)): ?>
<div class="at-box">
  <div class="at-empty">
    <div class="big">Nothing to show</div>
    <?php if($hasFilter): ?>
      No events match these filters. Widen the dates or <a href="<?= base_url('audit_trail'); ?>">clear the filters</a>.
    <?php else: ?>
      No actions have been recorded yet. As soon as someone signs in or changes a record, it appears here.
    <?php endif; ?>
  </div>
</div>
<?php else:
  $groups = array();
  foreach($entries as $e){
    $d = (string)($e->created_date ?: substr((string)($e->created_at ?? ''), 0, 10));
    $groups[$d][] = $e;
  }
?>
<div class="at-box">
  <?php foreach($groups as $day => $rows): ?>
    <div class="at-day">
      <h3><?= htmlspecialchars($dayLabel($day)); ?></h3>
      <div class="rule"></div>
      <div class="n"><?= count($rows); ?> event(s)</div>
    </div>
    <?php foreach($rows as $e):
      $who  = function_exists('mp_first_name') ? mp_first_name((int)$e->user_id, $e->username) : ($e->username ?: 'System');
      $verbTxt = $verb($e->action);
      $time = '';
      if(!empty($e->created_at)){ $ts = strtotime($e->created_at); if($ts){ $time = date('g:i A', $ts); } }
      if($time === '' && !empty($e->created_time)){ $ts = strtotime($e->created_time); $time = $ts ? date('g:i A', $ts) : (string)$e->created_time; }
    ?>
    <div class="at-row">
      <div class="at-time"><?= htmlspecialchars($time ?: '—'); ?></div>
      <div><span class="at-pill at-<?= $tone($e->action); ?>"><?= htmlspecialchars($verbTxt); ?></span></div>
      <div class="at-main">
        <div class="at-what">
          <span class="mod"><?= htmlspecialchars(function_exists('mp_code_label') ? mp_code_label($e->module) : $e->module); ?></span>
          <span class="who"><?= htmlspecialchars($who); ?></span>
          <?= htmlspecialchars(strtolower($verbTxt)); ?>
          <?php if(!empty($e->ref_id)): ?><span class="at-desc" style="display:inline;margin:0">&mdash; reference <?= htmlspecialchars($e->ref_id); ?></span><?php endif; ?>
        </div>
        <?php if(!empty($e->description)): ?><div class="at-desc"><?= htmlspecialchars($e->description); ?></div><?php endif; ?>
      </div>
      <div class="at-ref">
        <?php if(!empty($e->ip_address)): ?><span class="lbl">Device address</span><?= htmlspecialchars($e->ip_address); ?><?php else: ?>&nbsp;<?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endforeach; ?>

  <?php if($pages > 1): ?>
  <div class="at-pager">
    <div class="at-count">Page <b><?= $page; ?></b> of <b><?= $pages; ?></b> &middot; <b><?= number_format($total); ?></b> event(s) in total</div>
    <div class="nav">
      <?php if($page > 1): ?><a href="<?= $pageUrl($page - 1); ?>">&larr; Newer</a><?php else: ?><span class="cur off" style="background:none;color:inherit">&larr; Newer</span><?php endif; ?>
      <?php
        $start = max(1, $page - 2); $end = min($pages, $page + 2);
        if($start > 1): ?><a href="<?= $pageUrl(1); ?>">1</a><?php if($start > 2): ?><span class="off">&hellip;</span><?php endif; endif;
        for($i = $start; $i <= $end; $i++):
          if($i === $page): ?><span class="cur"><?= $i; ?></span><?php else: ?><a href="<?= $pageUrl($i); ?>"><?= $i; ?></a><?php endif;
        endfor;
        if($end < $pages): ?><?php if($end < $pages - 1): ?><span class="off">&hellip;</span><?php endif; ?><a href="<?= $pageUrl($pages); ?>"><?= $pages; ?></a><?php endif;
      ?>
      <?php if($page < $pages): ?><a href="<?= $pageUrl($page + 1); ?>">Older &rarr;</a><?php else: ?><span class="cur off" style="background:none;color:inherit">Older &rarr;</span><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>
