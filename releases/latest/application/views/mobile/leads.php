<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Leads</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar .back:active { background: #E2E8F0; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar .add { padding: 9px 14px; border-radius: 10px; background: var(--mp-primary); color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; border: none; cursor: pointer; }
    .pills { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 6px; -webkit-overflow-scrolling: touch; }
    .pills::-webkit-scrollbar { display: none; }
    .pill { flex-shrink: 0; padding: 7px 14px; border-radius: 20px; background: var(--mp-bg); border: 1px solid var(--mp-border); font-size: 12px; font-weight: 600; color: var(--mp-muted); text-decoration: none; }
    .pill.active { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .lead-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 14px 16px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .lead-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .lead-name { font-size: 15px; font-weight: 700; color: var(--mp-ink); }
    .lead-contact { font-size: 12px; color: var(--mp-muted); margin-top: 3px; line-height: 1.5; word-break: break-all; }
    .lead-contact a { color: var(--mp-muted); text-decoration: none; }
    .lead-interest { font-size: 12px; color: var(--mp-ink); margin-top: 8px; background: var(--mp-bg); border-radius: 8px; padding: 8px 10px; }
    .badge { font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px; white-space: nowrap; text-transform: capitalize; }
    .badge.new { background: #DBEAFE; color: #1D4ED8; }
    .badge.contacted { background: #FEF3C7; color: #B45309; }
    .badge.qualified { background: #EDE9FE; color: #6D28D9; }
    .badge.converted { background: #D1FAE5; color: #065F46; }
    .badge.lost { background: #FEE2E2; color: #B91C1C; }
    .lead-actions { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
    .lead-actions button, .lead-actions a { font-size: 12px; font-weight: 600; padding: 7px 12px; border-radius: 8px; border: 1px solid var(--mp-border); background: #fff; color: var(--mp-ink); text-decoration: none; cursor: pointer; }
    .lead-actions .convert { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .lead-actions .del { color: var(--mp-danger); }
    .lead-meta { font-size: 11px; color: var(--mp-muted); margin-top: 8px; text-transform: capitalize; }
    .sheet { display: none; background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; padding: 16px; margin-bottom: 14px; }
    .sheet.show { display: block; }
    .sheet h3 { font-size: 15px; font-weight: 700; margin: 0 0 12px; }
    .sheet input, .sheet select, .sheet textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; margin-bottom: 10px; font-family: inherit; }
    .sheet .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .sheet .save { width: 100%; padding: 13px; border-radius: 10px; border: none; background: var(--mp-primary); color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; }
    .empty { text-align: center; padding: 60px 24px; color: var(--mp-muted); font-size: 14px; }
    .searchbar { display: flex; gap: 8px; margin-bottom: 12px; }
    .searchbar input { flex: 1; padding: 11px 14px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Leads</h1>
        </div>
        <?php if($can_edit): ?>
          <button class="add" onclick="toggleForm()">+ New</button>
        <?php endif; ?>
      </div>

      <?php if($can_edit): ?>
      <div class="sheet" id="leadForm">
        <h3 id="formTitle">New Lead</h3>
        <input type="hidden" id="lead_id" value="">
        <input type="text" id="ld_name" placeholder="Name *">
        <div class="row2">
          <input type="tel" id="ld_phone" placeholder="Phone / WhatsApp">
          <input type="email" id="ld_email" placeholder="Email">
        </div>
        <div class="row2">
          <select id="ld_source">
            <option value="manual">Manual Entry</option>
            <option value="walk_in">Walk-in</option>
            <option value="phone">Phone Call</option>
            <option value="whatsapp">WhatsApp</option>
            <option value="referral">Referral</option>
            <option value="storefront">Website</option>
            <option value="other">Other</option>
          </select>
          <select id="ld_status">
            <option value="new">New</option>
            <option value="contacted">Contacted</option>
            <option value="qualified">Qualified</option>
            <option value="lost">Lost</option>
          </select>
        </div>
        <input type="text" id="ld_interest" placeholder="Interested in…">
        <textarea id="ld_notes" rows="2" placeholder="Notes"></textarea>
        <button class="save" onclick="saveLead()">Save Lead</button>
      </div>
      <?php endif; ?>

      <div class="pills">
        <a class="pill <?= $status_filter === '' ? 'active' : ''; ?>" href="<?= base_url('mobile/leads'); ?>">All (<?= (int)($stats['total'] ?? 0); ?>)</a>
        <?php foreach(['new','contacted','qualified','converted','lost'] as $st): ?>
        <a class="pill <?= $status_filter === $st ? 'active' : ''; ?>" href="<?= base_url('mobile/leads?status=' . $st); ?>"><?= ucfirst($st); ?> (<?= (int)($stats[$st] ?? 0); ?>)</a>
        <?php endforeach; ?>
      </div>

      <form method="get" action="<?= base_url('mobile/leads'); ?>" class="searchbar">
        <?php if($status_filter !== ''): ?><input type="hidden" name="status" value="<?= htmlspecialchars($status_filter); ?>"><?php endif; ?>
        <input type="text" name="search" placeholder="Search name, phone, email…" value="<?= htmlspecialchars($search ?? ''); ?>">
      </form>

      <?php if(!empty($leads)): ?>
        <?php foreach($leads as $l): ?>
          <div class="lead-card" data-id="<?= (int)$l->id; ?>"
               data-name="<?= htmlspecialchars($l->name); ?>"
               data-phone="<?= htmlspecialchars($l->phone ?? ''); ?>"
               data-email="<?= htmlspecialchars($l->email ?? ''); ?>"
               data-source="<?= htmlspecialchars($l->source); ?>"
               data-status="<?= htmlspecialchars($l->status); ?>"
               data-interest="<?= htmlspecialchars($l->interest ?? ''); ?>"
               data-notes="<?= htmlspecialchars($l->notes ?? ''); ?>">
            <div class="lead-top">
              <div class="lead-name"><?= htmlspecialchars($l->name); ?></div>
              <span class="badge <?= $l->status; ?>"><?= $l->status; ?></span>
            </div>
            <div class="lead-contact">
              <?php if($l->phone): ?><a href="tel:<?= htmlspecialchars($l->phone); ?>"><i class="fa fa-phone"></i> <?= htmlspecialchars($l->phone); ?></a><?php endif; ?>
              <?php if($l->phone && $l->email): ?> · <?php endif; ?>
              <?php if($l->email): ?><i class="fa fa-envelope-o"></i> <?= htmlspecialchars($l->email); ?><?php endif; ?>
            </div>
            <?php if($l->interest): ?><div class="lead-interest"><?= htmlspecialchars($l->interest); ?></div><?php endif; ?>
            <div class="lead-meta"><i class="fa fa-tag"></i> <?= htmlspecialchars(str_replace('_',' ',$l->source)); ?> · <?= !empty($l->created_at) ? date('M j, Y', strtotime($l->created_at)) : ''; ?><?php if($l->status === 'converted' && $l->converted_customer_id): ?> · <i class="fa fa-check-circle" style="color:var(--mp-success);"></i> Customer #<?= (int)$l->converted_customer_id; ?><?php endif; ?></div>
            <?php if($can_edit): ?>
            <div class="lead-actions">
              <?php if($l->status !== 'converted'): ?>
                <button class="convert" onclick="convertLead(<?= (int)$l->id; ?>)"><i class="fa fa-user-plus"></i> Convert</button>
                <?php if($l->status === 'new'): ?><button onclick="setStatus(<?= (int)$l->id; ?>, 'contacted')">Contacted</button><?php endif; ?>
                <?php if($l->status === 'contacted'): ?><button onclick="setStatus(<?= (int)$l->id; ?>, 'qualified')">Qualified</button><?php endif; ?>
                <button onclick="editLead(this.closest('.lead-card'))">Edit</button>
              <?php endif; ?>
              <button class="del" onclick="deleteLead(<?= (int)$l->id; ?>)"><i class="fa fa-trash"></i></button>
            </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No leads here yet.<br>Tap + New to add one, or they will appear when customers enquire on your online store.</div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var CSRF_NAME = '<?= $this->security->get_csrf_token_name(); ?>';
    var CSRF_HASH = '<?= $this->security->get_csrf_hash(); ?>';

    function post(url, extra){
      var fd = new FormData();
      fd.append(CSRF_NAME, CSRF_HASH);
      if(extra){ for(var k in extra){ fd.append(k, extra[k]); } }
      return fetch(url, { method: 'POST', body: fd }).then(function(r){ return r.json(); });
    }
    function toggleForm(){
      var el = document.getElementById('leadForm');
      if(el) el.classList.toggle('show');
    }
    function editLead(card){
      document.getElementById('lead_id').value = card.getAttribute('data-id');
      document.getElementById('ld_name').value = card.getAttribute('data-name');
      document.getElementById('ld_phone').value = card.getAttribute('data-phone');
      document.getElementById('ld_email').value = card.getAttribute('data-email');
      document.getElementById('ld_source').value = card.getAttribute('data-source');
      document.getElementById('ld_status').value = card.getAttribute('data-status') === 'converted' ? 'qualified' : card.getAttribute('data-status');
      document.getElementById('ld_interest').value = card.getAttribute('data-interest');
      document.getElementById('ld_notes').value = card.getAttribute('data-notes');
      document.getElementById('formTitle').textContent = 'Edit Lead';
      var el = document.getElementById('leadForm');
      el.classList.add('show');
      el.scrollIntoView({ behavior: 'smooth' });
    }
    function saveLead(){
      var name = document.getElementById('ld_name').value.trim();
      if(!name){ alert('Lead name is required'); return; }
      post('<?= base_url('leads/save'); ?>', {
        lead_id: document.getElementById('lead_id').value,
        name: name,
        phone: document.getElementById('ld_phone').value,
        email: document.getElementById('ld_email').value,
        source: document.getElementById('ld_source').value,
        status: document.getElementById('ld_status').value,
        interest: document.getElementById('ld_interest').value,
        notes: document.getElementById('ld_notes').value
      }).then(function(d){
        if(d && d.status === 'success'){ location.reload(); }
        else { alert(d && d.message ? d.message : 'Save failed'); }
      }).catch(function(){ alert('Save failed. Please try again.'); });
    }
    function setStatus(id, status){
      post('<?= base_url('leads/update_status/'); ?>' + id, { status: status })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { alert(d && d.message ? d.message : 'Failed'); } });
    }
    function convertLead(id){
      if(!confirm('Convert this lead to a customer?')) return;
      post('<?= base_url('leads/convert/'); ?>' + id, {})
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { alert(d && d.message ? d.message : 'Conversion failed'); } });
    }
    function deleteLead(id){
      if(!confirm('Delete this lead?')) return;
      post('<?= base_url('leads/delete/'); ?>' + id, {})
        .then(function(d){ if(d && d.status === 'success'){ document.querySelector('[data-id="' + id + '"]').remove(); } else { alert(d && d.message ? d.message : 'Delete failed'); } });
    }
  </script>
</body>
</html>
