<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 130px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .stats { display: flex; gap: 8px; margin-bottom: 14px; flex-wrap: wrap; }
    .stat-pill { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 16px; font-size: 12px; font-weight: 700; }
    .stat-pill.month { background: #EFF6FF; color: #1D4ED8; }
    .stat-pill.refill { background: #FEF3C7; color: #B45309; }
    .alert-card { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 14px; padding: 12px 14px; margin-bottom: 10px; font-size: 13px; }
    .alert-card .who { font-weight: 700; }
    .alert-card .when { color: #B45309; font-weight: 600; margin-top: 2px; }
    .search-row { display: flex; gap: 8px; margin-bottom: 14px; }
    .search-row input { flex: 1; padding: 11px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-family: inherit; font-size: 14px; }
    .search-row button { padding: 11px 16px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-weight: 700; cursor: pointer; }
    .card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .who { font-size: 15px; font-weight: 700; }
    .doc { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .date { font-size: 12px; color: var(--mp-muted); white-space: nowrap; }
    .excerpt { font-size: 13px; color: var(--mp-muted); margin-top: 8px; line-height: 1.5; }
    .flags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .flag { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }
    .flag.allergy { background: #FEE2E2; color: #B91C1C; }
    .flag.refill { background: #DBEAFE; color: #1D4ED8; }
    .flag.file { background: var(--mp-bg); color: var(--mp-ink); text-decoration: none; }
    .foot { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; font-size: 12px; color: var(--mp-muted); }
    .row-actions { display: flex; gap: 8px; }
    .mini-btn { padding: 6px 12px; border-radius: 8px; border: 1px solid var(--mp-border); background: var(--mp-bg); font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; color: var(--mp-ink); display: inline-flex; align-items: center; gap: 5px; }
    .mini-btn.danger { color: var(--mp-danger); border-color: #FECACA; }
    .fab { position: fixed; right: 18px; bottom: calc(92px + var(--safe-bottom)); width: 54px; height: 54px; border-radius: 50%; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; text-decoration: none; box-shadow: 0 6px 16px rgba(0,87,255,0.35); z-index: 50; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 14px; }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    .section-label { font-size: 12px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; margin: 14px 0 8px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } .fab { right: 32px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/operations'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <div class="stats">
        <span class="stat-pill month"><i class="fa fa-file-medical-o"></i> <?= (int)$this_month_count; ?> this month</span>
        <?php if(!empty($refill_reminders)): ?>
        <span class="stat-pill refill"><i class="fa fa-bell-o"></i> <?= count($refill_reminders); ?> refill<?= count($refill_reminders) == 1 ? '' : 's'; ?> due</span>
        <?php endif; ?>
      </div>

      <?php if(!empty($refill_reminders)): ?>
      <div class="section-label"><i class="fa fa-bell-o"></i> Refills due within 7 days</div>
      <?php foreach(array_slice($refill_reminders, 0, 5) as $rr): ?>
      <div class="alert-card">
        <div class="who"><?= htmlspecialchars($rr->customer_name ?: 'Unknown'); ?></div>
        <div class="when"><i class="fa fa-repeat"></i> Next refill <?= show_date($rr->next_refill_date); ?><?= ($rr->refills_remaining ?? 0) > 0 ? ' · '.$rr->refills_remaining.' left' : ''; ?></div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>

      <form class="search-row" method="get" action="<?= base_url('mobile/medical_notes'); ?>">
        <input type="search" name="q" placeholder="Search patient, diagnosis or doctor…" value="<?= htmlspecialchars($search); ?>">
        <button type="submit"><i class="fa fa-search"></i></button>
      </form>

      <?php if(!empty($notes)): ?>
        <?php foreach($notes as $n): ?>
        <div class="card">
          <div class="top">
            <div>
              <div class="who"><?= htmlspecialchars($n->customer_name ?: 'Unknown'); ?></div>
              <div class="doc"><?= $n->prescribing_doctor ? 'Dr. '.htmlspecialchars($n->prescribing_doctor) : 'No doctor recorded'; ?></div>
            </div>
            <span class="date"><i class="fa fa-calendar-o"></i> <?= show_date($n->note_date); ?></span>
          </div>
          <?php if(!empty($n->diagnosis)): ?><div class="excerpt"><?= nl2br(htmlspecialchars(mb_strimwidth($n->diagnosis, 0, 140, '…'))); ?></div><?php endif; ?>
          <div class="flags">
            <?php if(!empty($n->allergies_flagged)): ?><span class="flag allergy" title="<?= htmlspecialchars($n->allergies_flagged); ?>"><i class="fa fa-exclamation-triangle"></i> Allergy</span><?php endif; ?>
            <?php if(($n->refills_remaining ?? 0) > 0): ?><span class="flag refill"><i class="fa fa-repeat"></i> <?= (int)$n->refills_remaining; ?> refills<?= !empty($n->next_refill_date) ? ' · next '.show_date($n->next_refill_date) : ''; ?></span><?php endif; ?>
            <?php if(!empty($n->prescription_file)): ?><a class="flag file" href="<?= base_url($n->prescription_file); ?>" target="_blank"><i class="fa fa-file-image-o"></i> Rx file</a><?php endif; ?>
          </div>
          <div class="foot">
            <span><i class="fa fa-user-md"></i> <?= htmlspecialchars($n->staff_name ?: '-'); ?></span>
            <span class="row-actions">
              <?php if($can_add): ?><a class="mini-btn" href="<?= base_url('mobile/medical_note/'.$n->id); ?>"><i class="fa fa-pencil"></i> Edit</a><?php endif; ?>
              <?php if($can_delete): ?><button class="mini-btn danger" onclick="delNote(<?= (int)$n->id; ?>)"><i class="fa fa-trash"></i></button><?php endif; ?>
            </span>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa fa-file-medical-o"></i>
          <div>No medical notes<?= $search !== '' ? ' matching “'.htmlspecialchars($search).'”' : ' yet'; ?>.</div>
        </div>
      <?php endif; ?>
    </section>

    <?php if($can_add): ?>
    <a href="<?= base_url('mobile/medical_note'); ?>" class="fab" title="New Medical Note"><i class="fa fa-plus"></i></a>
    <?php endif; ?>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var csrf_token = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';

    function delNote(id){
      if(!confirm('Delete this medical note?')){ return; }
      var fd = new FormData();
      fd.append('id', id); fd.append(csrf_token, csrf_hash);
      fetch(base_url + 'mobile/medical_note_delete', { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(d){
          if(d && d.csrf_hash){ csrf_hash = d.csrf_hash; }
          if(d && d.success){ window.location.reload(); }
          else { alert(d && d.message ? d.message : 'Delete failed.'); }
        });
    }
  </script>
</body>
</html>
