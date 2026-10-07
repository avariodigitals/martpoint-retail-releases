<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($enc->encounter_code); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-teal: #0D9488; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, sans-serif; background: var(--mp-bg); color: var(--mp-text); }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); text-decoration: none; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .card { background: #fff; border-radius: 14px; border: 1px solid var(--mp-border); padding: 12px 14px; margin-bottom: 10px; }
    .card h4 { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--mp-muted); margin: 0 0 8px; }
    .row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px dashed var(--mp-border); font-size: 12px; }
    .row:last-child { border-bottom: none; }
    .pill { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 12px; background: #F1F5F9; color: #475569; }
    .pill.final,.pill.completed,.pill.reviewed { background: #D1FAE5; color: #065F46; }
    .pill.draft { background: #FEF3C7; color: #92400E; }
    .pill.result_received,.pill.awaiting_verification { background: #DBEAFE; color: #1E40AF; }
    .pill.declined,.pill.cancelled { background: #FEE2E2; color: #991B1B; }
    .pill.superseded { background: #F5F3FF; color: #5B21B6; }
    .vt-nm { color: #991B1B; font-size: 10px; font-weight: 700; }
    .vrow { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
    .vrow .nm { flex: 0 0 110px; font-size: 12px; font-weight: 600; }
    .vrow input[type=text] { flex: 1; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; }
    .vrow .un { font-size: 11px; color: var(--mp-muted); width: 34px; }
    .vrow label.nmchk { font-size: 10px; color: #991B1B; display: flex; align-items: center; gap: 3px; }
    .btn { width: 100%; padding: 12px; border-radius: 12px; border: none; font-size: 14px; font-weight: 700; cursor: pointer; margin-top: 8px; }
    .btn.teal { background: var(--mp-teal); color: #fff; }
    .btn.line { background: #fff; border: 1px solid var(--mp-border); }
    .field { margin-bottom: 10px; }
    .field label { font-size: 12px; font-weight: 600; color: var(--mp-muted); display: block; margin-bottom: 5px; }
    .field textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; font-family: inherit; }
    .prov { background: #FEF3C7; border: 1px solid #F59E0B; color: #92400E; font-size: 11px; padding: 8px 10px; border-radius: 8px; margin-bottom: 10px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/care_queue'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($enc->encounter_code); ?></h1>
        </div>
      </div>
      <p style="margin:0 0 12px;font-size:13px;color:var(--mp-muted);">
        <b style="color:var(--mp-text);"><?= htmlspecialchars($patient->customer_name ?? ''); ?></b> · <?= htmlspecialchars($patient->patient_code ?? ''); ?> · <?= str_replace('_',' ',$enc->queue_stage); ?>
      </p>

      <?php if(physio_can('vitals_view')): ?>
      <div class="card">
        <h4>Nursing Intake — Vitals</h4>
        <?php foreach($vitals_sets as $vs): ?>
          <div class="row"><span>Set #<?= (int)$vs->id; ?> <span class="pill <?= $vs->status; ?>"><?= $vs->status; ?></span><br>
            <small style="color:var(--mp-muted);"><?= htmlspecialchars($vs->recorded_by_name ?: ''); ?> · <?= $vs->created_at; ?></small></span></div>
          <?php foreach($vs->entries as $e): ?>
            <div class="row"><span><?= htmlspecialchars($e->label ?: $e->vital_key); ?></span>
              <span><?= $e->not_measured ? '<span class="vt-nm">NOT MEASURED</span>' : htmlspecialchars($e->value_text . ' ' . $e->unit); ?></span></div>
          <?php endforeach; ?>
        <?php endforeach; ?>
        <?php if(empty($vitals_sets)): ?><div style="font-size:12px;color:var(--mp-muted);">No vitals yet.</div><?php endif; ?>

        <?php if($can['vitals_add'] && $enc->queue_stage !== 'closed'): ?>
        <form id="vtForm" onsubmit="return false;" style="margin-top:10px;">
          <?php
          $vitalsDef = array(
            array('bp_systolic','Systolic BP','mmHg'), array('bp_diastolic','Diastolic BP','mmHg'),
            array('pulse','Pulse','bpm'), array('temperature','Temp','°C'), array('spo2','SpO2','%'),
            array('resp_rate','Resp rate','/min'), array('weight','Weight','kg'), array('pain_score','Pain','/10'),
          );
          /*
           * Prefill from the OPEN DRAFT. Without this the form always started
           * blank, so the only way to "add" a reading was to save a whole new
           * set — which is how one intake ended up with a stack of draft sets.
           */
          $draftEntries = array();
          foreach(($vitals_draft_entries ?? array()) as $de){
            $draftEntries[(string)$de->vital_key] = $de;
          }
          foreach($vitalsDef as $vd):
            $de = $draftEntries[$vd[0]] ?? null;
            $deVal   = ($de && empty($de->not_measured)) ? (string)$de->value_text : '';
            $deChecked = ($de && !empty($de->not_measured)) ? ' checked' : '';
          ?>
            <div class="vrow" data-key="<?= $vd[0]; ?>" data-label="<?= $vd[1]; ?>" data-unit="<?= $vd[2]; ?>">
              <div class="nm"><?= $vd[1]; ?></div>
              <input type="text" class="vt-val" inputmode="decimal" value="<?= htmlspecialchars($deVal); ?>">
              <div class="un"><?= $vd[2]; ?></div>
              <label class="nmchk"><input type="checkbox" class="vt-nm"<?= $deChecked; ?>> N/M</label>
            </div>
          <?php endforeach; ?>
          <div class="field"><label>Intake notes</label><textarea id="vt_notes" rows="2"><?= htmlspecialchars((string)($vitals_draft->note ?? '')); ?></textarea></div>
          <?php if(!empty($vitals_draft)): ?>
            <div style="font-size:12px;color:var(--mp-muted);margin:0 0 8px;">
              Saving continues <strong>Set #<?= (int)$vitals_draft->id; ?></strong> (draft).
            </div>
          <?php endif; ?>
          <button type="button" class="btn line" onclick="saveVitals(0)">Save Draft</button>
          <button type="button" class="btn teal" onclick="saveVitals(1)">Finalise Vitals</button>
        </form>
        <?php endif; ?>
        <?php if($can['vitals_add'] && $enc->queue_stage === 'nursing_intake'): ?>
          <button class="btn teal" onclick="intakeDone()">Complete intake → hand to <?= htmlspecialchars(mp_label('staff')); ?></button>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if(physio_can('assessments_view')): ?>
      <div class="card">
        <h4>Assessments</h4>
        <div class="prov">Provisional template — pending the agreed five-page clinical form.</div>
        <?php foreach($assessments as $a): ?>
          <div class="row"><span><?= htmlspecialchars($a->template_key); ?> v<?= (int)$a->template_version; ?></span>
            <span class="pill <?= $a->status; ?>"><?= $a->status; ?></span></div>
        <?php endforeach; ?>
        <?php if(empty($assessments)): ?><div style="font-size:12px;color:var(--mp-muted);">None — complete on desktop or open this visit there.</div><?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if(physio_can('investigations_view')): ?>
      <div class="card">
        <h4>Investigations</h4>
        <?php foreach($investigations as $i): ?>
          <div class="row"><span><?= htmlspecialchars($i->request_ref); ?> <?= htmlspecialchars($i->test_name); ?></span>
            <span class="pill <?= $i->status; ?>"><?= str_replace('_',' ',$i->status); ?></span></div>
        <?php endforeach; ?>
        <?php if(empty($investigations)): ?><div style="font-size:12px;color:var(--mp-muted);">None.</div><?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if($can['docs_upload'] || physio_can('patient_docs_view')): ?>
      <div class="card">
        <h4>Consents &amp; Documents</h4>
        <?php foreach($consents as $c): ?>
          <div class="row"><span><?= htmlspecialchars($c->title); ?></span>
            <span class="pill <?= $c->status; ?>"><?= str_replace('_',' ',$c->status); ?></span></div>
        <?php endforeach; ?>
        <?php foreach($documents as $d): ?>
          <div class="row"><span><?= htmlspecialchars($d->title); ?> <small style="color:var(--mp-muted);">v<?= (int)$d->version_no; ?></small></span>
            <a href="<?= base_url('patient_docs/download/' . (int)$d->id); ?>" style="color:var(--mp-primary);"><i class="fa fa-download"></i></a></div>
        <?php endforeach; ?>
        <?php if(empty($consents) && empty($documents)): ?><div style="font-size:12px;color:var(--mp-muted);">None.</div><?php endif; ?>
      </div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    var CSRF_NAME = '<?= $this->security->get_csrf_token_name(); ?>';
    var CSRF_HASH = '<?= $this->security->get_csrf_hash(); ?>';
    var ENC_ID = <?= (int)$enc->id; ?>;

    function saveVitals(finalize){
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH);
      fd.append('finalize', finalize ? 1 : 0);
      <?php if(!empty($vitals_draft)): ?>
      // Continue the open draft rather than inserting yet another set.
      fd.append('vitals_set_id', <?= (int)$vitals_draft->id; ?>);
      <?php endif; ?>
      fd.append('vitals_notes', document.getElementById('vt_notes').value);
      var i = 0, ok = false;
      document.querySelectorAll('#vtForm .vrow').forEach(function(r){
        var nm = r.querySelector('.vt-nm').checked, val = r.querySelector('.vt-val').value;
        if(!nm && val === '') return;
        fd.append('vit_key['+i+']', r.dataset.key);
        fd.append('vit_label['+i+']', r.dataset.label);
        fd.append('vit_value['+i+']', val);
        fd.append('vit_unit['+i+']', r.dataset.unit);
        fd.append('vit_nm['+i+']', nm ? 1 : 0);
        i++;
      });
      if(i === 0){ mpError('Enter at least one vital or mark it not measured'); return; }
      mpFetchJson('<?= base_url('mobile/care_vitals/'); ?>' + ENC_ID, { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && d.message) || 'Failed'); } });
    }
    function intakeDone(){
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH);
      mpFetchJson('<?= base_url('mobile/care_intake_complete/'); ?>' + ENC_ID, { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && d.message) || 'Failed'); } });
    }
  </script>
</body>
</html>
