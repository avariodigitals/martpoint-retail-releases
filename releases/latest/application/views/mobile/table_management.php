<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Tables</title>
  <link rel='preconnect' href='https://fonts.googleapis.com'>
  <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <link rel='stylesheet' href='<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css'>
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 16px; }
    .kpi-card { padding: 12px 4px; border-radius: 12px; text-align: center; background: var(--mp-surface); border: 1px solid var(--mp-border); }
    .kpi-card .value { font-size: 20px; font-weight: 700; }
    .kpi-card .label { font-size: 10px; color: var(--mp-muted); margin-top: 4px; }
    .kpi-card.available .value { color: var(--mp-success); }
    .kpi-card.occupied .value { color: var(--mp-danger); }
    .kpi-card.reserved .value { color: var(--mp-warning); }
    .kpi-card.cleaning .value { color: var(--mp-muted); }
    .table-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-left: 4px solid var(--mp-border); }
    .table-card.available { border-left-color: var(--mp-success); }
    .table-card.occupied { border-left-color: var(--mp-danger); }
    .table-card.reserved { border-left-color: var(--mp-warning); }
    .table-card.cleaning { border-left-color: var(--mp-muted); }
    .table-name { font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .table-meta { font-size: 12px; color: var(--mp-muted); margin-top: 4px; }
    .table-status { display: inline-block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; margin-top: 10px; }
    .table-status.available { color: var(--mp-success); }
    .table-status.occupied { color: var(--mp-danger); }
    .table-status.reserved { color: var(--mp-warning); }
    .table-status.cleaning { color: var(--mp-muted); }
    .table-actions { display: flex; gap: 8px; margin-top: 12px; }
    .action-btn { flex: 1; padding: 10px; border-radius: 10px; border: none; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; text-align: center; }
    .edit-btn { background: var(--mp-bg); color: var(--mp-ink); border: 1px solid var(--mp-border); }
    .quick-btn { color: #fff; }
    .quick-btn.available { background: var(--mp-danger); }
    .quick-btn.occupied { background: var(--mp-success); }
    .quick-btn.reserved { background: var(--mp-warning); color: #212529; }
    .quick-btn.cleaning { background: var(--mp-success); }
    .fab { position: fixed; right: 16px; bottom: 86px; width: 56px; height: 56px; border-radius: 50%; background: var(--mp-primary); color: #fff; border: none; box-shadow: 0 4px 12px rgba(0,87,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 22px; cursor: pointer; z-index: 100; }
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 200; display: none; align-items: flex-end; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal { background: var(--mp-surface); width: 100%; max-width: 430px; border-radius: 20px 20px 0 0; padding: 20px; max-height: 90vh; overflow-y: auto; }
    .modal h3 { margin: 0 0 16px; font-size: 18px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-ink); }
    .form-control { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: var(--mp-surface); color: var(--mp-text); }
    .form-actions { display: flex; gap: 10px; margin-top: 20px; }
    .form-actions button { flex: 1; padding: 14px; border-radius: 12px; border: none; font-size: 15px; font-weight: 700; cursor: pointer; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-secondary { background: var(--mp-bg); color: var(--mp-ink); border: 1px solid var(--mp-border); }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
  </style>
</head>
<body>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <a href='<?= base_url('mobile/operations'); ?>' class='back'><i class='fa fa-chevron-left'></i></a>
        <div class='topbar-titles'>
          <div class='store-name'><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Table Management</h1>
        </div>
      </div>

      <div class='kpi-grid'>
        <div class='kpi-card available'>
          <div class='value'><?= (int)$status_counts['available']; ?></div>
          <div class='label'>Free</div>
        </div>
        <div class='kpi-card occupied'>
          <div class='value'><?= (int)$status_counts['occupied']; ?></div>
          <div class='label'>Full</div>
        </div>
        <div class='kpi-card reserved'>
          <div class='value'><?= (int)$status_counts['reserved']; ?></div>
          <div class='label'>Rsvd</div>
        </div>
        <div class='kpi-card cleaning'>
          <div class='value'><?= (int)$status_counts['cleaning']; ?></div>
          <div class='label'>Clean</div>
        </div>
      </div>

      <?php if(!empty($tables)): ?>
        <?php foreach($tables as $t): ?>
        <div class='table-card <?= $t->status; ?>' data-id='<?= (int)$t->id; ?>'>
          <div class='table-name'>
            <?= htmlspecialchars($t->table_name); ?>
            <?php if(!empty($t->table_code)): ?><span class='label label-default' style='font-size:10px;padding:2px 6px;background:var(--mp-bg);border-radius:4px;color:var(--mp-muted);'><?= htmlspecialchars($t->table_code); ?></span><?php endif; ?>
          </div>
          <div class='table-meta'>
            <?php if(!empty($t->zone)): ?><i class='fa fa-map-marker'></i> <?= htmlspecialchars($t->zone); ?> · <?php endif; ?>
            <i class='fa fa-users'></i> <?= (int)$t->capacity; ?> seats
          </div>
          <div class='table-status <?= $t->status; ?>'><?= ucfirst($t->status); ?></div>
          <div class='table-actions'>
            <button type='button' class='action-btn edit-btn' onclick='openEdit(<?= (int)$t->id; ?>)'><i class='fa fa-pencil'></i> Edit</button>
            <button type='button' class='action-btn quick-btn <?= $t->status; ?>' onclick='toggleStatus(<?= (int)$t->id; ?>, "<?= $t->status; ?>")'><?= nextStatusLabel($t->status); ?></button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class='empty-state'>
          <i class='fa fa-table'></i>
          <div>No tables yet. Add your first table.</div>
        </div>
      <?php endif; ?>
    </section>

    <button type='button' class='fab' id='addFab' onclick='openAdd()'><i class='fa fa-plus'></i></button>

    <div class='modal-overlay' id='tableModal'>
      <div class='modal'>
        <h3 id='modalTitle'>Add Table</h3>
        <form id='tableForm' action='<?= base_url('mobile/table_management'); ?>' method='post'>
          <input type='hidden' name='<?= $this->security->get_csrf_token_name(); ?>' value='<?= $this->security->get_csrf_hash(); ?>'>
          <input type='hidden' name='save_table' value='1'>
          <input type='hidden' name='edit_id' id='editId' value=''>

          <div class='form-group'>
            <label>Table Name</label>
            <input type='text' name='table_name' id='tableName' class='form-control' placeholder='Table 1' required>
          </div>

          <div class='form-group'>
            <label>Table Code</label>
            <input type='text' name='table_code' id='tableCode' class='form-control' placeholder='T01'>
          </div>

          <div class='form-group'>
            <label>Capacity</label>
            <input type='number' name='capacity' id='capacity' class='form-control' placeholder='4' min='1' value='4'>
          </div>

          <div class='form-group'>
            <label>Zone / Area</label>
            <input type='text' name='zone' id='zone' class='form-control' placeholder='Indoor, Garden'>
          </div>

          <div class='form-group'>
            <label>Status</label>
            <select name='status' id='status' class='form-control' style='-webkit-appearance:none; appearance:none;'>
              <option value='available'>Available</option>
              <option value='occupied'>Occupied</option>
              <option value='reserved'>Reserved</option>
              <option value='cleaning'>Cleaning</option>
            </select>
          </div>

          <div class='form-group'>
            <label>Sort Order</label>
            <input type='number' name='sort_order' id='sortOrder' class='form-control' placeholder='0' value='0'>
          </div>

          <div class='form-actions'>
            <button type='button' class='btn-secondary' onclick='closeModal()'>Cancel</button>
            <button type='submit' class='btn-primary'>Save</button>
          </div>
        </form>
      </div>
    </div>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var csrf_token = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';
    var tables = <?= json_encode($tables ?? []); ?>;

    function nextStatusLabel(status){
      if(status === 'available') return '<i class="fa fa-user-plus"></i> Seat';
      if(status === 'occupied') return '<i class="fa fa-refresh"></i> Cleaning';
      if(status === 'reserved') return '<i class="fa fa-check-circle"></i> Arrived';
      if(status === 'cleaning') return '<i class="fa fa-check-circle"></i> Done';
      return 'Update';
    }

    function openAdd(){
      document.getElementById('modalTitle').textContent = 'Add Table';
      document.getElementById('editId').value = '';
      document.getElementById('tableName').value = '';
      document.getElementById('tableCode').value = '';
      document.getElementById('capacity').value = '4';
      document.getElementById('zone').value = '';
      document.getElementById('status').value = 'available';
      document.getElementById('sortOrder').value = '0';
      document.getElementById('tableModal').classList.add('open');
    }

    function openEdit(id){
      var t = tables.find(function(x){ return parseInt(x.id) === id; });
      if(!t) return;
      document.getElementById('modalTitle').textContent = 'Edit Table';
      document.getElementById('editId').value = t.id;
      document.getElementById('tableName').value = t.table_name || '';
      document.getElementById('tableCode').value = t.table_code || '';
      document.getElementById('capacity').value = t.capacity || 4;
      document.getElementById('zone').value = t.zone || '';
      document.getElementById('status').value = t.status || 'available';
      document.getElementById('sortOrder').value = t.sort_order || 0;
      document.getElementById('tableModal').classList.add('open');
    }

    function closeModal(){
      document.getElementById('tableModal').classList.remove('open');
    }

    function toggleStatus(id, current){
      var next = { available: 'occupied', occupied: 'cleaning', reserved: 'occupied', cleaning: 'available' }[current] || 'available';
      var fd = new FormData();
      fd.append('table_id', id);
      fd.append('status', next);
      fd.append(csrf_token, csrf_hash);

      fetch(base_url + 'mobile/table_update_status', {
        method: 'POST',
        body: fd
      })
      .then(function(res){ return res.json(); })
      .then(function(data){
        if(data && data.success){
          window.location.reload();
        } else {
          alert(data && data.message ? data.message : 'Update failed');
        }
      })
      .catch(function(){ alert('Network error'); });
    }

    document.querySelector('.modal-overlay').addEventListener('click', function(e){
      if(e.target === this) closeModal();
    });
  </script>
</body>
</html>
