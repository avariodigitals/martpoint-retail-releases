<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.tp-form{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:18px!important;max-width:860px!important}
.tp-form label{font-size:12px!important;font-weight:700!important;color:var(--mp-muted)!important;display:block!important;margin:10px 0 4px!important}
.tp-form input,.tp-form select,.tp-form textarea{width:100%!important;padding:8px 10px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:13px!important}
.tp-line{display:grid!important;grid-template-columns:2fr 1fr 1fr 1fr auto!important;gap:8px!important;margin-bottom:8px!important;align-items:end!important}
</style>

<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2>
  <div class="mp-page-sub">Prices are snapshotted at write time — later service repricing won't change the plan</div></div>
</div>

<div class="tp-form">
  <label>Title</label>
  <input type="text" id="tp_title" placeholder="e.g. Post-op knee rehabilitation — 6 weeks">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
    <div>
      <label>Clinician</label>
      <select id="tp_clinician">
        <?php foreach($clinicians as $u): ?>
        <option value="<?= (int)$u->id; ?>" <?= $u->id == $this->session->userdata('inv_userid') ? 'selected' : ''; ?>>
          <?= htmlspecialchars(trim($u->first_name . ' ' . $u->last_name) ?: $u->username); ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Care setting</label>
      <select id="tp_setting"><option value="outpatient">Outpatient</option><option value="inpatient">Inpatient</option></select>
    </div>
    <div>
      <label>Linked assessment</label>
      <select id="tp_assessment"><option value="">— none —</option>
        <?php foreach($assessments as $a): ?>
        <option value="<?= (int)$a->id; ?>">Assessment #<?= (int)$a->id; ?> (<?= htmlspecialchars($a->status); ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Care episode</label>
      <select id="tp_episode"><option value="">— none —</option>
        <?php foreach($episodes as $e): ?>
        <option value="<?= (int)$e->id; ?>">Episode #<?= (int)$e->id; ?> — <?= htmlspecialchars($e->status); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <label>Goals</label>
  <textarea id="tp_goals" rows="2" placeholder="Treatment goals…"></textarea>

  <label>Review points (one per line: e.g. "After session 3 — re-measure ROM")</label>
  <textarea id="tp_reviews" rows="2"></textarea>

  <label>Service lines</label>
  <div id="tp_lines"></div>
  <button type="button" class="mp-qa-btn blue" onclick="addLine()"><i class="fa fa-plus"></i> Add service</button>

  <div style="margin-top:16px;display:flex;gap:10px;">
    <button class="mp-qa-btn green" onclick="savePlan('draft')">Save Draft</button>
    <button class="mp-qa-btn" onclick="savePlan('active')">Save &amp; Activate</button>
  </div>
</div>

<script>
var SERVICES = <?= json_encode(array_map(function($s){ return ['id'=>$s->id,'name'=>$s->item_name,'price'=>$s->sales_price]; }, $services)); ?>;

function addLine(){
  var d = document.createElement('div');
  d.className = 'tp-line';
  var opts = SERVICES.map(function(s){ return '<option value="'+s.id+'" data-price="'+s.price+'">'+s.name+'</option>'; }).join('');
  d.innerHTML = '<div><label>Service</label><select class="li_item">'+opts+'</select></div>'
    + '<div><label>Qty</label><input class="li_qty" type="number" min="1" value="1"></div>'
    + '<div><label>Sessions/unit</label><input class="li_spu" type="number" min="1" value="1"></div>'
    + '<div><label>Unit price</label><input class="li_price" type="number" min="0" step="0.01"></div>'
    + '<button type="button" class="mp-qa-btn" onclick="this.parentNode.remove()">×</button>';
  var sel = d.querySelector('.li_item'), pr = d.querySelector('.li_price');
  function sync(){ var o = sel.options[sel.selectedIndex]; if(o) pr.value = o.getAttribute('data-price') || ''; }
  sel.onchange = sync; sync();
  document.getElementById('tp_lines').appendChild(d);
}
addLine();

function savePlan(status){
  var items = [];
  document.querySelectorAll('#tp_lines .tp-line').forEach(function(l){
    items.push({
      item_id: l.querySelector('.li_item').value,
      qty: parseFloat(l.querySelector('.li_qty').value) || 1,
      sessions_per_unit: parseFloat(l.querySelector('.li_spu').value) || 1,
      unit_price: parseFloat(l.querySelector('.li_price').value) || 0,
      funding: 'bill'
    });
  });
  var reviews = document.getElementById('tp_reviews').value.split('\n').filter(function(x){return x.trim();})
    .map(function(x){ return {note: x.trim()}; });
  $.post('<?= base_url('treatment_plans/save'); ?>', {
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>',
    patient_id: <?= (int)$patient->id; ?>,
    title: document.getElementById('tp_title').value,
    clinician_id: document.getElementById('tp_clinician').value,
    care_setting: document.getElementById('tp_setting').value,
    assessment_id: document.getElementById('tp_assessment').value,
    episode_id: document.getElementById('tp_episode').value,
    goals: document.getElementById('tp_goals').value,
    review_points: JSON.stringify(reviews),
    items: JSON.stringify(items),
    status: status
  }, function(r){
    if(r.status === 'success'){ location.href = '<?= base_url('treatment_plans/view/'); ?>' + r.plan_id; }
    else { alert(r.message); }
  }, 'json');
}
</script>
