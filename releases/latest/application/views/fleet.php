<?php
/* Fleet Manager — content-only view for mp_layout (central install only) */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<?php include "comman/code_flashdata.php"; ?>

<style>
/* Fleet dashboard */
.fleet-stats { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
.fleet-stat {
  flex: 1 1 140px; background: #fff; border: 1px solid #E7E5E4;
  border-radius: 10px; padding: 14px 16px;
}
.fleet-stat .num { font-size: 22px; font-weight: 700; line-height: 1.2; }
.fleet-stat .lbl { font-size: 11px; color: #78716C; text-transform: uppercase; letter-spacing: .4px; }
.fleet-stat.warn .num { color: #D97706; }
.fleet-stat.bad .num { color: #DC2626; }

/* Keep long keys and the installs table inside their cards */
.fleet-card { overflow: hidden; }
.fleet-key-row { display: flex; align-items: stretch; gap: 6px; }
.fleet-key-row .form-control {
  flex: 1 1 auto; min-width: 0; width: auto !important;
  font-family: monospace; font-size: 11px;
}
.fleet-key-row .btn { flex: 0 0 auto; white-space: nowrap; }
/* ---- Installs list ---------------------------------------------------
   A 9-column table with a button cluster and a clamped detail block in
   every row does not survive scale: row height is set by the widest cell,
   so 50 installs is a wall and 1000 is unusable. Replaced with a list of
   fixed-height rows that expand in place. Collapsed = scannable; expanded
   = everything for the ONE install you are looking at. */
.fleet-list { border: 1px solid #E7E5E4; border-radius: 10px; overflow: hidden; background: #fff; }
.fleet-row { border-bottom: 1px solid #F1F0EF; }
.fleet-row:last-child { border-bottom: 0; }
.fleet-row.is-open { background: #FAFAF9; }
.fleet-row.is-bad { box-shadow: inset 3px 0 0 #DC2626; }
.fleet-row.is-warn { box-shadow: inset 3px 0 0 #D97706; }

/* Collapsed summary — one line, fixed height, nothing that can grow. */
.fleet-row-main {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 12px; min-height: 46px; cursor: pointer;
}
.fleet-row-main:hover { background: #F5F5F4; }
.fleet-row-main .fleet-check { flex: 0 0 auto; width: 26px; text-align: center; }
.fleet-caret {
  flex: 0 0 auto; width: 14px; color: #A8A29E; font-size: 11px;
  transition: transform .15s ease;
}
.fleet-row.is-open .fleet-caret { transform: rotate(90deg); }
.fleet-id { flex: 1 1 200px; min-width: 0; }
.fleet-id .nm {
  font-weight: 600; font-size: 12.5px; color: #1C1917;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.fleet-id .url {
  font-size: 11px; color: #78716C; overflow: hidden;
  text-overflow: ellipsis; white-space: nowrap;
}
/* Fixed-width columns so every row lines up and the eye can scan a column. */
.fleet-col { flex: 0 0 auto; }
.fleet-col-ver   { width: 92px; }
.fleet-col-stage { width: 168px; }
.fleet-col-seen  { width: 132px; }

.fleet-row-main .fleet-quick {
  flex: 0 0 auto; display: flex; gap: 4px; align-items: center;
  opacity: 0; transition: opacity .12s ease;
}
.fleet-row-main:hover .fleet-quick,
.fleet-row.is-open .fleet-quick { opacity: 1; }

/* Expanded panel — the dense detail lives ONLY here. */
.fleet-row-detail { padding: 0 12px 14px 60px; cursor: default; }
.fleet-detail-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
  gap: 12px; padding-top: 4px;
}
.fleet-detail-box {
  background: #fff; border: 1px solid #E7E5E4; border-radius: 8px;
  padding: 9px 11px;
}
.fleet-detail-box h5 {
  margin: 0 0 6px; font-size: 10px; text-transform: uppercase;
  letter-spacing: .4px; color: #78716C; font-weight: 700;
}
.fleet-detail-box .kv { font-size: 11.5px; color: #44403C; line-height: 1.7; }
.fleet-detail-box .kv code { font-size: 10.5px; }
.fleet-detail-actions {
  display: flex; flex-wrap: wrap; gap: 5px; margin-top: 12px;
  padding-top: 12px; border-top: 1px solid #E7E5E4;
}
.fleet-longtext {
  font-size: 11px; color: #57534E; line-height: 1.6; word-break: break-word;
  max-height: 120px; overflow-y: auto;
}

/* Toolbar above the list. */
.fleet-toolbar {
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
  margin-bottom: 12px;
}
.fleet-toolbar .form-control { height: 30px; font-size: 12px; }
.fleet-toolbar .fleet-search { flex: 1 1 200px; max-width: 280px; }
.fleet-toolbar select { width: auto; }
.fleet-count { font-size: 12px; color: #78716C; }
.fleet-pager { display: flex; gap: 6px; margin-left: auto; align-items: center; }
.fleet-pager .btn-group .btn { padding: 4px 10px; font-size: 12px; }

.fleet-empty { padding: 34px 16px; text-align: center; color: #78716C; font-size: 13px; }
.fleet-badge {
  display: inline-block; font-size: 10px; font-weight: 600; padding: 2px 7px;
  border-radius: 20px; text-transform: uppercase; letter-spacing: .3px;
}
.fleet-badge.ok { background: #DCFCE7; color: #15803D; }
.fleet-badge.soon { background: #FEF3C7; color: #B45309; }
.fleet-badge.bad { background: #FEE2E2; color: #B91C1C; }
.fleet-badge.off { background: #E7E5E4; color: #57534E; }
.fleet-badge.info { background: #DBEAFE; color: #1D4ED8; }
/* Update stage badge — the one visual vocabulary shared by the collapsed
   row and the expanded panel, so the colour always means the same thing. */
  .fleet-stage-badge {
    display: inline-flex; align-items: center; gap: 5px; font-size: 10px;
    font-weight: 600; padding: 3px 8px; border-radius: 20px;
    text-transform: uppercase; letter-spacing: .3px; white-space: nowrap;
  }
  .fleet-stage-badge .dot {
    width: 7px; height: 7px; border-radius: 50%; background: currentColor;
    flex: 0 0 auto;
  }
  /* Colour carries the meaning — green flows, amber is moving/needs a look,
     red is stopped, grey is quiet. */
  .fleet-stage-badge.st-idle       { background: #DCFCE7; color: #15803D; }
  .fleet-stage-badge.st-progress   { background: #DBEAFE; color: #1D4ED8; }
  .fleet-stage-badge.st-ready      { background: #FEF3C7; color: #B45309; }
  .fleet-stage-badge.st-stalled    { background: #FFEDD5; color: #C2410C; }
  .fleet-stage-badge.st-failed     { background: #FEE2E2; color: #B91C1C; }
  .fleet-stage-badge.st-unknown    { background: #E7E5E4; color: #57534E; }
  /* A live pulse only while something is actually moving — an animation on a
     stuck install would be a lie. */
  .fleet-stage-badge.st-progress .dot { animation: fleetPulse 1.4s ease-in-out infinite; }
  @keyframes fleetPulse { 0%,100% { opacity: 1; } 50% { opacity: .25; } }
  .fleet-stage-bar {
    height: 3px; background: #E7E5E4; border-radius: 2px; margin-top: 3px;
    overflow: hidden; max-width: 180px;
  }
  .fleet-stage-bar > span { display: block; height: 100%; background: #1D4ED8; }
.fleet-bulkbar {
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
  background: #F5F5F4; border: 1px solid #E7E5E4; border-radius: 8px;
  padding: 8px 12px; margin-bottom: 10px; font-size: 12px;
}
.fleet-bulkbar .fleet-sel-count { font-weight: 600; color: #44403C; }
.fleet-bulkbar select.form-control {
  width: auto; display: inline-block; height: 28px; padding: 3px 8px; font-size: 12px;
}
.fleet-bulkbar .fleet-bulk-progress { color: #78716C; }
</style>

<div class="mp-page-head">
  <h1 class="mp-page-title">Fleet Manager</h1>
</div>

<div class="fleet-stats">
  <div class="fleet-stat"><div class="num"><?= (int) $stats['total'] ?></div><div class="lbl">Registered Installs</div></div>
  <div class="fleet-stat"><div class="num"><?= (int) $stats['active24h'] ?></div><div class="lbl">Seen in 24h</div></div>
  <div class="fleet-stat<?= $stats['outdated'] ? ' warn' : '' ?>"><div class="num"><?= (int) $stats['outdated'] ?></div><div class="lbl">Outdated<?= $latest_version ? ' (v' . htmlspecialchars($latest_version) . ')' : '' ?></div></div>
  <div class="fleet-stat<?= $stats['expired'] ? ' warn' : '' ?>"><div class="num"><?= (int) $stats['expired'] ?></div><div class="lbl">Expired</div></div>
  <div class="fleet-stat<?= $stats['suspended'] ? ' bad' : '' ?>"><div class="num"><?= (int) $stats['suspended'] ?></div><div class="lbl">Suspended</div></div>
  <div class="fleet-stat<?= $stats['updating'] ? ' warn' : '' ?>"><div class="num"><?= (int) $stats['updating'] ?></div><div class="lbl">Updating now</div></div>
  <div class="fleet-stat<?= $stats['stuck'] ? ' bad' : '' ?>"><div class="num"><?= (int) $stats['stuck'] ?></div><div class="lbl">Stuck / failed</div></div>
</div>

<div class="row">
  <div class="col-md-7">
    <div class="mp-card fleet-card">
      <div class="mp-card-body">
        <h3><i class="fa fa-plus-circle"></i> Provision New Install</h3>
        <p class="text-muted" style="font-size:12px">
          Creates <code>name.<?= htmlspecialchars($base_domain) ?></code>, its database and a ready
          launcher on this server — send the partner one link, they click <b>Install</b>.
        </p>
        <div class="row">
          <div class="col-sm-6">
            <h4 style="font-size:12px;text-transform:uppercase;color:#78716C;letter-spacing:.4px">cPanel API <small class="text-muted" style="text-transform:none">(one-time — cPanel → Manage API Tokens)</small></h4>
            <div class="form-group"><label>API Host</label><input type="text" id="cpHost" class="form-control input-sm" value="<?= htmlspecialchars($cpanel_host) ?>"></div>
            <div class="form-group"><label>cPanel User</label><input type="text" id="cpUser" class="form-control input-sm" value="<?= htmlspecialchars($cpanel_user) ?>" placeholder="cPanel account username"></div>
            <div class="form-group"><label>API Token</label><input type="password" id="cpToken" class="form-control input-sm" placeholder="<?= $cpanel_token_set ? 'Saved — enter to change' : 'Paste API token' ?>" autocomplete="off"></div>
            <div class="form-group"><label>Base Domain</label><input type="text" id="cpDomain" class="form-control input-sm" value="<?= htmlspecialchars($base_domain) ?>"></div>
            <button class="btn btn-default btn-sm" onclick="saveCpanel()">Save API Settings</button>
          </div>
          <div class="col-sm-6">
            <h4 style="font-size:12px;text-transform:uppercase;color:#78716C;letter-spacing:.4px">New Client</h4>
            <div class="form-group">
              <label>Subdomain</label>
              <div class="fleet-key-row">
                <input type="text" id="provSlug" class="form-control input-sm" placeholder="clientname">
                <span class="btn btn-default btn-sm disabled" style="line-height:20px">.<?= htmlspecialchars($base_domain) ?></span>
              </div>
            </div>
            <div class="form-group"><label>Partner / client name <small class="text-muted">(optional)</small></label><input type="text" id="provPartner" class="form-control input-sm"></div>
            <button class="btn btn-primary" id="provBtn" onclick="provisionInstall()"><i class="fa fa-magic"></i> Provision Install</button>
            <div id="provResult" style="display:none;margin-top:12px"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-5">
    <div class="mp-card fleet-card">
      <div class="mp-card-body">
        <h3><i class="fa fa-key"></i> Deployment Keys</h3>
        <p class="text-muted" style="font-size:12px">
          The deploy key unlocks <code>fleet/package</code> for the <code>deploy.php</code> launcher.
          The fleet key authenticates install heartbeats — put it in release manifests.
        </p>
        <div class="form-group">
          <label>Deploy Key</label>
          <div class="fleet-key-row">
            <input type="text" id="deployKey" class="form-control" readonly value="<?= htmlspecialchars($deploy_key) ?>" placeholder="Not set — generate one">
            <button class="btn btn-default" type="button" onclick="rotateKey('deploy_key')">Rotate</button>
          </div>
        </div>
        <div class="form-group">
          <label>Fleet Key</label>
          <div class="fleet-key-row">
            <input type="text" id="fleetKey" class="form-control" readonly value="<?= htmlspecialchars($fleet_key) ?>" placeholder="Not set — generate one">
            <button class="btn btn-default" type="button" onclick="rotateKey('fleet_key')">Rotate</button>
          </div>
        </div>
        <p class="text-muted" style="font-size:11px">
          Rotating the deploy key instantly invalidates launchers using the old one.
          Rotating the fleet key requires shipping the new key in the next manifest.
        </p>
      </div>
    </div>

    <div class="mp-card fleet-card">
      <div class="mp-card-body">
        <h3><i class="fa fa-rocket"></i> Automated Deployment</h3>
        <p style="font-size:12px">
          Full package:
          <?php if ($package_exists): ?>
            <span class="label label-success">martpoint-full.zip (<?= $package_size ?> MB)</span>
          <?php else: ?>
            <span class="label label-warning">not built</span> — run <a href="<?= base_url('release'); ?>">Release → Build Full Package</a>
          <?php endif; ?>
        </p>
        <ol style="font-size:12px; padding-left:18px">
          <li>Generate a deploy key above.</li>
          <li>Download the launcher below and give it + the key to the engineer.</li>
          <li>Engineer uploads <code>deploy.php</code> to the client's <code>public_html</code> and opens it in a browser — it downloads, extracts and starts the install wizard automatically.</li>
        </ol>
        <a href="<?= base_url('fleet/launcher'); ?>" class="btn btn-primary"><i class="fa fa-download"></i> Download deploy.php</a>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xs-12">
    <div class="mp-card fleet-card">
      <div class="mp-card-body">
        <h3><i class="fa fa-globe"></i> Registered Installs (<?= count($installs) ?>)
          <button class="btn btn-default btn-xs pull-right" onclick="syncCronKeys()" title="Verify each install's live cron key and rewrite its cPanel cron lines (removes duplicates and stale keys)"><i class="fa fa-refresh"></i> Sync &amp; repair cron</button>
        </h3>
        <div class="fleet-bulkbar">
          <span class="fleet-sel-count" id="bulkCount">Tick installs to run a command on all of them</span>
          <select id="bulkAction" class="form-control">
            <option value="update_now">Update now</option>
            <option value="run_backup">Backup</option>
            <option value="cron">Setup cron</option>
            <option value="report_status">Report status</option>
            <option value="suspend">Suspend</option>
            <option value="resume">Resume</option>
            <option value="push_file">Push a file to ALL…</option>
          </select>
          <button class="btn btn-primary btn-sm" id="bulkApply" onclick="bulkApply()" disabled>
            <i class="fa fa-bolt"></i> Run on selected
          </button>
          <span class="fleet-bulk-progress" id="bulkProgress"></span>
        </div>
        <div class="fleet-toolbar">
          <input type="text" id="fleetSearch" class="form-control fleet-search"
                 placeholder="Search store, domain or plan…" autocomplete="off">
          <select id="fleetFilter" class="form-control">
            <option value="">All installs</option>
            <option value="attention">Needs attention</option>
            <option value="outdated">Outdated</option>
            <option value="stuck">Stuck / failed</option>
            <option value="active">Licence active</option>
            <option value="expired">Expired</option>
            <option value="suspended">Suspended</option>
          </select>
          <div class="checkbox" style="margin:0">
            <label style="font-size:12px;font-weight:600">
              <input type="checkbox" id="fleetSelAll"> Select all <span id="fleetSelScope" class="text-muted"></span>
            </label>
          </div>
          <span class="fleet-count" id="fleetCount"></span>
          <div class="fleet-pager">
            <span class="fleet-count">Show</span>
            <div class="btn-group" id="fleetPageSize">
              <button type="button" class="btn btn-default active" data-n="25">25</button>
              <button type="button" class="btn btn-default" data-n="50">50</button>
              <button type="button" class="btn btn-default" data-n="100">100</button>
            </div>
            <div class="btn-group">
              <button type="button" class="btn btn-default" id="fleetPrev"><i class="fa fa-chevron-left"></i></button>
              <button type="button" class="btn btn-default" id="fleetNext"><i class="fa fa-chevron-right"></i></button>
            </div>
            <span class="fleet-count" id="fleetPageInfo"></span>
          </div>
        </div>
        <div class="fleet-list" id="fleetList">
        <?php if (empty($installs)): ?>
          <div class="fleet-empty">No installs have phoned home yet. Heartbeats start once a manifest carries <code>fleet_url</code>.</div>
        <?php else: $licData = []; foreach ($installs as $i): $cmd = $last_commands[$i->id] ?? null;
            $licStatus = strtoupper((string) ($i->license_status ?? ''));
            $suspended = ($licStatus === 'SUSPENDED');
            $outdated = $latest_version && !empty($i->version) && version_compare($i->version, $latest_version, '<');
            $usage = !empty($i->usage_json) ? json_decode($i->usage_json, true) : [];
            $quotaOf = function($key) use ($usage) {
              foreach ((array) $usage as $q) { if (($q['key'] ?? '') === $key) return $q; }
              return null;
            };
            $uUsers = $quotaOf('user_limit');
            $uProds = $quotaOf('product_limit');
            $limits = [];
            foreach ((array) $usage as $q) {
              if (isset($q['key'], $q['limit'])) $limits[$q['key']] = (int) $q['limit'];
            }
            // The MP- key Central stores is self-contained — decode it so the
            // modal shows real plan/dates/limits even when the install's
            // heartbeat predates license reporting.
            $licDec = (!empty($i->license_code) && function_exists('decode_license_key'))
              ? decode_license_key($i->license_code) : null;
            if (is_array($licDec)) {
              foreach (['branch_limit','user_limit','product_limit','sku_limit','online_product_limit',
                        'service_limit','media_storage_limit_mb','storefront_limit','custom_domain_limit'] as $k) {
                if (!isset($limits[$k]) && isset($licDec[$k])) { $limits[$k] = (int) $licDec[$k]; }
              }
            }
            $licData[$i->id] = [
              'domain' => parse_url($i->install_url, PHP_URL_HOST) ?: $i->install_url,
              'store_name' => (string) ($i->store_name ?? ''),
              'plan_name' => (string) ($i->plan_name ?? '') ?: (string) ($licDec['plan_name'] ?? ''),
              'days_left' => ($i->days_left !== null && $i->days_left !== '') ? $i->days_left
                : (!empty($licDec['subscription_end_date']) ? (int) floor((strtotime($licDec['subscription_end_date']) - time()) / 86400) : null),
              'end_date' => (string) ($licDec['subscription_end_date'] ?? ''),
              'license_status' => $licStatus !== '' ? $licStatus
                : (!empty($licDec['subscription_end_date'])
                    ? (strtotime($licDec['subscription_end_date']) >= time() ? 'ACTIVE' : 'EXPIRED')
                    : ''),
              'license_tail' => !empty($i->license_code) ? substr($i->license_code, -8) : '',
              'whatsapp_number' => (string) ($licDec['whatsapp_number'] ?? ''),
              'renewal_amount' => (string) ($licDec['renewal_amount'] ?? ''),
              'client_name' => (string) ($licDec['client_name'] ?? ''),
              'limits' => $limits,
            ];

            // Stage → colour class + severity. Severity drives the row's
            // left edge bar and the "Needs attention" filter, so a broken
            // install is findable without reading every row.
            $stage = strtolower(trim((string) ($i->update_stage ?? '')));
            $step  = (int) ($i->update_step ?? 0);
            $stDetail = trim((string) ($i->update_detail ?? ''));
            $stMap = [
              'idle'        => ['st-idle',     'Idle',        'No update in progress',        'ok'],
              'ready'       => ['st-ready',    'Starting',    'Backing up before changes',    'busy'],
              'downloading' => ['st-progress', 'Downloading', 'Fetching changed files',       'busy'],
              'verifying'   => ['st-progress', 'Verifying',   'Checking file hashes',         'busy'],
              'applying'    => ['st-progress', 'Applying',    'Writing files',                'busy'],
              'migrating'   => ['st-progress', 'Migrating',   'Running database migrations',  'busy'],
              'finalizing'  => ['st-progress', 'Finalizing',  'Stamping the new version',     'busy'],
              'cleanup'     => ['st-progress', 'Cleanup',     'Removing temp files',          'busy'],
              'stalled'     => ['st-stalled',  'Stalled',     'No progress — will resume on next check-in', 'bad'],
              'failed'      => ['st-failed',   'Failed',      'Update stopped with an error', 'bad'],
            ];
            $st = $stMap[$stage] ?? null;
            $stCls   = $st[0] ?? 'st-unknown';
            $stLabel = $stage === '' ? 'no data' : ($st[1] ?? ucfirst($stage));
            $stTitle = $st[2] ?? $stage;

            $cmdStatus = strtolower((string) ($cmd->status ?? ''));
            $cmdText   = trim((string) ($cmd->result ?? ''));
            $needsAttention = in_array($stage, ['stalled', 'failed'], true)
                || $outdated || $licStatus === 'EXPIRED' || $suspended || $cmdStatus === 'failed';

            // Row accent: red beats amber. Uses the same severity the badge
            // shows so the colour and the word never disagree.
            $rowCls = '';
            if (in_array($stage, ['stalled', 'failed'], true) || $licStatus === 'EXPIRED' || $suspended || $cmdStatus === 'failed') {
              $rowCls = 'is-bad';
            } elseif ($outdated || $st !== null && $st[3] === 'busy') {
              $rowCls = 'is-warn';
            }

            // Search/filter haystack — built server-side so the client only
            // does a cheap indexOf, which matters at a thousand rows.
            $haystack = strtolower(implode(' ', array_filter([
              (string) ($i->store_name ?? ''), (string) $i->install_url,
              (string) ($i->plan_name ?? ''), (string) $i->version,
              (string) ($i->php_version ?? ''), $licStatus, $stage, $stLabel,
            ])));
        ?>
          <div class="fleet-item<?= $rowCls ? ' ' . $rowCls : '' ?>"
               data-hay="<?= htmlspecialchars($haystack, ENT_QUOTES) ?>"
               data-attn="<?= $needsAttention ? '1' : '0' ?>"
               data-outdated="<?= $outdated ? '1' : '0' ?>"
               data-stuck="<?= in_array($stage, ['stalled', 'failed'], true) ? '1' : '0' ?>"
               data-lic="<?= htmlspecialchars($licStatus ?: 'UNKNOWN', ENT_QUOTES) ?>">
            <div class="fleet-row <?= $rowCls ?>">
              <div class="fleet-row-main" onclick="toggleRow(this)">
                <span class="fleet-check" onclick="event.stopPropagation()">
                  <input type="checkbox" class="fleet-sel" value="<?= (int) $i->id ?>">
                </span>
                <span class="fleet-caret"><i class="fa fa-chevron-right"></i></span>

                <span class="fleet-id">
                  <span class="nm"><?= htmlspecialchars($i->store_name ?: parse_url($i->install_url, PHP_URL_HOST)) ?></span>
                  <span class="url"><?= htmlspecialchars($i->install_url) ?></span>
                </span>

                <span class="fleet-col fleet-col-ver">
                  <?= htmlspecialchars($i->version) ?>
                  <?php if ($outdated): ?><br><span class="fleet-badge info">outdated</span><?php endif; ?>
                </span>

                <span class="fleet-col fleet-col-stage">
                  <span class="fleet-stage-badge <?= $stCls ?>" title="<?= htmlspecialchars($stTitle, ENT_QUOTES) ?>">
                    <span class="dot"></span> <?= htmlspecialchars($stLabel) ?>
                    <?php if ($step > 0): ?><span style="opacity:.75"><?= $step ?>/8</span><?php endif; ?>
                  </span>
                  <?php if ($step > 0 && $step < 8): ?>
                    <span class="fleet-stage-bar"><span style="width:<?= (int) round($step / 8 * 100) ?>%"></span></span>
                  <?php endif; ?>
                </span>

                <span class="fleet-col fleet-col-seen">
                  <?php if ($suspended): ?>
                    <span class="fleet-badge off">suspended</span>
                  <?php elseif ($licStatus === 'EXPIRED'): ?>
                    <span class="fleet-badge bad">expired</span>
                  <?php elseif ($licStatus === 'EXPIRING_SOON'): ?>
                    <span class="fleet-badge soon">expiring</span>
                  <?php elseif ($licStatus === 'ACTIVE'): ?>
                    <span class="fleet-badge ok">active</span>
                  <?php else: ?>
                    <span class="fleet-badge off">unknown</span>
                  <?php endif; ?>
                  <br><span class="url"><?= htmlspecialchars($i->last_seen) ?></span>
                </span>

                <span class="fleet-quick" onclick="event.stopPropagation()">
                  <button class="btn btn-xs btn-primary" onclick="queueCmd(<?= (int) $i->id ?>, 'update_now')" title="Update now">Update</button>
                  <button class="btn btn-xs btn-default" onclick="toggleRow(this, true)" title="Show everything">More</button>
                </span>
              </div>

              <div class="fleet-row-detail" style="display:none">
                <div class="fleet-detail-grid">
                  <div class="fleet-detail-box">
                    <h5>Install</h5>
                    <div class="kv">
                      <a href="<?= htmlspecialchars($i->install_url) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($i->install_url) ?></a><br>
                      PHP <?= htmlspecialchars($i->php_version) ?>
                      <?php if (!empty($i->plan_name)): ?><br>Plan: <?= htmlspecialchars($i->plan_name) ?><?php endif; ?>
                      <?php if (!empty($i->admin_pass)): ?><br>admin: <code><?= htmlspecialchars($i->admin_pass) ?></code><?php endif; ?>
                    </div>
                  </div>

                  <div class="fleet-detail-box">
                    <h5>Update stage</h5>
                    <div class="kv">
                      <span class="fleet-stage-badge <?= $stCls ?>"><span class="dot"></span> <?= htmlspecialchars($stLabel) ?></span>
                      <?php if ($step > 0): ?> step <?= $step ?>/8<?php endif; ?>
                      <?php if ($stDetail !== ''): ?>
                        <div class="fleet-longtext" style="margin-top:5px"><?= htmlspecialchars($stDetail) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="fleet-detail-box">
                    <h5>Last command</h5>
                    <div class="kv">
                      <?php if ($cmd): ?>
                        <span class="label label-<?= $cmd->status === 'done' ? 'success' : ($cmd->status === 'failed' ? 'danger' : 'info') ?>"><?= htmlspecialchars($cmd->status) ?></span>
                        <?= htmlspecialchars((string) $cmd->command) ?>
                        <?php if ($cmdText !== ''): ?>
                          <div class="fleet-longtext" style="margin-top:5px"><?= htmlspecialchars($cmdText) ?></div>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-muted">Nothing queued yet.</span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="fleet-detail-box">
                    <h5>Licence &amp; usage</h5>
                    <div class="kv">
                      <?= $licStatus ? htmlspecialchars($licStatus) : 'unknown' ?>
                      <?php if (isset($i->days_left) && $i->days_left !== null): ?> · <?= (int) $i->days_left ?>d left<?php endif; ?>
                      <?php if ($uUsers): ?><br>Users <?= (int) $uUsers['used'] ?>/<?= (int) $uUsers['limit'] ?><?php endif; ?>
                      <?php if ($uProds): ?><br>Products <?= (int) $uProds['used'] ?>/<?= (int) $uProds['limit'] ?><?php endif; ?>
                      <?php if (!empty($i->cron_key)): ?><br>Cron key set<?php endif; ?>
                    </div>
                  </div>
                </div>

                <div class="fleet-detail-actions">
                  <button class="btn btn-xs btn-primary" onclick="queueCmd(<?= (int) $i->id ?>, 'update_now')">Update now</button>
                  <button class="btn btn-xs btn-success" onclick="openLicenseModal(<?= (int) $i->id ?>)">License</button>
                  <?php if ($suspended): ?>
                    <button class="btn btn-xs btn-default" onclick="toggleSuspend(<?= (int) $i->id ?>, 'resume')">Resume</button>
                  <?php else: ?>
                    <button class="btn btn-xs btn-warning" onclick="toggleSuspend(<?= (int) $i->id ?>, 'suspend')">Suspend</button>
                  <?php endif; ?>
                  <button class="btn btn-xs btn-default" onclick="reportStatus(<?= (int) $i->id ?>)"><i class="fa fa-refresh"></i> Report status</button>
                  <button class="btn btn-xs btn-default" onclick="openEmailModal(<?= (int) $i->id ?>)">Email</button>
                  <button class="btn btn-xs btn-default" onclick="openSettingsModal(<?= (int) $i->id ?>)"><i class="fa fa-sliders"></i> Settings</button>
                  <button class="btn btn-xs btn-default" onclick="queueCmd(<?= (int) $i->id ?>, 'run_backup')" title="Runs a full DB backup on the install"><i class="fa fa-database"></i> Backup</button>
                  <button class="btn btn-xs btn-default" onclick="openFileModal(<?= (int) $i->id ?>)" title="Push a single file to this install (hotfix)"><i class="fa fa-upload"></i> File</button>
                  <?php if (!empty($i->cron_scheduled)): ?>
                    <span class="fleet-badge ok">cron &#10003;</span>
                  <?php else: ?>
                    <button class="btn btn-xs btn-default" onclick="setupCron(<?= (int) $i->id ?>)">Cron</button>
                  <?php endif; ?>
                  <button class="btn btn-xs btn-default" onclick="editCronKey(<?= (int) $i->id ?>, '<?= htmlspecialchars((string) ($i->cron_key ?? ''), ENT_QUOTES) ?>')" title="Save the install's cron key so wake pings work"><i class="fa fa-key"></i></button>
                  <button class="btn btn-xs btn-danger pull-right" onclick="removeInstall(<?= (int) $i->id ?>, this)"><i class="fa fa-trash"></i> Remove</button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; endif; ?>
        </div>
        <p class="text-muted" style="font-size:11px;margin-top:10px">Click a row to expand it. Commands are queued — each install picks them up on its next check-in (cron or admin login). Suspend blocks every page on the install except the dashboard until you Resume. No client login is ever needed.</p>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="licenseModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:480px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Activate License on Install</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="licInstallId">
        <div id="licCurrent" style="background:#f7f9fc;border:1px solid #e3e8f0;border-radius:6px;padding:10px 12px;margin-bottom:12px;font-size:12px"></div>
        <div class="form-group">
          <label>Domain <small class="text-muted">— key locks to this install</small></label>
          <input type="text" id="licDomain" class="form-control" readonly style="background:#f5f5f5">
        </div>
        <div class="form-group">
          <label>Plan</label>
          <select id="licPlanCode" class="form-control">
            <?php if (empty($plans)): ?><option value="basic">Basic — 1 branch, 5 users, 500 products</option><?php endif; ?>
            <?php foreach ($plans as $p): ?>
              <option value="<?= htmlspecialchars($p->plan_code) ?>"><?= htmlspecialchars($p->plan_name) ?>
                — <?= (int) $p->branch_limit ?> branch<?= $p->branch_limit == 1 ? '' : 'es' ?>,
                <?= (int) $p->user_limit ?> users,
                <?= number_format((int) $p->product_limit) ?> products</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row">
          <div class="col-xs-6"><div class="form-group"><label>Start date</label><input type="date" id="licStart" class="form-control" readonly style="background:#f5f5f5"></div></div>
          <div class="col-xs-6"><div class="form-group"><label>End date</label><input type="date" id="licEnd" class="form-control"></div></div>
        </div>
        <div class="row">
          <div class="col-xs-6"><div class="form-group"><label>Support WhatsApp Number</label><input type="text" id="licWhatsapp" class="form-control" placeholder="e.g. 2348012345678"></div></div>
          <div class="col-xs-6"><div class="form-group"><label>Renewal Amount</label><input type="text" id="licRenewal" class="form-control" placeholder="e.g. 50000"></div></div>
        </div>
        <div class="form-group">
          <label>Client / Store Name</label>
          <input type="text" id="licClientName" class="form-control" placeholder="Client or store name">
        </div>
        <div class="form-group" style="margin-bottom:6px">
          <div class="checkbox" style="margin:0">
            <label style="font-weight:600">
              <input type="checkbox" id="licOverride"> Vendor override — approve custom limits
            </label>
          </div>
          <small class="text-muted">Unchecked = plan limits apply exactly. Checked = you approve custom values.</small>
        </div>
        <div class="row">
          <div class="col-xs-4"><div class="form-group"><label>Branches</label><input type="number" id="lic_branch_limit" class="form-control lic-q" min="1" readonly></div></div>
          <div class="col-xs-4"><div class="form-group"><label>Users</label><input type="number" id="lic_user_limit" class="form-control lic-q" min="1" readonly></div></div>
          <div class="col-xs-4"><div class="form-group"><label>Products</label><input type="number" id="lic_product_limit" class="form-control lic-q" min="1" readonly></div></div>
        </div>
        <div class="row">
          <div class="col-xs-4"><div class="form-group"><label>SKUs</label><input type="number" id="lic_sku_limit" class="form-control lic-q" min="1" readonly></div></div>
          <div class="col-xs-4"><div class="form-group"><label>Online products</label><input type="number" id="lic_online_product_limit" class="form-control lic-q" min="1" readonly></div></div>
          <div class="col-xs-4"><div class="form-group"><label>Services</label><input type="number" id="lic_service_limit" class="form-control lic-q" min="1" readonly></div></div>
        </div>
        <div class="row">
          <div class="col-xs-4"><div class="form-group"><label>Storage (MB)</label><input type="number" id="lic_media_storage_limit_mb" class="form-control lic-q" min="1" readonly></div></div>
          <div class="col-xs-4"><div class="form-group"><label>Storefronts</label><input type="number" id="lic_storefront_limit" class="form-control lic-q" min="1" readonly></div></div>
          <div class="col-xs-4"><div class="form-group"><label>Custom domains</label><input type="number" id="lic_custom_domain_limit" class="form-control lic-q" min="1" readonly></div></div>
        </div>
        <div class="form-group" style="border-top:1px solid #eee; padding-top:12px">
          <label>OTP Code <span class="text-danger">*</span></label>
          <div style="display:flex; gap:8px; align-items:stretch">
            <input type="text" id="licOtp" class="form-control" placeholder="6-character OTP" maxlength="6"
                   style="text-transform:uppercase; letter-spacing:3px; font-weight:600; text-align:center; font-size:16px">
            <button type="button" class="btn btn-primary" id="licOtpBtn" onclick="requestLicenseOtp()" style="white-space:nowrap">
              <i class="fa fa-key"></i> Request OTP
            </button>
          </div>
          <p class="help-block" id="licOtpStatus" style="margin-bottom:2px">Central emails the OTP to the authorized vendor address. Expires in 10 minutes.</p>
        </div>
        <p class="text-muted" style="font-size:11px">Generates the same domain-locked MP- key the Subscription page produces — the install verifies the OTP, decodes the key, and activates it on its domain on the next check-in.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default btn-sm pull-left" onclick="refreshLicense()" title="Ask the install to report its current license now"><i class="fa fa-refresh"></i> Refresh</button>
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-success" onclick="pushLicense()">Activate License</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="emailModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:440px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Push Email Settings to Install</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="emInstallId">
        <div class="form-group">
          <label>Provider</label>
          <select id="emProvider" class="form-control">
            <option value="resend">Resend (API)</option>
            <option value="smtp">SMTP</option>
          </select>
        </div>
        <div id="emResendBox">
          <div class="form-group"><label>Resend API key</label><input type="text" id="emResendKey" class="form-control" placeholder="re_xxxxxxxxx" autocomplete="off"></div>
          <div class="row">
            <div class="col-xs-6"><div class="form-group"><label>From email</label><input type="text" id="emResendFrom" class="form-control" placeholder="noreply@martpoint.com.ng"></div></div>
            <div class="col-xs-6"><div class="form-group"><label>From name</label><input type="text" id="emResendName" class="form-control" placeholder="MartPoint"></div></div>
          </div>
        </div>
        <div id="emSmtpBox" style="display:none">
          <div class="row">
            <div class="col-xs-8"><div class="form-group"><label>SMTP host</label><input type="text" id="emSmtpHost" class="form-control"></div></div>
            <div class="col-xs-4"><div class="form-group"><label>Port</label><input type="text" id="emSmtpPort" class="form-control" value="587"></div></div>
          </div>
          <div class="row">
            <div class="col-xs-6"><div class="form-group"><label>User</label><input type="text" id="emSmtpUser" class="form-control" autocomplete="off"></div></div>
            <div class="col-xs-6"><div class="form-group"><label>Password</label><input type="password" id="emSmtpPass" class="form-control" autocomplete="new-password"></div></div>
          </div>
          <div class="form-group"><label>Encryption</label>
            <select id="emSmtpCrypto" class="form-control"><option value="tls">TLS</option><option value="ssl">SSL</option><option value="">None</option></select>
          </div>
        </div>
        <p class="text-muted" style="font-size:11px">Queued like a license — the install writes these to its email settings on the next check-in. Blank fields are left unchanged.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-success" onclick="pushEmail()">Queue Email Settings</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="settingsModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:480px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Push Settings to Install</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="stInstallId">
        <div class="form-group">
          <label>Settings group</label>
          <select id="stScope" class="form-control">
            <option value="assist">MartPoint Assist — AI Understanding</option>
            <option value="paystack">Paystack</option>
            <option value="monnify">Monnify</option>
            <option value="nin">NIN / BVN Verification</option>
            <option value="debt_reminder">Debt Reminders</option>
            <option value="audit">Audit Trail</option>
          </select>
        </div>

        <div class="st-section" data-scope="assist">
          <div class="checkbox"><label><input type="checkbox" id="st_assist_ai_enabled" data-field="assist_ai_enabled"> Enable AI understanding</label></div>
          <div class="form-group"><label>Provider</label>
            <select class="form-control" data-field="assist_ai_provider">
              <option value="groq">Groq (free tier)</option>
              <option value="gemini">Google Gemini (free tier)</option>
              <option value="openai">OpenAI</option>
              <option value="custom">Custom OpenAI-compatible endpoint</option>
            </select></div>
          <div class="form-group"><label>API key</label><input type="password" class="form-control" data-field="assist_ai_key" autocomplete="new-password"></div>
          <div class="row">
            <div class="col-xs-7"><div class="form-group"><label>Endpoint <small class="text-muted">(custom only)</small></label><input type="text" class="form-control" data-field="assist_ai_endpoint" placeholder="https://api.groq.com/openai/v1"></div></div>
            <div class="col-xs-5"><div class="form-group"><label>Model</label><input type="text" class="form-control" data-field="assist_ai_model" placeholder="llama-3.1-8b-instant"></div></div>
          </div>
        </div>

        <div class="st-section" data-scope="paystack" style="display:none">
          <div class="row">
            <div class="col-xs-6"><div class="checkbox"><label><input type="checkbox" data-field="enabled"> Enable Paystack</label></div></div>
            <div class="col-xs-6"><div class="checkbox"><label><input type="checkbox" data-field="test_mode" checked> Test mode</label></div></div>
          </div>
          <div class="form-group"><label>Public key</label><input type="text" class="form-control" data-field="public_key" placeholder="pk_…"></div>
          <div class="form-group"><label>Secret key</label><input type="password" class="form-control" data-field="secret_key" autocomplete="new-password" placeholder="sk_…"></div>
          <div class="form-group"><label>Webhook secret <small class="text-muted">(optional)</small></label><input type="text" class="form-control" data-field="webhook_secret"></div>
        </div>

        <div class="st-section" data-scope="monnify" style="display:none">
          <div class="row">
            <div class="col-xs-6"><div class="checkbox"><label><input type="checkbox" data-field="enabled"> Enable Monnify</label></div></div>
            <div class="col-xs-6"><div class="checkbox"><label><input type="checkbox" data-field="test_mode" checked> Sandbox mode</label></div></div>
          </div>
          <div class="row">
            <div class="col-xs-6"><div class="form-group"><label>API key</label><input type="text" class="form-control" data-field="api_key" placeholder="MK_PROD_…"></div></div>
            <div class="col-xs-6"><div class="form-group"><label>Contract code</label><input type="text" class="form-control" data-field="contract_code"></div></div>
          </div>
          <div class="form-group"><label>Secret key</label><input type="password" class="form-control" data-field="secret_key" autocomplete="new-password"></div>
          <div class="row">
            <div class="col-xs-6"><div class="form-group"><label>Wallet account no.</label><input type="text" class="form-control" data-field="wallet_account_number"></div></div>
            <div class="col-xs-6"><div class="checkbox" style="margin-top:28px"><label><input type="checkbox" data-field="disbursements_enabled"> Disbursements</label></div></div>
          </div>
        </div>

        <div class="st-section" data-scope="nin" style="display:none">
          <div class="checkbox"><label><input type="checkbox" data-field="nin_api_enabled"> Enable NIN/BVN verification</label></div>
          <div class="form-group"><label>Provider</label>
            <select class="form-control" data-field="nin_api_provider">
              <option value="ninbvnportal">NIN/BVN Portal</option>
              <option value="interswitch">Interswitch</option>
            </select></div>
          <div class="form-group"><label>API URL</label><input type="text" class="form-control" data-field="nin_api_url" placeholder="https://api.example.com/v1/verify"></div>
          <div class="form-group"><label>API key</label><input type="password" class="form-control" data-field="nin_api_key" autocomplete="new-password"></div>
        </div>

        <div class="st-section" data-scope="debt_reminder" style="display:none">
          <div class="checkbox"><label><input type="checkbox" data-field="enabled" checked> Enable debt reminders</label></div>
          <div class="row">
            <div class="col-xs-6"><div class="form-group"><label>Frequency</label>
              <select class="form-control" data-field="frequency">
                <option value="daily">Daily</option>
                <option value="3days">Every 3 days</option>
                <option value="weekly" selected>Weekly</option>
                <option value="biweekly">Every 2 weeks</option>
                <option value="monthly">Monthly</option>
              </select></div></div>
            <div class="col-xs-6"><div class="form-group"><label>Max reminders <small class="text-muted">(0 = unlimited)</small></label><input type="number" class="form-control" data-field="max_reminders" value="0" min="0"></div></div>
          </div>
          <div class="row">
            <div class="col-xs-6"><div class="checkbox"><label><input type="checkbox" data-field="send_email" checked> Send by email</label></div></div>
            <div class="col-xs-6"><div class="checkbox"><label><input type="checkbox" data-field="send_sms"> Send by SMS</label></div></div>
          </div>
        </div>

        <div class="st-section" data-scope="audit" style="display:none">
          <div class="checkbox"><label><input type="checkbox" data-field="audit_trail_enabled" checked> Record audit trail (logins, edits, deletions, settings changes)</label></div>
          <p class="text-muted" style="font-size:11px">Turning this off stops new audit entries; existing history is kept.</p>
        </div>

        <p class="text-muted" style="font-size:11px">Queued like a license — the install writes these to its own database on the next check-in. Only the fields you fill are sent.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-success" onclick="pushSettings()">Push Settings</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="fileModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:460px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Push File to Install</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="pfInstallId">
        <div class="form-group">
          <label>File on this computer</label>
          <input type="file" id="pfFile" class="form-control">
        </div>
        <div class="form-group">
          <label>Destination path on the install <small class="text-muted">— relative to the install root</small></label>
          <input type="text" id="pfPath" class="form-control" placeholder="application/models/Roles_model.php">
        </div>
        <p class="text-muted" style="font-size:11px">
          Only paths under <code>application/</code> or <code>theme/</code> are accepted — <code>config.php</code>,
          <code>database.php</code>, <code>installed.lock</code> and other protected files are refused on the
          install side too. One file per push; repeat for multiple files. Max ~900KB.
        </p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-success" onclick="pushFile()">Queue File Push</button>
      </div>
    </div>
  </div>
</div>

  <!-- Push ONE file to MANY installs — the recovery path when installs are
       stuck on an old Updater.php and cannot update themselves. -->
  <div class="modal fade" id="bulkFileModal" tabindex="-1">
    <div class="modal-dialog" style="max-width:520px">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Push One File to Selected Installs</h4>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning" style="font-size:12px">
            Use this to place a file directly on installs that <b>cannot update themselves</b>
            (e.g. an old <code>Updater.php</code>). Each install applies it on its next check-in.
          </div>
          <div class="form-group">
            <label>File on this computer</label>
            <input type="file" id="bfFile" class="form-control">
          </div>
          <div class="form-group">
            <label>Destination path <small class="text-muted">— relative to the install root</small></label>
            <input type="text" id="bfPath" class="form-control" value="application/libraries/Updater.php">
          </div>
          <p class="text-muted" style="font-size:11px">
            Only <code>application/</code> or <code>theme/</code> paths are accepted.
            <code>config.php</code>, <code>database.php</code> and <code>installed.lock</code>
            are refused on the install side too. One file per action, max ~900&nbsp;KB.
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button class="btn btn-warning" onclick="bulkPushFile()">
            <i class="fa fa-upload"></i> Push to All Selected
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog" style="max-width:420px">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Suspend Install</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" id="susInstallId">
          <div class="form-group"><label>Reason (optional)</label><input type="text" id="susReason" class="form-control" placeholder="e.g. Subscription payment overdue"></div>
          <p class="text-muted" style="font-size:11px">The install keeps its data but every page is locked except the dashboard, until you Resume it.</p>
        </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-warning" onclick="doSuspend()">Queue Suspend</button>
      </div>
    </div>
  </div>
</div>

<script>
function rotateKey(which) {
  if (!confirm('Rotate ' + which.replace('_', ' ') + '? The old value stops working immediately.')) return;
  fleetPost('<?= base_url('fleet/rotate_key'); ?>', { which: which }, function(res) {
    if (res.status === 'ok') {
      if (which === 'deploy_key') $('#deployKey').val(res.key);
      else $('#fleetKey').val(res.key);
      toastr.success('New key generated.');
    } else {
      toastr.error(res.message || 'Failed');
    }
  }, function() { toastr.error('Server error'); });
}

function fleetPost(url, data, ok, fail) {
  data = data || {};
  if (window.csrfName) data[window.csrfName] = window.csrfHash;
  return $.post(url, data, ok, 'json').fail(fail);
}

function syncCronKeys() {
  toastr.info('Verifying keys against each install and repairing cPanel cron lines… this can take a minute.');
  fleetPost('<?= base_url('fleet/sync_cron_keys'); ?>', {}, function(res) {
    if (res.status === 'ok') { toastr.success(res.message, '', { timeOut: 10000 }); setTimeout(function(){ location.reload(); }, 2500); }
    else if (res.status === 'warning') { toastr.warning(res.message, '', { timeOut: 20000 }); setTimeout(function(){ location.reload(); }, 4000); }
    else toastr.error(res.message || 'Failed');
  }, function() { toastr.error('Server error'); });
}

function editCronKey(id, current) {
  var key = prompt('Cron key for this install (from its config.php cron_secret_key).', current || '');
  if (key === null) return;
  fleetPost('<?= base_url('fleet/save_cron_key'); ?>', { install_id: id, cron_key: $.trim(key) }, function(res) {
    if (res.status === 'ok') { toastr.success(res.message); setTimeout(function(){ location.reload(); }, 1200); }
    else toastr.error(res.message || 'Failed');
  }, function() { toastr.error('Server error'); });
}

function queueCmd(id, command, extra) {
  fleetPost('<?= base_url('fleet/queue'); ?>', $.extend({ install_id: id, command: command }, extra || {}), function(res) {
    if (res.status === 'ok') {
      onQueued(id, command, res);
    } else {
      toastr.error(res.message || 'Failed');
    }
  }, function() { toastr.error('Server error'); });
}

/**
 * Ask ONE install to report its version + migration count, then poll for the
 * answer and reload so the Status column shows it. Central can only ask and
 * wait — the install answers on its next check-in — so this polls briefly and
 * says so plainly rather than pretending the result is instant.
 */
function reportStatus(id) {
  queueCmd(id, 'report_status');
  toastr.info('Asked the install to report — waiting for it to check in…');
  var tries = 0, maxTries = 20;   // ~2 minutes at 6s intervals
  var poll = setInterval(function() {
    tries++;
    fleetPost('<?= base_url('fleet/status'); ?>', { install_id: id }, function(res) {
      // Reload as soon as the install has answered, so the new result renders
      // in the Status column without the user hunting for it.
      if (res && res.answered) {
        clearInterval(poll);
        location.reload();
      }
    }, function() { /* keep polling */ });
    if (tries >= maxTries) {
      clearInterval(poll);
      toastr.warning('No reply yet — the install has not checked in. Status will appear once it does.');
    }
  }, 6000);
}

/* ------------------------------------------------------------------ */
/*  Installs list — expand / search / filter / page                   */
/* ------------------------------------------------------------------ */

/**
 * Expand one install's detail in place.
 *
 * Only the panel is toggled — rows are never rebuilt — so expanding is
 * instant no matter how many installs there are. Multiple rows may be open
 * at once: comparing two installs is a real thing to want.
 *
 * Pass force=true from the "More" button so it opens even if the click
 * already bubbled and toggled it.
 */
function toggleRow(el, force) {
  var $row = $(el).closest('.fleet-row');
  var $detail = $row.find('.fleet-row-detail');
  var open = $detail.is(':visible');
  var next = (force === true) ? true : !open;
  $detail.toggle(next);
  $row.toggleClass('is-open', next);
}

var fleetPage = 1, fleetPageSize = 25;

/**
 * Render the list for the current search + filter + page.
 *
 * Filtering happens on data-* attributes rather than re-parsing the DOM,
 * so it stays O(n) with n cheap comparisons — this has to remain usable
 * with a thousand installs.
 */
function fleetRender() {
  var q = $.trim($('#fleetSearch').val()).toLowerCase();
  var f = $('#fleetFilter').val();
  var $items = $('#fleetList .fleet-item');
  var matched = [];

  $items.each(function () {
    var $i = $(this);
    var ok = true;
    if (q && $i.data('hay').indexOf(q) === -1) ok = false;
    if (ok && f) {
      switch (f) {
        case 'attention': ok = $i.data('attn') == 1; break;
        case 'outdated':  ok = $i.data('outdated') == 1; break;
        case 'stuck':     ok = $i.data('stuck') == 1; break;
        case 'active':    ok = $i.data('lic') === 'ACTIVE'; break;
        case 'expired':   ok = $i.data('lic') === 'EXPIRED'; break;
        case 'suspended': ok = $i.data('lic') === 'SUSPENDED'; break;
      }
    }
    $i.data('match', ok);
    if (ok) matched.push(this);
    else $i.hide();
  });

  // Page the matched set. Collapse any expanded rows on a page change so
  // the page opens at a predictable, scannable height.
  var total = matched.length;
  var pages = Math.max(1, Math.ceil(total / fleetPageSize));
  if (fleetPage > pages) fleetPage = pages;
  var start = (fleetPage - 1) * fleetPageSize;
  var end = Math.min(start + fleetPageSize, total);

  $.each(matched, function (idx) {
    var show = idx >= start && idx < end;
    var $i = $(this);
    $i.toggle(show);
    if (!show) {
      $i.find('.fleet-row-detail').hide();
      $i.find('.fleet-row').removeClass('is-open');
    }
  });

  $('#fleetCount').text(
    total === $items.length
      ? total + ' install' + (total === 1 ? '' : 's')
      : total + ' of ' + $items.length + ' shown'
  );
  $('#fleetPageInfo').text(total ? (start + 1) + '–' + end + ' of ' + total : '');
  $('#fleetPrev').prop('disabled', fleetPage <= 1);
  $('#fleetNext').prop('disabled', fleetPage >= pages);
  $('#fleetSelAll').prop('checked', false);
  bulkRefresh();
}

$(document).on('input', '#fleetSearch', function () { fleetPage = 1; fleetRender(); });
$(document).on('change', '#fleetFilter', function () { fleetPage = 1; fleetRender(); });

$('#fleetPageSize').on('click', 'button', function () {
  $('#fleetPageSize button').removeClass('active');
  $(this).addClass('active');
  fleetPageSize = parseInt($(this).data('n'), 10) || 25;
  fleetPage = 1;
  fleetRender();
});
$('#fleetPrev').on('click', function () { if (fleetPage > 1) { fleetPage--; fleetRender(); } });
$('#fleetNext').on('click', function () { fleetPage++; fleetRender(); });

/* ------------------------------------------------------------------ */
/*  Bulk actions — tick installs, pick a command, run it on all of them */
/* ------------------------------------------------------------------ */

function fleetSelIds() {
  return $('.fleet-sel:checked').map(function() { return parseInt(this.value, 10); }).get();
}

// "Select all" means all VISIBLE installs — ticking it must not silently
// queue a command on rows hidden by a search, a filter, or another page.
function fleetVisibleItems() {
  return $('#fleetList .fleet-item').filter(function () { return $(this).is(':visible'); });
}

function bulkRefresh() {
  var n = fleetSelIds().length, total = $('.fleet-sel').length;
  $('#bulkApply').prop('disabled', n === 0);
  $('#bulkCount').text(n ? n + ' of ' + total + ' selected' : 'Tick installs to run a command on all of them');

  // "Select all" is scoped to what is VISIBLE. The label states the scope
  // explicitly, because ticking a box that quietly queued commands on
  // filtered-out installs would be the worst kind of surprise in a tool
  // that can suspend a client's store.
  var vis = fleetVisibleItems().length;
  $('#fleetSelAll').prop('checked', vis > 0 && n >= vis);
  $('#fleetSelScope').text(vis && vis < total ? '(this page: ' + vis + ')' : '');
}

$(document).on('change', '.fleet-sel', bulkRefresh);
$('#fleetSelAll').on('change', function() {
  var on = this.checked;
  fleetVisibleItems().find('.fleet-sel').prop('checked', on);
  bulkRefresh();
});

var bulkSuspend = false;

function bulkApply() {
  var ids = fleetSelIds();
  if (!ids.length) return;
  var cmd = $('#bulkAction').val();
  if (cmd === 'suspend') {
    bulkSuspend = true;
    $('#suspendModal').modal('show');
    return;
  }
  // Pushing one file to every selected install — the recovery path when
  // installs are stuck on an old Updater.php and cannot update themselves.
  if (cmd === 'push_file') {
    bulkPushIds = ids;
    $('#bfFile').val('');
    $('#bfPath').val('application/libraries/Updater.php');
    $('#bulkFileModal').modal('show');
    return;
  }
  var labels = {
    update_now: 'Update now', run_backup: 'Backup', cron: 'Setup cron',
    report_status: 'Report status', resume: 'Resume', push_file: 'Push file'
  };
  if (!confirm('Run "' + (labels[cmd] || cmd) + '" on ' + ids.length + ' selected install(s)?')) return;
  runBulk(cmd, ids, {});
}

/**
 * Push ONE file to MANY installs in a single action.
 *
 * This is the escape hatch for installs stuck on an old Updater.php: they
 * cannot complete an update to fetch the fix, so Central has to place the file
 * directly. Doing that site-by-site is what made a fleet-wide problem
 * unbearable — this queues the same push to every selected install and reports
 * how many were accepted.
 */
var bulkPushIds = [];
function bulkPushFile() {
  var path = $.trim($('#bfPath').val());
  var f = document.getElementById('bfFile').files[0];
  if (!f) { toastr.error('Choose a file.'); return; }
  if (!path) { toastr.error('Enter the destination path on the install.'); return; }
  if (!/^(application|theme)\/[\w\-./]+$/i.test(path) || path.indexOf('..') !== -1) {
    toastr.error('Path must be under application/ or theme/ (e.g. application/libraries/Updater.php).');
    return;
  }
  if (f.size > 900 * 1024) { toastr.error('File too large — max ~900KB per push.'); return; }
  if (!bulkPushIds.length) { toastr.error('No installs selected.'); return; }
  if (!confirm('Push "' + path + '" to ' + bulkPushIds.length + ' install(s)?\n\n'
      + 'Each install applies it on its next check-in.')) return;
  var r = new FileReader();
  r.onload = function () {
    var b64 = (r.result || '').split(',')[1] || '';
    if (!b64) { toastr.error('Could not read the file.'); return; }
    $('#bulkFileModal').modal('hide');
    runBulk('push_file', bulkPushIds, { path: path, content_b64: b64 });
  };
  r.onerror = function () { toastr.error('Could not read the file.'); };
  r.readAsDataURL(f);
}

// Chunked so thousands of installs never hit request timeouts — commands go
// 50-at-a-time, cron setup (cPanel API calls per install) goes 5-at-a-time.
function runBulk(cmd, ids, extra) {
  var size = cmd === 'cron' ? 5 : 50;
  var done = 0, queued = 0, woke = 0, errs = [];
  $('#bulkApply').prop('disabled', true);
  $('#bulkProgress').html('<i class="fa fa-spinner fa-spin"></i> 0/' + ids.length);
  function step() {
    var chunk = ids.slice(done, done + size);
    if (!chunk.length) { finish(); return; }
    fleetPost('<?= base_url('fleet/queue_bulk'); ?>', $.extend({ command: cmd, install_ids: chunk }, extra || {}), function(res) {
      if (res.status === 'ok') {
        queued += res.queued || 0;
        woke += res.woke || 0;
        errs = errs.concat(res.errors || []);
        done += chunk.length;
        $('#bulkProgress').html('<i class="fa fa-spinner fa-spin"></i> ' + done + '/' + ids.length);
        step();
      } else {
        errs.unshift(res.message || 'Failed');
        finish();
      }
    }, function() {
      errs.unshift('Server error — ' + done + ' of ' + ids.length + ' processed.');
      finish();
    });
  }
  function finish() {
    $('#bulkProgress').text('');
    bulkRefresh();
    var msg = queued + ' install(s) queued — ' + woke + ' woken now, the rest run on their next check-in.';
    if (errs.length) {
      toastr.warning(msg + '<br>' + $('<i>').text(errs.slice(0, 3).join(' · ')).html(), '', { timeOut: 20000 });
    } else {
      toastr.success(msg, '', { timeOut: 8000 });
    }
    setTimeout(function() { location.reload(); }, 2500);
  }
  step();
}

// Shared post-queue handling: honest colour (woke vs. not), then watch for
// the install's confirmation.
function onQueued(id, command, res) {
  if (res.woke === false) { toastr.warning(res.message, '', { timeOut: 12000 }); }
  else { toastr.info(res.message); }
  watchCommand(id, command, res.woke !== false);
}

// Poll command_status until the install reports done/failed — the click was
// always silent before, so failures looked identical to success. Bounded at
// ~2min when woken (the install answers in seconds); ~5min otherwise.
//
// 'resumed' is a live update, not a finished one: the install made progress
// and Central re-queued the next slice. Keep watching rather than declaring
// success, so a multi-slice update is visible as it advances.
function watchCommand(id, command, woke) {
  var tries = 0, max = woke ? 40 : 100, label = command.replace(/_/g, ' ');
  var t = setInterval(function() {
    tries++;
    $.getJSON('<?= base_url('fleet/command_status'); ?>', { install_id: id, command: command }, function(r) {
      if (r.command_status === 'done' || r.command_status === 'failed') {
        clearInterval(t);
        var msg = label + ': ' + (r.result || r.command_status);
        if (r.command_status === 'done') { toastr.success(msg, 'Confirmed by install', { timeOut: 8000 }); }
        else { toastr.error(msg, 'Install reported a failure', { timeOut: 15000 }); }
        setTimeout(function(){ location.reload(); }, 2500);
      } else if (r.command_status === 'resumed') {
        // Progress, not completion — say so instead of faking a finish.
        toastr.info(label + ' is still running — ' + (r.result || 'in progress') + '\nCentral continued it automatically.', '', { timeOut: 6000 });
      } else if (tries >= max) {
        clearInterval(t);
        toastr.warning(label + ' is still pending — the install has not confirmed yet. It will run on its next cron check-in; reload this page later to see the result.', '', { timeOut: 15000 });
      }
    });
  }, 3000);
}

function toggleSuspend(id, action) {
  if (action === 'resume') {
    if (!confirm('Resume this install? Its pages unlock on the next check-in.')) return;
    queueCmd(id, 'resume');
    return;
  }
  bulkSuspend = false;
  $('#susInstallId').val(id);
  $('#suspendModal').modal('show');
}

function doSuspend() {
  var bulk = bulkSuspend;
  bulkSuspend = false;
  $('#suspendModal').modal('hide');
  if (bulk) {
    runBulk('suspend', fleetSelIds(), { reason: $('#susReason').val() });
    return;
  }
  queueCmd($('#susInstallId').val(), 'suspend', { reason: $('#susReason').val() });
}

$('#suspendModal').on('hidden.bs.modal', function() { bulkSuspend = false; });

var licPlans = <?= json_encode(array_map(function($p) {
    return [
        'code'  => $p->plan_code,
        'name'  => $p->plan_name,
        'branch_limit'           => (int) $p->branch_limit,
        'user_limit'             => (int) $p->user_limit,
        'product_limit'          => (int) $p->product_limit,
        'sku_limit'              => (int) $p->sku_limit,
        'online_product_limit'   => (int) $p->online_product_limit,
        'service_limit'          => (int) $p->service_limit,
        'media_storage_limit_mb' => (int) $p->media_storage_limit_mb,
        'storefront_limit'       => (int) $p->storefront_limit,
        'custom_domain_limit'    => (int) $p->custom_domain_limit,
    ];
}, $plans), JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function licFillPlan() {
  var code = $('#licPlanCode').val();
  var plan = licPlans.find(function(p){ return p.code === code; }) || licPlans[0];
  if (!plan) return;
  ['branch_limit','user_limit','product_limit','sku_limit','online_product_limit',
   'service_limit','media_storage_limit_mb','storefront_limit','custom_domain_limit']
    .forEach(function(k){ $('#lic_' + k).val(plan[k]); });
}

function licToggleOverride() {
  var on = $('#licOverride').is(':checked');
  $('.lic-q').prop('readonly', !on).css('background', on ? '' : '#f5f5f5');
  if (!on) licFillPlan();
}

// What each install last reported via heartbeat — the "current license"
// shown in the modal before you decide to push a new one.
var INSTALL_LIC = <?= json_encode($licData ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function openLicenseModal(installId) {
  var cur = INSTALL_LIC[installId] || {};
  $('#licInstallId').val(installId);
  $('#licDomain').val(cur.domain || '');
  $('#licStart').val(new Date().toISOString().slice(0, 10));
  $('#licOtp').val('');
  $('#licOtpStatus').text('');
  $('#licClientName').val(cur.client_name || cur.store_name || '');
  $('#licWhatsapp').val(cur.whatsapp_number || '');
  $('#licRenewal').val(cur.renewal_amount || '');

  // Current-license summary box
  var summary;
  if (cur.license_status || cur.plan_name) {
    var badge = cur.license_status === 'ACTIVE' ? 'ok' : (cur.license_status === 'EXPIRED' ? 'bad' : 'soon');
    summary = '<b>Current:</b> <span class="fleet-badge ' + badge + '">' +
      (cur.license_status || 'unknown').toLowerCase() + '</span> ' +
      (cur.plan_name || '—') +
      (cur.days_left !== null && cur.days_left !== undefined ? ' · ' + cur.days_left + 'd left' : '') +
      (cur.license_tail ? ' · <code>…' + cur.license_tail + '</code>' : '') +
      '<br><small class="text-muted">As of last check-in — Refresh asks the install to report now.</small>';
  } else {
    summary = '<span class="text-muted">No license reported yet — pick a plan below to activate one.</span>';
  }
  $('#licCurrent').html(summary);

  // Preselect the plan matching the install's current plan_name
  var matched = null;
  if (cur.plan_name) {
    matched = licPlans.find(function(p){ return p.name.toLowerCase() === cur.plan_name.toLowerCase(); });
    if (matched) $('#licPlanCode').val(matched.code);
  }
  licFillPlan();

  // Prefill quotas from what the install actually has — if its limits differ
  // from the plan defaults it's a custom license → auto-tick override so
  // the values aren't clobbered.
  var custom = false;
  if (cur.limits && Object.keys(cur.limits).length) {
    ['branch_limit','user_limit','product_limit','sku_limit','online_product_limit',
     'service_limit','media_storage_limit_mb','storefront_limit','custom_domain_limit']
      .forEach(function(k){
        if (cur.limits[k] !== undefined) {
          $('#lic_' + k).val(cur.limits[k]);
          var plan = licPlans.find(function(p){ return p.code === $('#licPlanCode').val(); });
          if (plan && plan[k] !== cur.limits[k]) custom = true;
        }
      });
  }
  $('#licOverride').prop('checked', custom);
  licToggleOverride();
  if (custom) {
    // Re-apply the install's limits — licToggleOverride refilled plan defaults.
    Object.keys(cur.limits).forEach(function(k){ $('#lic_' + k).val(cur.limits[k]); });
  }

  // End date — only prefill the key's own end date when it's still in the
  // future; activating an already-past date yields a license that's EXPIRED
  // the moment it's applied. Local-date formatting (toISOString is UTC and
  // can give the wrong day).
  var fmt = function(dt) {
    return dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(dt.getDate()).padStart(2, '0');
  };
  var todayStr = fmt(new Date());
  var endDate = '';
  if (cur.end_date && cur.end_date > todayStr) {
    endDate = cur.end_date;
  } else {
    var days = parseInt(cur.days_left, 10);
    if (!(days > 0)) { days = 365; } // expired/unknown → default to a year out
    var d = new Date(); d.setDate(d.getDate() + days);
    endDate = fmt(d);
  }
  $('#licEnd').val(endDate).attr('min', todayStr);

  $('#licenseModal').modal('show');
}

function refreshLicense() {
  queueCmd($('#licInstallId').val(), 'report_status');
  toastr.info('Asked the install to report — close and reopen this modal after it checks in.');
}

$('#licPlanCode').on('change', function(){ if (!$('#licOverride').is(':checked')) licFillPlan(); });
$('#licOverride').on('change', licToggleOverride);

function openEmailModal(installId) {
  $('#emInstallId').val(installId);
  $('#emailModal').modal('show');
}

$('#emProvider').on('change', function() {
  var resend = $(this).val() === 'resend';
  $('#emResendBox').toggle(resend);
  $('#emSmtpBox').toggle(!resend);
});

function pushEmail() {
  var provider = $('#emProvider').val();
  var data = {
    install_id: $('#emInstallId').val(),
    command: 'set_email',
    email_provider: provider
  };
  if (provider === 'resend') {
    data.resend_api_key = $('#emResendKey').val();
    data.resend_from_email = $('#emResendFrom').val();
    data.resend_from_name = $('#emResendName').val();
    data.email_from_email = $('#emResendFrom').val();
    data.email_from_name = $('#emResendName').val();
  } else {
    data.smtp_host = $('#emSmtpHost').val();
    data.smtp_port = $('#emSmtpPort').val();
    data.smtp_user = $('#emSmtpUser').val();
    data.smtp_pass = $('#emSmtpPass').val();
    data.smtp_crypto = $('#emSmtpCrypto').val();
    data.smtp_status = '1';
  }
  fleetPost('<?= base_url('fleet/queue'); ?>', data, function(res) {
    if (res.status === 'ok') { $('#emailModal').modal('hide'); onQueued(data.install_id, 'set_email', res); }
    else toastr.error(res.message || 'Failed');
  }, function() { toastr.error('Server error'); });
}

function openSettingsModal(installId) {
  $('#stInstallId').val(installId);
  $('#stScope').val('assist').trigger('change');
  $('#settingsModal .st-section [data-field]').each(function () {
    var $f = $(this);
    if ($f.is(':checkbox')) { $f.prop('checked', $f.prop('defaultChecked')); }
    else if ($f.is('select')) { $f.prop('selectedIndex', 0); }
    else { $f.val(''); }
  });
  $('#settingsModal').modal('show');
}

$('#stScope').on('change', function () {
  var scope = $(this).val();
  $('#settingsModal .st-section').each(function () {
    $(this).toggle($(this).data('scope') === scope);
  });
});

function pushSettings() {
  var scope = $('#stScope').val();
  var fields = {};
  $('#settingsModal .st-section[data-scope="' + scope + '"] [data-field]').each(function () {
    var $f = $(this), name = $f.data('field'), val;
    if ($f.is(':checkbox')) {
      val = $f.is(':checked') ? '1' : '0';
    } else {
      val = $.trim($f.val());
      if (val === '') return; // untouched field — don't send
    }
    fields[name] = val;
  });
  if ($.isEmptyObject(fields)) { toastr.error('Fill at least one field.'); return; }
  $('#settingsModal').modal('hide');
  queueCmd($('#stInstallId').val(), 'set_settings', { scope: scope, fields: fields });
}

function openFileModal(id) {
  $('#pfInstallId').val(id);
  $('#pfFile').val('');
  $('#pfPath').val('');
  $('#fileModal').modal('show');
}

function pushFile() {
  var id = $('#pfInstallId').val(), path = $.trim($('#pfPath').val());
  var f = document.getElementById('pfFile').files[0];
  if (!f) { toastr.error('Choose a file.'); return; }
  if (!path) { toastr.error('Enter the destination path on the install.'); return; }
  if (!/^(application|theme)\//i.test(path) || path.indexOf('..') !== -1) {
    toastr.error('Path must start with application/ or theme/.');
    return;
  }
  if (f.size > 900 * 1024) { toastr.error('File too large — max ~900KB per push.'); return; }
  var r = new FileReader();
  r.onload = function () {
    var b64 = (r.result || '').split(',')[1] || '';
    if (!b64) { toastr.error('Could not read the file.'); return; }
    $('#fileModal').modal('hide');
    queueCmd(id, 'push_file', { path: path, content_b64: b64 });
  };
  r.onerror = function () { toastr.error('Could not read the file.'); };
  r.readAsDataURL(f);
}

function setupCron(id) {
  // schedule_crons handles every state itself: known key → schedules now;
  // missing/fallback key → pushes a unique one and auto-schedules when the
  // install reports it back. One click either way.
  fleetPost('<?= base_url('fleet/schedule_crons'); ?>', { install_id: id }, function(res) {
    if (res.status === 'ok') {
      toastr.success(res.message);
      setTimeout(function(){ location.reload(); }, 1500);
    } else {
      toastr.error(res.message || 'Failed');
    }
  }, function() { toastr.error('Server error'); });
}

function requestLicenseOtp() {
  var id = $('#licInstallId').val();
  var btn = $('#licOtpBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending…');
  $('#licOtpStatus').text('Central is generating the OTP and emailing it now…');
  // Central generates + emails the OTP itself — no install round-trip, no waiting.
  fleetPost('<?= base_url('fleet/request_otp'); ?>', { install_id: id }, function(res) {
    btn.prop('disabled', false).html('<i class="fa fa-key"></i> Request OTP');
    if (res.status === 'ok') {
      toastr.success(res.message);
      $('#licOtpStatus').html('<span class="text-success"><i class="fa fa-check"></i> ' + $('<i>').text(res.message).html() + ' Enter it below — expires in 10 minutes.</span>');
      $('#licOtp').focus();
    } else {
      toastr.error(res.message || 'Failed');
      $('#licOtpStatus').html('<span class="text-danger">' + $('<i>').text(res.message || 'Failed').html() + '</span>');
    }
  }, function() {
    btn.prop('disabled', false).html('<i class="fa fa-key"></i> Request OTP');
    toastr.error('Server error');
  });
}

function pushLicense() {
  var end = $('#licEnd').val();
  if (!end) { toastr.error('Pick a subscription end date'); return; }
  var otp = $.trim($('#licOtp').val());
  if (!otp) { toastr.error('Enter the OTP — use Request OTP so the install emails one.'); return; }
  var data = {
    install_id: $('#licInstallId').val(),
    command: 'set_license',
    plan_code: $('#licPlanCode').val(),
    end_date: end,
    otp_code: otp,
    whatsapp_number: $.trim($('#licWhatsapp').val()),
    renewal_amount: $.trim($('#licRenewal').val()),
    client_name: $.trim($('#licClientName').val()),
    override: $('#licOverride').is(':checked') ? 1 : 0
  };
  if (data.override) {
    ['branch_limit','user_limit','product_limit','sku_limit','online_product_limit',
     'service_limit','media_storage_limit_mb','storefront_limit','custom_domain_limit']
      .forEach(function(k){ data[k] = $('#lic_' + k).val(); });
  }
  fleetPost('<?= base_url('fleet/queue'); ?>', data, function(res) {
    if (res.status === 'ok') {
      $('#licenseModal').modal('hide');
      onQueued(data.install_id, 'set_license', res);
    } else {
      // Keep the modal open — a wrong/expired OTP is fixed right here.
      toastr.error(res.message || 'Failed');
      $('#licOtp').focus();
    }
  }, function() { toastr.error('Server error'); });
}

function saveCpanel() {
  fleetPost('<?= base_url('fleet/save_cpanel'); ?>', {
    cpanel_host: $('#cpHost').val(),
    cpanel_user: $('#cpUser').val(),
    cpanel_token: $('#cpToken').val(),
    base_domain: $('#cpDomain').val()
  }, function(res) {
    if (res.status === 'ok') toastr.success(res.message);
    else toastr.error(res.message || 'Failed');
  }, function() { toastr.error('Server error'); });
}

function provisionInstall() {
  var slug = $('#provSlug').val().trim();
  if (!slug) { toastr.error('Enter a subdomain name'); return; }
  $('#provBtn').prop('disabled', true).text('Provisioning…');
  $('#provResult').hide();
  fleetPost('<?= base_url('fleet/provision'); ?>', { slug: slug, partner: $('#provPartner').val() }, function(res) {
    $('#provBtn').prop('disabled', false).html('<i class="fa fa-magic"></i> Provision Install');
    if (res.status === 'ok') {
      $('#provResult').html(
        '<div class="alert alert-success" style="font-size:12px;margin-bottom:0">' +
        '<b>Ready:</b> <a href="' + res.deploy_url + '" target="_blank" rel="noopener">' + res.deploy_url + '</a><br>' +
        'Send this link to the partner — it creates the site, they click <b>Install</b>, then finish the setup wizard. ' +
        'The install appears in the table once it phones home.</div>'
      ).show();
      setTimeout(function(){ location.reload(); }, 1500);
    } else {
      toastr.error(res.message || 'Provision failed');
    }
  }, function() {
    $('#provBtn').prop('disabled', false).html('<i class="fa fa-magic"></i> Provision Install');
    toastr.error('Server error');
  });
}

function removeInstall(id, btn) {
  if (!confirm('Remove this install from the registry?')) return;
  fleetPost('<?= base_url('fleet/remove'); ?>', { id: id }, function(res) {
    if (res.status === 'ok') $(btn).closest('tr').fadeOut();
  });
}
</script>
<script>$('.fleet-active-li').addClass("active");$('.fleet-active-li').closest(".mp-nav-group").addClass("open");</script>
