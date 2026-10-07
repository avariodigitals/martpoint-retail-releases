<?php $CI =& get_instance(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($patient->customer_name); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar .edit { padding: 8px 14px; border-radius: 10px; background: var(--mp-primary); color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; }
    .hero { background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; padding: 16px; margin-bottom: 12px; display: flex; gap: 14px; align-items: center; }
    .avatar { width: 52px; height: 52px; border-radius: 14px; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; flex-shrink: 0; }
    .hero .name { font-size: 17px; font-weight: 700; }
    .hero .code { font-size: 11px; font-family: monospace; color: var(--mp-muted); margin-top: 2px; }
    .badge { font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }
    .badge.active { background: #D1FAE5; color: #065F46; }
    .badge.inactive { background: #FEF3C7; color: #B45309; }
    .badge.deceased { background: #E2E8F0; color: #475569; }
    .card { background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; padding: 16px; margin-bottom: 12px; }
    .card h3 { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--mp-muted); margin: 0 0 10px; }
    .kv { display: grid; grid-template-columns: 110px 1fr; gap: 6px 10px; font-size: 13px; }
    .kv dt { color: var(--mp-muted); }
    .kv dd { margin: 0; word-break: break-word; }
    .money { font-size: 17px; font-weight: 800; }
    .ok { color: #059669; } .due { color: #DC2626; }
    .row-item { display: flex; justify-content: space-between; gap: 8px; padding: 9px 0; border-bottom: 1px solid var(--mp-border); font-size: 13px; }
    .row-item:last-child { border-bottom: none; }
    .row-item .r-sub { font-size: 11px; color: var(--mp-muted); margin-top: 2px; }
    .empty { color: var(--mp-muted); font-size: 13px; padding: 6px 0; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/patients'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($patient->customer_name); ?></h1>
        </div>
        <?php if($can_edit && !$patient->deceased): ?>
          <a class="edit" href="<?= base_url('mobile/patient_form/'.(int)$patient->id); ?>">Edit</a>
        <?php endif; ?>
      </div>

      <div class="hero">
        <div class="avatar"><?= strtoupper(substr($patient->customer_name, 0, 1)); ?></div>
        <div style="flex:1;min-width:0;">
          <div class="name"><?= htmlspecialchars($patient->customer_name); ?></div>
          <div class="code"><?= htmlspecialchars($patient->patient_code ?: '—'); ?> · <?= htmlspecialchars($patient->customer_code ?: '—'); ?></div>
        </div>
        <?php if($patient->deceased): ?><span class="badge deceased">Deceased</span>
        <?php else: ?><span class="badge <?= $patient->status ? 'active' : 'inactive'; ?>"><?= $patient->status ? 'Active' : 'Inactive'; ?></span><?php endif; ?>
      </div>

      <div class="card">
        <h3>Contact</h3>
        <dl class="kv">
          <dt>Mobile</dt><dd><?= $patient->mobile ? '<a href="tel:' . htmlspecialchars($patient->mobile) . '" style="color:var(--mp-primary);text-decoration:none;">' . htmlspecialchars($patient->mobile) . '</a>' : '—'; ?></dd>
          <dt>Email</dt><dd><?= htmlspecialchars($patient->email ?: '—'); ?></dd>
          <dt>Address</dt><dd><?= htmlspecialchars(trim(($patient->address ?: '') . ' ' . ($patient->city ?: '')) ?: '—'); ?></dd>
        </dl>
      </div>

      <div class="card">
        <h3>Demographics</h3>
        <dl class="kv">
          <dt>Gender</dt><dd><?= $patient->gender ? ucfirst($patient->gender) : '—'; ?></dd>
          <dt>Date of Birth</dt><dd><?= $patient->dob ? date('M j, Y', strtotime($patient->dob)) : '—'; ?></dd>
          <dt>Blood Group</dt><dd><?= ($patient->blood_group && $patient->blood_group !== 'unknown') ? htmlspecialchars($patient->blood_group) : '—'; ?></dd>
          <dt>Occupation</dt><dd><?= htmlspecialchars($patient->occupation ?: '—'); ?></dd>
        </dl>
      </div>

      <div class="card">
        <h3>Next of Kin</h3>
        <dl class="kv">
          <dt>Name</dt><dd><?= htmlspecialchars($patient->nok_name ?: '—'); ?></dd>
          <dt>Phone</dt><dd><?= $patient->nok_phone ? '<a href="tel:' . htmlspecialchars($patient->nok_phone) . '" style="color:var(--mp-primary);text-decoration:none;">' . htmlspecialchars($patient->nok_phone) . '</a>' : '—'; ?></dd>
          <dt>Relationship</dt><dd><?= htmlspecialchars($patient->nok_relationship ?: '—'); ?></dd>
        </dl>
      </div>

      <div class="card">
        <h3>Account (billing identity)</h3>
        <dl class="kv">
          <dt>Prepaid / Advance</dt><dd class="money ok"><?= $CI->currency($patient->tot_advance ?? 0); ?></dd>
          <dt>Balance Due</dt><dd class="money <?= ($patient->sales_due ?? 0) > 0 ? 'due' : 'ok'; ?>"><?= $CI->currency($patient->sales_due ?? 0); ?></dd>
        </dl>
      </div>

      <div class="card">
        <h3>Care Episodes</h3>
        <?php if(empty($episodes)): ?>
          <div class="empty">No care episodes yet.</div>
        <?php else: ?>
          <?php foreach($episodes as $e): ?>
            <div class="row-item">
              <div>
                <div style="font-family:monospace;"><?= htmlspecialchars($e->episode_code ?: 'EP-'.$e->id); ?></div>
                <div class="r-sub"><?= ucfirst($e->episode_type); ?> · <?= $e->started_at ? date('M j, Y', strtotime($e->started_at)) : ''; ?></div>
              </div>
              <span class="badge <?= $e->status === 'open' ? 'active' : 'deceased'; ?>"><?= ucfirst($e->status); ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="card">
        <h3>Recent Appointments</h3>
        <?php if(empty($appointments)): ?>
          <div class="empty">No appointments on record.</div>
        <?php else: ?>
          <?php foreach($appointments as $a): ?>
            <div class="row-item">
              <div>
                <div><?= htmlspecialchars($a->service_name ?: 'Appointment'); ?></div>
                <div class="r-sub"><?= $a->scheduled_at ? date('M j, Y g:ia', strtotime($a->scheduled_at)) : '—'; ?><?= $a->staff_name ? ' · ' . htmlspecialchars($a->staff_name) : ''; ?></div>
              </div>
              <span class="badge <?= in_array($a->status, ['completed','confirmed','checked_in']) ? 'active' : 'inactive'; ?>"><?= ucfirst(str_replace('_',' ',$a->status)); ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
