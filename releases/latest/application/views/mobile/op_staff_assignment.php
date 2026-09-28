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
    .hint { font-size: 13px; color: var(--mp-muted); margin-bottom: 14px; }
    .svc-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; margin-bottom: 10px; overflow: hidden; }
    .svc-card summary { padding: 14px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; list-style: none; }
    .svc-card summary::-webkit-details-marker { display: none; }
    .svc-card .nm { font-size: 15px; font-weight: 700; }
    .svc-card .cnt { font-size: 12px; color: var(--mp-muted); margin-left: 8px; }
    .svc-card .arrow { color: var(--mp-muted); transition: transform 0.2s ease; }
    .svc-card[open] .arrow { transform: rotate(90deg); }
    .staff-chips { display: flex; flex-wrap: wrap; gap: 8px; padding: 0 14px 14px; }
    .staff-chip { padding: 9px 14px; border-radius: 20px; border: 1px solid var(--mp-border); background: var(--mp-bg); font-size: 13px; font-weight: 600; color: var(--mp-ink); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
    .staff-chip.on { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .staff-chip.busy { opacity: 0.6; }
    .staff-chip i { font-size: 11px; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 14px; }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    .toast { position: fixed; left: 50%; bottom: 110px; transform: translateX(-50%); background: #111827; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 13px; display: none; z-index: 300; max-width: 90%; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/operations'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <div class="hint">Tap a <?= htmlspecialchars(strtolower($staff_label)); ?> chip to assign or unassign them from a <?= htmlspecialchars(strtolower($service_label)); ?>. Changes save immediately.</div>

      <?php if(!empty($services)): ?>
        <?php foreach($services as $svc):
          $assigned_count = !empty($assignments[$svc->id]) ? count($assignments[$svc->id]) : 0;
        ?>
        <details class="svc-card" <?= $assigned_count ? 'open' : ''; ?>>
          <summary>
            <span><span class="nm"><?= htmlspecialchars($svc->service_name); ?></span><span class="cnt"><?= $assigned_count; ?> assigned</span></span>
            <i class="fa fa-chevron-right arrow"></i>
          </summary>
          <div class="staff-chips">
            <?php foreach($staff_list as $u):
              $on = !empty($assignments[$svc->id][$u->id]);
              $uname = trim(($u->first_name ?? '').' '.($u->last_name ?? '')) ?: $u->username;
            ?>
            <button type="button" class="staff-chip <?= $on ? 'on' : ''; ?>" data-svc="<?= (int)$svc->id; ?>" data-staff="<?= (int)$u->id; ?>" data-on="<?= $on ? '1' : '0'; ?>">
              <i class="fa <?= $on ? 'fa-check' : 'fa-plus'; ?>"></i> <?= htmlspecialchars($uname); ?>
            </button>
            <?php endforeach; ?>
            <?php if(empty($staff_list)): ?><span class="hint" style="margin:0;">No active <?= htmlspecialchars(strtolower($staff_label)); ?> found.</span><?php endif; ?>
          </div>
        </details>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa fa-user-md"></i>
          <div>No <?= htmlspecialchars(strtolower($service_label)); ?>s configured yet.</div>
        </div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var csrf_token = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';

    function showToast(msg){ var t = document.getElementById('toast'); t.textContent = msg; t.style.display = 'block'; setTimeout(function(){ t.style.display = 'none'; }, 2500); }

    document.querySelectorAll('.staff-chip').forEach(function(chip){
      chip.addEventListener('click', function(){
        if(this.classList.contains('busy')) return;
        var svc = this.getAttribute('data-svc');
        var staff = this.getAttribute('data-staff');
        var isOn = this.getAttribute('data-on') === '1';
        var el = this;
        el.classList.add('busy');

        var fd = new FormData();
        fd.append('action', isOn ? 'unassign' : 'assign');
        fd.append('service_id', svc);
        fd.append('staff_id', staff);
        fd.append(csrf_token, csrf_hash);

        fetch(base_url + 'mobile/staff_assignment_save', { method: 'POST', body: fd })
          .then(function(r){ return r.json(); })
          .then(function(d){
            el.classList.remove('busy');
            if(d && d.csrf_hash){ csrf_hash = d.csrf_hash; }
            if(d && d.success){
              var nowOn = !isOn;
              el.setAttribute('data-on', nowOn ? '1' : '0');
              el.classList.toggle('on', nowOn);
              el.querySelector('i').className = 'fa ' + (nowOn ? 'fa-check' : 'fa-plus');
              var cnt = el.closest('.svc-card').querySelector('.cnt');
              var n = parseInt(cnt.textContent) + (nowOn ? 1 : -1);
              cnt.textContent = Math.max(n, 0) + ' assigned';
            } else {
              showToast(d && d.message ? d.message : 'Update failed.');
            }
          })
          .catch(function(){ el.classList.remove('busy'); showToast('Network error.'); });
      });
    });
  </script>
</body>
</html>
