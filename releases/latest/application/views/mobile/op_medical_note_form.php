<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
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
    .section { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 12px; }
    .section h3 { font-size: 13px; font-weight: 700; margin: 0 0 12px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
    .field:last-child { margin-bottom: 0; }
    .field label { font-size: 13px; color: var(--mp-muted); font-weight: 600; }
    .field input[type=text], .field input[type=number], .field input[type=date], .field input[type=file], .field textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-family: inherit; font-size: 15px; background: #fff; color: var(--mp-ink); }
    .field textarea { min-height: 80px; resize: vertical; }
    .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; }
    .mp-select-trigger { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; font-size: 15px; cursor: pointer; }
    .mp-select-options { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .mp-select-options.open { display: block; }
    .mp-option { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .mp-option:last-child { border-bottom: none; }
    .mp-option.active { background: #E0E7FF; color: var(--mp-primary); font-weight: 600; }
    .ac-wrap { position: relative; }
    .ac-results { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 210; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; max-height: 200px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .ac-results.open { display: block; }
    .ac-item { padding: 11px 14px; font-size: 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; }
    .ac-item:last-child { border-bottom: none; }
    .ac-item small { color: var(--mp-muted); }
    .med-card { border: 1px solid var(--mp-border); border-radius: 12px; padding: 10px; margin-bottom: 10px; background: var(--mp-bg); }
    .med-card .hd { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .med-card .nm { font-size: 14px; font-weight: 700; }
    .med-card .rm { width: 30px; height: 30px; border: none; border-radius: 8px; background: #FEE2E2; color: var(--mp-danger); cursor: pointer; }
    .med-card .grid3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px; }
    .med-card input { width: 100%; padding: 8px 10px; border: 1px solid var(--mp-border); border-radius: 8px; font-size: 13px; background: #fff; }
    .save-btn { width: 100%; padding: 16px; border: none; border-radius: 14px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 4px; }
    .save-btn:disabled { opacity: 0.6; }
    .toast { position: fixed; left: 50%; bottom: 110px; transform: translateX(-50%); background: #111827; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 13px; display: none; z-index: 300; max-width: 90%; }
    .hint { font-size: 12px; color: var(--mp-muted); }
    .file-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 10px; background: var(--mp-bg); font-size: 12px; margin-top: 6px; color: var(--mp-ink); text-decoration: none; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/medical_notes'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <form id="mnForm" method="post" action="<?= base_url('mobile/medical_note_save'); ?>" enctype="multipart/form-data" onsubmit="return saveNote(event);" autocomplete="off">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <?php if($edit_note): ?><input type="hidden" name="id" value="<?= (int)$edit_note->id; ?>"><?php endif; ?>

        <div class="section">
          <h3>Patient & Prescriber</h3>
          <div class="field">
            <label>Patient *</label>
            <select name="customer_id" class="mp-select" required>
              <option value="">Select patient</option>
              <?php foreach($customers as $c): ?>
              <option value="<?= (int)$c->id; ?>" <?= ($edit_note && $edit_note->customer_id == $c->id) || (!$edit_note && $preselect_customer_id == $c->id) ? 'selected' : ''; ?>><?= htmlspecialchars($c->customer_name); ?><?= !empty($c->mobile) ? ' — '.$c->mobile : ''; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="grid2">
            <div class="field"><label>Prescribing Doctor</label><input type="text" name="prescribing_doctor" value="<?= $edit_note ? htmlspecialchars($edit_note->prescribing_doctor ?? '') : ''; ?>"></div>
            <div class="field"><label>Doctor Contact</label><input type="text" name="doctor_contact" value="<?= $edit_note ? htmlspecialchars($edit_note->doctor_contact ?? '') : ''; ?>"></div>
            <div class="field"><label>Prescription Ref</label><input type="text" name="prescription_ref" value="<?= $edit_note ? htmlspecialchars($edit_note->prescription_ref ?? '') : ''; ?>"></div>
            <div class="field"><label>Note Date *</label><input type="date" name="note_date" value="<?= $edit_note ? htmlspecialchars($edit_note->note_date) : date('Y-m-d'); ?>" required></div>
          </div>
          <div class="field"><label>Allergies Flagged</label><input type="text" name="allergies_flagged" placeholder="e.g. Penicillin" value="<?= $edit_note ? htmlspecialchars($edit_note->allergies_flagged ?? '') : ''; ?>"></div>
          <div class="field">
            <label>Attending <?= htmlspecialchars(mp_label('staff')); ?></label>
            <select name="staff_id" class="mp-select">
              <option value="">Select</option>
              <?php foreach($staff as $u): ?>
              <option value="<?= (int)$u->id; ?>" <?= ($edit_note && $edit_note->staff_id == $u->id) ? 'selected' : ''; ?>><?= htmlspecialchars(trim($u->first_name.' '.$u->last_name) ?: $u->username); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Prescription Image / PDF</label>
            <input type="file" name="prescription_file" accept="image/*,application/pdf">
            <?php if($edit_note && !empty($edit_note->prescription_file)): ?>
            <a class="file-chip" href="<?= base_url($edit_note->prescription_file); ?>" target="_blank"><i class="fa fa-file-image-o"></i> Current file</a>
            <?php endif; ?>
          </div>
        </div>

        <div class="section">
          <h3>Clinical</h3>
          <div class="field"><label>Diagnosis</label><textarea name="diagnosis"><?= $edit_note ? htmlspecialchars($edit_note->diagnosis ?? '') : ''; ?></textarea></div>
          <div class="field"><label>Dosage Instructions</label><textarea name="dosage_instructions"><?= $edit_note ? htmlspecialchars($edit_note->dosage_instructions ?? '') : ''; ?></textarea></div>
          <div class="field"><label>Counselling Notes</label><textarea name="counselling_notes"><?= $edit_note ? htmlspecialchars($edit_note->counselling_notes ?? '') : ''; ?></textarea></div>
        </div>

        <div class="section">
          <h3>Refills</h3>
          <div class="grid2">
            <div class="field"><label>Refills Remaining</label><input type="number" name="refills_remaining" min="0" value="<?= $edit_note ? (int)$edit_note->refills_remaining : 0; ?>"></div>
            <div class="field"><label>Next Refill Date</label><input type="date" name="next_refill_date" value="<?= ($edit_note && !empty($edit_note->next_refill_date)) ? htmlspecialchars($edit_note->next_refill_date) : ''; ?>"></div>
          </div>
        </div>

        <div class="section">
          <h3>Prescribed Items</h3>
          <div class="field ac-wrap">
            <label>Add item</label>
            <input type="text" id="itemSearch" placeholder="Search items…" autocomplete="off">
            <div class="ac-results" id="itemResults"></div>
          </div>
          <div id="medRows">
            <?php if($edit_note && !empty($edit_note->items)): foreach($edit_note->items as $it): ?>
            <div class="med-card">
              <input type="hidden" name="item_id[]" value="<?= (int)$it->item_id; ?>">
              <div class="hd"><span class="nm"><?= htmlspecialchars($it->item_name ?? 'Item #'.$it->item_id); ?></span><button type="button" class="rm" onclick="this.closest('.med-card').remove()"><i class="fa fa-times"></i></button></div>
              <div class="grid3">
                <input type="number" step="any" name="item_qty[]" value="<?= htmlspecialchars($it->qty); ?>" placeholder="Qty">
                <input type="text" name="item_dosage[]" value="<?= htmlspecialchars($it->dosage ?? ''); ?>" placeholder="Dosage">
                <input type="text" name="item_duration[]" value="<?= htmlspecialchars($it->duration ?? ''); ?>" placeholder="Duration">
              </div>
              <input type="text" name="item_instructions[]" value="<?= htmlspecialchars($it->instructions ?? ''); ?>" placeholder="Instructions" style="margin-top:6px;">
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <button type="submit" class="save-btn" id="saveBtn"><i class="fa fa-check"></i> <?= $edit_note ? 'Update Note' : 'Save Note'; ?></button>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var csrf_token = '<?= $this->security->get_csrf_token_name(); ?>';

    function showToast(msg){ var t = document.getElementById('toast'); t.textContent = msg; t.style.display = 'block'; setTimeout(function(){ t.style.display = 'none'; }, 3000); }

    document.querySelectorAll('select.mp-select').forEach(function(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap';
      sel.parentNode.insertBefore(wrap, sel); wrap.appendChild(sel);
      var trigger = document.createElement('div'); trigger.className = 'mp-select-trigger';
      var opts = document.createElement('div'); opts.className = 'mp-select-options';
      var label = document.createElement('span');
      var icon = document.createElement('i'); icon.className = 'fa fa-chevron-down'; icon.style.fontSize = '12px';
      trigger.appendChild(label); trigger.appendChild(icon);
      wrap.appendChild(trigger); wrap.appendChild(opts);
      function setLabel(){ label.textContent = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : 'Select'; }
      Array.from(sel.options).forEach(function(opt, idx){
        var d = document.createElement('div'); d.className = 'mp-option'; d.textContent = opt.text;
        if(idx === sel.selectedIndex) d.classList.add('active');
        d.addEventListener('click', function(e){
          e.stopPropagation(); sel.selectedIndex = idx; setLabel();
          opts.querySelectorAll('.mp-option').forEach(function(o){ o.classList.remove('active'); });
          d.classList.add('active'); opts.classList.remove('open');
          sel.dispatchEvent(new Event('change'));
        });
        opts.appendChild(d);
      });
      setLabel();
      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        document.querySelectorAll('.mp-select-options.open').forEach(function(o){ if(o !== opts) o.classList.remove('open'); });
        opts.classList.toggle('open');
      });
    });
    document.addEventListener('click', function(){ document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); }); });

    /* ---------- Item autocomplete ---------- */
    var timer = null;
    var results = document.getElementById('itemResults');
    document.getElementById('itemSearch').addEventListener('input', function(){
      var term = this.value.trim();
      clearTimeout(timer);
      if(term.length < 2){ results.classList.remove('open'); return; }
      timer = setTimeout(function(){
        mpFetchJson(base_url + 'mobile/medical_items?term=' + encodeURIComponent(term))
          .catch(function(){ return []; })
          .then(function(list){
            results.innerHTML = '';
            if(!list || !list.length){ results.classList.remove('open'); return; }
            list.forEach(function(it){
              var d = document.createElement('div'); d.className = 'ac-item';
              d.innerHTML = it.text + ' <small>· stock ' + it.stock + '</small>';
              d.addEventListener('click', function(){
                addItem(it.id, it.text);
                results.classList.remove('open');
                document.getElementById('itemSearch').value = '';
              });
              results.appendChild(d);
            });
            results.classList.add('open');
          });
      }, 250);
    });
    function addItem(id, name){
      var holder = document.getElementById('medRows');
      if(holder.querySelector('input[name="item_id[]"][value="' + id + '"]')){ showToast('Already added.'); return; }
      var card = document.createElement('div'); card.className = 'med-card';
      card.innerHTML = '<input type="hidden" name="item_id[]" value="' + id + '">' +
        '<div class="hd"><span class="nm">' + name + '</span><button type="button" class="rm" onclick="this.closest(\'.med-card\').remove()"><i class="fa fa-times"></i></button></div>' +
        '<div class="grid3">' +
        '<input type="number" step="any" name="item_qty[]" value="1" placeholder="Qty">' +
        '<input type="text" name="item_dosage[]" placeholder="Dosage">' +
        '<input type="text" name="item_duration[]" placeholder="Duration">' +
        '</div>' +
        '<input type="text" name="item_instructions[]" placeholder="Instructions" style="margin-top:6px;">';
      holder.appendChild(card);
    }
    document.addEventListener('click', function(e){
      if(!document.getElementById('itemSearch').contains(e.target) && !results.contains(e.target)){ results.classList.remove('open'); }
    });

    /* ---------- Save ---------- */
    function saveNote(e){
      e.preventDefault();
      var btn = document.getElementById('saveBtn'); btn.disabled = true;
      var form = document.getElementById('mnForm');
      mpFetchJson(form.action, { method: 'POST', body: new FormData(form) })
        .then(function(d){
          if(d && d.csrf_hash){
            var ci = form.querySelector('input[name="<?= $this->security->get_csrf_token_name(); ?>"]');
            if(ci) ci.value = d.csrf_hash;
          }
          if(d && d.success){
            showToast(d.message || 'Saved.');
            setTimeout(function(){ window.location.href = base_url + 'mobile/medical_notes'; }, 600);
          } else {
            showToast(d && d.message ? d.message : 'Could not save this note. Please check the details and try again.');
            btn.disabled = false;
          }
        })
        .catch(function(err){ showToast(mpErrorText(err)); btn.disabled = false; });
      return false;
    }
  </script>
</body>
</html>
