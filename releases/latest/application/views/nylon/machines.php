<?php $this->load->view('admin/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars(mp_label('machine','Machines')); ?></h2>
    <div class="mp-page-sub">Extruders, printers, cutters and packers used on production jobs</div>
  </div>
</div>

<div class="mp-form-grid" style="grid-template-columns:1fr 2fr;gap:20px;align-items:start;">
  <?php if ($can_edit): ?>
  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-plus-circle"></i> Add Machine</h3></div>
    <div class="mp-card-body">
      <form onsubmit="return nySaveMachine(event);">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <label>Code</label><input type="text" name="machine_code" class="form-control" placeholder="EX-01">
        <label>Name *</label><input type="text" name="machine_name" class="form-control" required>
        <label>Type</label>
        <select name="machine_type" class="form-control">
          <?php foreach ($types as $k=>$l): ?><option value="<?= $k; ?>"><?= $l; ?></option><?php endforeach; ?>
        </select>
        <label>Notes</label><input type="text" name="notes" class="form-control">
        <div style="margin-top:10px;"><button type="submit" class="mp-qa-btn"><i class="fa fa-save"></i> Save</button></div>
        <div id="nyMachMsg" style="margin-top:8px;font-size:13px;"></div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-list"></i> Machines</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <table class="mp-static-table">
        <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Notes</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($machines)): ?><tr><td colspan="5" style="padding:16px;color:var(--mp-muted);">No machines registered.</td></tr><?php endif; ?>
        <?php foreach ($machines as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m->machine_code); ?></td>
            <td><strong><?= htmlspecialchars($m->machine_name); ?></strong></td>
            <td><span class="label label-info"><?= $types[$m->machine_type] ?? $m->machine_type; ?></span></td>
            <td><small class="text-muted"><?= htmlspecialchars($m->notes); ?></small></td>
            <td><?php if ($can_edit): ?><button class="btn btn-xs btn-danger" onclick="nyDelMachine(<?= $m->id; ?>)"><i class="fa fa-trash"></i></button><?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
var NY_CSRF_NAME = <?= json_encode($this->security->get_csrf_token_name()); ?>;
function nySaveMachine(ev){
  ev.preventDefault();
  var f = ev.target;
  fetch('<?= base_url('nylon/machine_save'); ?>', {method:'POST', body:new FormData(f)})
    .then(function(r){return r.json();})
    .then(function(d){ if(d.csrf_hash){f.querySelector('input[name="'+NY_CSRF_NAME+'"]').value=d.csrf_hash;} if(d.success){location.reload();} else {document.getElementById('nyMachMsg').innerHTML='<span class="text-danger">'+d.message+'</span>';} });
  return false;
}
function nyDelMachine(id){
  if(!confirm('Remove this machine?')) return;
  var fd = new FormData(); fd.append('id',id); fd.append(NY_CSRF_NAME, <?= json_encode($this->security->get_csrf_hash()); ?>);
  fetch('<?= base_url('nylon/machine_delete'); ?>', {method:'POST', body:fd}).then(function(r){return r.json();}).then(function(d){ if(d.success) location.reload(); else alert(d.message); });
}
</script>
