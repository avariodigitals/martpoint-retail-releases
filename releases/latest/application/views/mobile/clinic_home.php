<?php
/**
 * Mobile clinical home — physiotherapy & rehabilitation.
 *
 * Shown by Mobile::index() when the store's business type is
 * physiotherapy_rehabilitation. Every tile is rendered only when the current
 * user holds the matching explicit clinical grant (see Mobile::clinicHome());
 * figures come from the same models the desktop screens use. Retail POS/Sale
 * tiles must never appear here.
 */
$CI =& get_instance();
$c = isset($clinic) && is_array($clinic) ? $clinic : array();
$acc = isset($clinic_access) && is_array($clinic_access) ? $clinic_access : array();
$patientTerm = mp_label('customer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Clinic</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/assist.css?v=15">
  <style>
    :root{
      --mp-primary:#176753; --mp-primary-dark:#104d40; --mp-bg:#eef3f0; --mp-surface:#fff;
      --mp-text:#1e2d28; --mp-ink:#1e2d28; --mp-muted:#687a72; --mp-border:#d8e2dc;
      --mp-success:#176753; --mp-danger:#c95f49; --mp-warning:#a96e24;
      --safe-bottom:env(safe-area-inset-bottom,0px);
    }
    *{box-sizing:border-box}
    html,body{margin:0;padding:0;font-family:'DM Sans',-apple-system,BlinkMacSystemFont,sans-serif;background:var(--mp-bg);color:var(--mp-text);height:100%;overscroll-behavior:none;-webkit-tap-highlight-color:transparent}
    #app{max-width:430px;margin:0 auto;background:var(--mp-bg);min-height:100vh}
    .screen{padding:12px 12px 100px}
    .topbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;padding-top:8px}
    .topbar-titles{flex:1;min-width:0}
    .store-name{font-size:11px;color:var(--mp-muted);font-weight:700;text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px}
    .topbar h1{font-family:'DM Serif Display',Georgia,serif;font-weight:400;font-size:23px;margin:0}
    .avatar{width:36px;height:36px;border-radius:50%;background:#dcefe7;color:var(--mp-primary-dark);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;overflow:hidden;text-decoration:none;flex:0 0 36px}
    .avatar img{width:100%;height:100%;object-fit:cover}
    .greeting{font-size:13px;color:var(--mp-muted);margin-bottom:14px}

    .hero{display:flex;align-items:center;gap:12px;padding:16px 14px;background:var(--mp-primary);border-radius:16px;color:#fff;margin-bottom:12px;text-decoration:none}
    .hero .icon{width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-size:18px;flex:0 0 42px}
    .hero .text{flex:1;min-width:0}
    .hero .title{font-size:16px;font-weight:700;margin:0}
    .hero .sub{font-size:12px;opacity:.9;margin-top:2px}
    .hero .arrow{opacity:.9}

    .kpi-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
    .kpi{display:block;padding:14px 13px;border-radius:14px;background:var(--mp-surface);border:1px solid var(--mp-border);text-decoration:none;color:var(--mp-ink)}
    .kpi .label{font-size:10px;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.4px;font-weight:700;margin-bottom:6px}
    .kpi .value{font-size:clamp(18px,5vw,24px);font-weight:700;line-height:1.15;overflow-wrap:break-word}
    .kpi .sub{font-size:11px;color:var(--mp-muted);margin-top:4px}
    .kpi.wide{grid-column:span 2}
    .kpi.money .value{color:var(--mp-primary-dark)}
    .kpi.alert .value{color:var(--mp-danger)}

    .section-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--mp-muted);margin:18px 0 8px}
    .tiles{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .tile{display:flex;flex-direction:column;gap:8px;padding:14px 12px;border-radius:14px;background:var(--mp-surface);border:1px solid var(--mp-border);text-decoration:none;color:var(--mp-ink);font-size:13px;font-weight:600}
    .tile .icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;background:#e3efe9;color:var(--mp-primary-dark)}
    .tile .desc{font-size:11px;font-weight:500;color:var(--mp-muted);line-height:1.35}
    .tile.blue .icon{background:#e2ecf5;color:#2f5f7d}
    .tile.amber .icon{background:#f7eddc;color:#8a5a1c}
    .tile.rose .icon{background:#f8e5e0;color:#a3452f}

    .empty-state{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:14px;padding:26px 18px;text-align:center;color:var(--mp-muted);font-size:13px}
    .empty-state i{display:block;font-size:26px;margin-bottom:8px;color:var(--mp-border)}
  </style>
</head>
<body>
<div id="app">
  <div class="screen">
    <div class="topbar">
      <div class="topbar-titles">
        <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? ($store_name ?? 'MartPoint')); ?></div>
        <h1>Clinic</h1>
      </div>
      <a class="avatar" href="<?= base_url('mobile/profile'); ?>" aria-label="My profile">
        <?php if(!empty($profile_picture)): ?><img src="<?= base_url($profile_picture); ?>" alt="">
        <?php else: ?><?= htmlspecialchars(strtoupper(substr($display_name ?? 'U', 0, 1))); ?><?php endif; ?>
      </a>
    </div>

    <div class="greeting">Hello <?= htmlspecialchars($display_name ?? 'there'); ?> · <?= date('D, d M Y'); ?></div>

    <?php if(!empty($acc['queue'])): ?>
    <a class="hero" href="<?= base_url('mobile/care_queue'); ?>">
      <span class="icon"><i class="fa fa-list-ol"></i></span>
      <span class="text">
        <span class="title"><?= (int)($c['queue_total'] ?? 0); ?> in care queue</span>
        <span class="sub">Check in arrivals and move patients through the day</span>
      </span>
      <i class="fa fa-chevron-right arrow"></i>
    </a>
    <?php endif; ?>

    <div class="kpi-grid">
      <?php if(!empty($acc['patients'])): ?>
      <a class="kpi" href="<?= base_url('mobile/patients'); ?>">
        <div class="label"><?= htmlspecialchars($patientTerm); ?>s</div>
        <div class="value"><?= (int)($c['patients']['active'] ?? 0); ?></div>
        <div class="sub">active · <?= (int)($c['patients']['total'] ?? 0); ?> on register</div>
      </a>
      <?php endif; ?>

      <?php if(isset($c['appointments_today'])): ?>
      <a class="kpi" href="<?= base_url('mobile/appointments'); ?>">
        <div class="label">Booked today</div>
        <div class="value"><?= (int)$c['appointments_today']; ?></div>
        <div class="sub">appointments</div>
      </a>
      <?php endif; ?>

      <?php if(isset($c['sessions_today'])): ?>
      <a class="kpi" href="<?= base_url('mobile/sessions'); ?>">
        <div class="label">Sessions</div>
        <div class="value"><?= (int)$c['sessions_today']; ?></div>
        <div class="sub">scheduled today</div>
      </a>
      <?php endif; ?>

      <?php if(isset($c['tasks_open'])): ?>
      <a class="kpi<?= $c['tasks_open'] > 0 ? ' alert' : ''; ?>" href="<?= base_url('mobile/ward_tasks'); ?>">
        <div class="label">Ward tasks</div>
        <div class="value"><?= (int)$c['tasks_open']; ?></div>
        <div class="sub">open nursing + porter</div>
      </a>
      <?php endif; ?>

      <?php if(isset($c['beds_total']) && $c['beds_total'] > 0): ?>
      <a class="kpi" href="<?= base_url('mobile/admissions'); ?>">
        <div class="label">Beds</div>
        <div class="value"><?= (int)$c['beds_free']; ?> <span style="font-size:12px;color:var(--mp-muted);font-weight:600;">free</span></div>
        <div class="sub"><?= (int)$c['beds_in_use']; ?> of <?= (int)$c['beds_total']; ?> in use</div>
      </a>
      <?php endif; ?>

      <?php if(isset($c['billing_due'])): ?>
      <a class="kpi money" href="<?= base_url('patient_billing'); ?>">
        <div class="label">Bills due</div>
        <div class="value"><?= htmlspecialchars($c['billing_due']); ?></div>
        <div class="sub">across patient accounts</div>
      </a>
      <?php endif; ?>

      <?php if(isset($c['funds_reserved'])): ?>
      <a class="kpi money wide" href="<?= base_url('patient_funds'); ?>">
        <div class="label">Funds reserved for treatment</div>
        <div class="value"><?= htmlspecialchars($c['funds_reserved']); ?></div>
        <div class="sub">held against active treatment plans</div>
      </a>
      <?php endif; ?>
    </div>

    <?php
      $hasTiles = !empty($acc['patients']) || !empty($acc['queue']) || !empty($acc['appointments'])
        || !empty($acc['sessions']) || !empty($acc['ward']) || !empty($acc['accounts']) || !empty($acc['imports']);
    ?>
    <?php if($hasTiles): ?>
    <div class="section-title">Workspaces</div>
    <div class="tiles">
      <?php if(!empty($acc['patients'])): ?>
      <a class="tile" href="<?= base_url('mobile/patients'); ?>">
        <span class="icon"><i class="fa fa-address-book-o"></i></span>
        <span><?= htmlspecialchars($patientTerm); ?> register</span>
        <span class="desc">Search the register and open a profile</span>
      </a>
      <?php endif; ?>

      <?php if(!empty($acc['queue'])): ?>
      <a class="tile blue" href="<?= base_url('mobile/care_queue'); ?>">
        <span class="icon"><i class="fa fa-list-ol"></i></span>
        <span>Care queue</span>
        <span class="desc">Today's live patient flow</span>
      </a>
      <?php endif; ?>

      <?php if(!empty($acc['appointments'])): ?>
      <a class="tile blue" href="<?= base_url('mobile/appointments'); ?>">
        <span class="icon"><i class="fa fa-calendar"></i></span>
        <span>Appointments</span>
        <span class="desc">Bookings and arrivals</span>
      </a>
      <?php endif; ?>

      <?php if(!empty($acc['sessions'])): ?>
      <a class="tile" href="<?= base_url('mobile/sessions'); ?>">
        <span class="icon"><i class="fa fa-stethoscope"></i></span>
        <span>Physiotherapy</span>
        <span class="desc">Treatment session diary</span>
      </a>
      <?php endif; ?>

      <?php if(!empty($acc['ward'])): ?>
      <a class="tile amber" href="<?= base_url('mobile/ward_tasks'); ?>">
        <span class="icon"><i class="fa fa-heartbeat"></i></span>
        <span>Ward tasks</span>
        <span class="desc">Nursing and porter worklists</span>
      </a>
      <?php endif; ?>

      <?php if(!empty($acc['ward'])): ?>
      <a class="tile amber" href="<?= base_url('mobile/admissions'); ?>">
        <span class="icon"><i class="fa fa-bed"></i></span>
        <span>Admissions &amp; beds</span>
        <span class="desc">Inpatients and bed occupancy</span>
      </a>
      <?php endif; ?>

      <?php if(!empty($acc['accounts'])): ?>
      <a class="tile rose" href="<?= base_url('patient_funds'); ?>">
        <span class="icon"><i class="fa fa-money"></i></span>
        <span>Patient accounts</span>
        <span class="desc">Bills, funds and payment evidence</span>
      </a>
      <?php endif; ?>

      <?php if(!empty($acc['imports'])): ?>
      <a class="tile" href="<?= base_url('imports'); ?>">
        <span class="icon"><i class="fa fa-upload"></i></span>
        <span>Legacy import</span>
        <span class="desc">Opening positions and migration console</span>
      </a>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="empty-state"><i class="fa fa-lock"></i>No clinical workspaces are assigned to your role. Contact your clinic administrator.</div>
    <?php endif; ?>
  </div>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'home']); ?>
</div>
<?php $this->load->view('mobile/chat'); ?>
</body>
</html>
