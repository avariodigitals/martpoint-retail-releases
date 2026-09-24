<?php
$s = $stats ?? [];
$latest = $s['latest_version'] ?? '';
$centralV = (string) ($central_version ?? '');
$channelV = (string) ($channel_version ?? '');
$centralBehind = !empty($central_update_available);
?>
<?php if ($centralBehind): ?>
<div class="cd-panel" style="border-color:#F79009; background:#FFFAEB">
  <div class="cd-panel-b" style="display:flex; align-items:center; gap:14px; flex-wrap:wrap">
    <div style="flex:1; min-width:220px">
      <strong style="color:#B54708">Central is behind the release channel</strong>
      <div style="font-size:12px; color:#667085; margin-top:2px">
        Running <code>v<?= htmlspecialchars($centralV) ?></code> — latest release is
        <code>v<?= htmlspecialchars($channelV) ?></code>. Central must always run the newest code it ships to clients.
      </div>
      <div id="cdUpdMsg" style="font-size:12px; color:#667085; margin-top:4px"></div>
    </div>
    <button class="btn btn-warning" id="cdUpdBtn" onclick="centralSelfUpdate()"><i class="fa fa-refresh"></i> Update Central Now</button>
  </div>
</div>
<script>
function centralSelfUpdate(){
  var btn = document.getElementById('cdUpdBtn');
  var msg = document.getElementById('cdUpdMsg');
  btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating…';
  msg.textContent = 'Applying update — keep this tab open.';
  (function step(){
    $.post('<?= base_url('system_updates/run_step'); ?>', {}, function(res){
      if (res.status === 'error' || res.failed) {
        msg.innerHTML = '<span class="text-danger">Paused: ' + $('<i>').text(res.message || 'open System Update to resume').html() + '</span>';
        btn.disabled = false; btn.innerHTML = '<i class="fa fa-refresh"></i> Retry Update';
        return;
      }
      msg.textContent = res.step_label ? res.step_label : 'Working…';
      if (res.done && (res.step || 0) >= 8) {
        msg.innerHTML = '<span class="text-success"><i class="fa fa-check"></i> Central updated — reloading…</span>';
        setTimeout(function(){ location.reload(); }, 1500);
        return;
      }
      setTimeout(step, 400);
    }, 'json').fail(function(){ setTimeout(step, 5000); });
  })();
}
</script>
<?php endif; ?>
<style>
.cd-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:18px}
.cd-kpi{background:#fff;border:1px solid #E5EAF3;border-radius:12px;padding:16px 18px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
.cd-kpi .k{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#667085}
.cd-kpi .v{font-size:30px;font-weight:700;color:#101828;line-height:1.15;margin-top:4px}
.cd-kpi .sub{font-size:11px;color:#98A2B3;margin-top:2px}
.cd-kpi.blue .v{color:#0057FF}.cd-kpi.green .v{color:#067647}.cd-kpi.amber .v{color:#B54708}.cd-kpi.red .v{color:#B42318}
.cd-panel{background:#fff;border:1px solid #E5EAF3;border-radius:12px;margin-bottom:18px;overflow:hidden}
.cd-panel-h{padding:13px 18px;border-bottom:1px solid #F0F3F8;font-weight:600;color:#101828;font-size:14px}
.cd-panel-h small{color:#98A2B3;font-weight:400;margin-left:6px}
.cd-panel-b{padding:14px 18px}
.cd-bar-row{display:flex;align-items:center;gap:10px;margin-bottom:8px;font-size:12px}
.cd-bar-row .lbl{width:180px;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#344054;text-align:right}
.cd-bar{flex:1;background:#F2F4F7;border-radius:6px;height:18px;overflow:hidden}
.cd-bar i{display:block;height:100%;background:#0057FF;border-radius:6px}
.cd-bar.green i{background:#12B76A}.cd-bar.amber i{background:#F79009}.cd-bar.red i{background:#F04438}
.cd-bar-row .n{width:34px;font-weight:600;color:#101828}
.cd-dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:6px;vertical-align:middle}
.cd-dot.g{background:#12B76A}.cd-dot.a{background:#F79009}.cd-dot.r{background:#F04438}.cd-dot.n{background:#D0D5DD}
.cd-pill{display:inline-block;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:600}
.cd-pill.active{background:#ECFDF3;color:#067647}.cd-pill.soon{background:#FFFAEB;color:#B54708}
.cd-pill.expired,.cd-pill.suspended{background:#FEF3F2;color:#B42318}.cd-pill.none{background:#F2F4F7;color:#667085}
.cd-empty{padding:34px 18px;text-align:center;color:#98A2B3;font-size:13px}
</style>

<p class="text-muted" style="font-size:11px; margin:0 0 10px">
  Central <code>v<?= htmlspecialchars($centralV ?: '?') ?></code>
  <?php if ($channelV !== ''): ?> · channel <code>v<?= htmlspecialchars($channelV) ?></code><?php endif; ?>
  <?= $centralBehind ? '' : ' · <span class="text-success"><i class="fa fa-check"></i> up to date</span>' ?>
  <span style="margin-left:12px">·</span>
  <label style="font-weight:400; margin-left:6px; cursor:pointer">
    <input type="checkbox" id="cdSlimMenu" <?= !empty($central_slim_menu) ? 'checked' : '' ?> onchange="toggleSlimMenu(this.checked)">
    Slim menu (hide retail menus)
  </label>
</p>
<script>
function toggleSlimMenu(on){
  $.post('<?= base_url('fleet/toggle_menu'); ?>', { enabled: on ? 1 : 0 }, function(){
    location.reload();
  }, 'json').fail(function(){ toastr.error('Could not save'); });
}
</script>
<div class="cd-grid">
  <div class="cd-kpi blue"><div class="k">Installations</div><div class="v"><?= (int)($s['total'] ?? 0) ?></div><div class="sub">stores reporting to Central</div></div>
  <div class="cd-kpi green"><div class="k">Active · 24h</div><div class="v"><?= (int)($s['active_24h'] ?? 0) ?></div><div class="sub"><?= (int)($s['active_7d'] ?? 0) ?> more this week</div></div>
  <div class="cd-kpi amber"><div class="k">Offline / Stale</div><div class="v"><?= (int)($s['stale'] ?? 0) ?></div><div class="sub"><?= (int)($s['never'] ?? 0) ?> never checked in</div></div>
  <div class="cd-kpi green"><div class="k">Licensed</div><div class="v"><?= (int)($s['licensed'] ?? 0) ?></div><div class="sub"><?= (int)($s['expiring'] ?? 0) ?> expiring within 30d</div></div>
  <div class="cd-kpi red"><div class="k">Suspended</div><div class="v"><?= (int)($s['suspended'] ?? 0) ?></div><div class="sub"><?= (int)($s['expired'] ?? 0) ?> expired · <?= (int)($s['unlicensed'] ?? 0) ?> unlicensed</div></div>
  <div class="cd-kpi"><div class="k">Outdated</div><div class="v"><?= (int)($s['outdated'] ?? 0) ?></div><div class="sub">behind v<?= htmlspecialchars($latest ?: '—') ?></div></div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="cd-panel">
      <div class="cd-panel-h">Fleet adoption <small>installs per release — how fast the fleet moves</small></div>
      <div class="cd-panel-b">
        <?php if (empty($s['by_version'])): ?><div class="cd-empty">No installs reporting yet.</div>
        <?php else: $mx = max($s['by_version']); foreach ($s['by_version'] as $v => $n): ?>
          <div class="cd-bar-row"><span class="lbl">v<?= htmlspecialchars($v) ?></span><div class="cd-bar"><i style="width:<?= max(3, round($n / $mx * 100)) ?>%"></i></div><span class="n"><?= $n ?></span></div>
        <?php endforeach; endif; ?>
      </div>
    </div>
    <div class="cd-panel">
      <div class="cd-panel-h">Installs by region <small>state / country reported by each store</small></div>
      <div class="cd-panel-b">
        <?php if (empty($s['by_region'])): ?><div class="cd-empty">No region data yet — installs report their store state on the next check-in.</div>
        <?php else: $mx = max($s['by_region']); foreach ($s['by_region'] as $r => $n): ?>
          <div class="cd-bar-row"><span class="lbl"><?= htmlspecialchars($r) ?></span><div class="cd-bar green"><i style="width:<?= max(3, round($n / $mx * 100)) ?>%"></i></div><span class="n"><?= $n ?></span></div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="cd-panel">
      <div class="cd-panel-h">Plans in the field <small>license plan distribution</small></div>
      <div class="cd-panel-b">
        <?php if (empty($s['by_plan'])): ?><div class="cd-empty">No licenses activated yet.</div>
        <?php else: $mx = max($s['by_plan']); foreach ($s['by_plan'] as $p => $n): ?>
          <div class="cd-bar-row"><span class="lbl"><?= htmlspecialchars($p) ?></span><div class="cd-bar amber"><i style="width:<?= max(3, round($n / $mx * 100)) ?>%"></i></div><span class="n"><?= $n ?></span></div>
        <?php endforeach; endif; ?>
      </div>
    </div>
    <div class="cd-panel">
      <div class="cd-panel-h">Growth <small>new installs per month</small></div>
      <div class="cd-panel-b">
        <?php if (empty($s['growth'])): ?><div class="cd-empty">No installs provisioned yet.</div>
        <?php else: $mx = max($s['growth']); foreach (array_slice($s['growth'], -12, 12, true) as $m => $n): ?>
          <div class="cd-bar-row"><span class="lbl"><?= htmlspecialchars(date('M Y', strtotime($m . '-01'))) ?></span><div class="cd-bar"><i style="width:<?= max(3, round($n / $mx * 100)) ?>%"></i></div><span class="n"><?= $n ?></span></div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($s['usage'])): ?>
<div class="cd-panel">
  <div class="cd-panel-h">Fleet usage <small>live counters aggregated from install heartbeats</small></div>
  <div class="cd-panel-b"><div class="cd-grid" style="margin-bottom:0">
    <?php foreach ($s['usage'] as $u): ?>
      <div class="cd-kpi"><div class="k"><?= htmlspecialchars($u['label']) ?></div>
        <div class="v" style="font-size:22px"><?= number_format($u['used']) ?><?= $u['unit'] ? ' ' . htmlspecialchars($u['unit']) : '' ?></div>
        <div class="sub">of <?= number_format($u['limit']) ?> licensed fleet-wide</div>
      </div>
    <?php endforeach; ?>
  </div></div>
</div>
<?php endif; ?>

<div class="cd-panel">
  <div class="cd-panel-h">Recent check-ins <small>latest heartbeats — <a href="<?= base_url('fleet') ?>">open Fleet Manager</a></small></div>
  <div class="table-responsive">
    <table class="table table-condensed" style="margin:0">
      <thead><tr><th>Install</th><th>Version</th><th>License</th><th>Region</th><th>Last check-in</th></tr></thead>
      <tbody>
      <?php if (empty($s['recent'])): ?>
        <tr><td colspan="5" class="cd-empty">Waiting for the first heartbeat.</td></tr>
      <?php else: foreach ($s['recent'] as $r):
        $seen = !empty($r->last_seen) ? strtotime($r->last_seen) : 0;
        $dot = $seen <= 0 ? 'n' : ($seen >= time() - 86400 ? 'g' : ($seen >= time() - 604800 ? 'a' : 'r'));
        $st = strtoupper((string) ($r->license_status ?? ''));
        $cls = $st === 'ACTIVE' ? 'active' : ($st === 'EXPIRING_SOON' ? 'soon' : ($st === 'EXPIRED' || $st === 'SUSPENDED' ? 'expired' : 'none'));
        $region = trim(implode(', ', array_filter([(string) ($r->store_city ?? ''), (string) ($r->store_state ?? '')])));
      ?>
        <tr>
          <td><span class="cd-dot <?= $dot ?>"></span><strong><?= htmlspecialchars(($r->store_name ?? '') ?: $r->install_url) ?></strong><br><small class="text-muted"><?= htmlspecialchars($r->install_url) ?></small></td>
          <td>v<?= htmlspecialchars($r->version ?: '?') ?></td>
          <td><span class="cd-pill <?= $cls ?>"><?= htmlspecialchars(strtolower($st ?: 'unlicensed')) ?></span></td>
          <td><?= htmlspecialchars($region !== '' ? $region : '—') ?></td>
          <td><?= $seen ? htmlspecialchars(date('M j, H:i', $seen)) : '<span class="text-muted">never</span>' ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
