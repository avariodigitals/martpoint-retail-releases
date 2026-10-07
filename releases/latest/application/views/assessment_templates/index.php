<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.at-card{background:var(--mp-surface,#fff);border:1px solid var(--mp-border);border-radius:14px;padding:18px;margin-bottom:16px}
.at-card h4{margin:0 0 12px;font-size:15px;font-weight:700}
table.at{width:100%;border-collapse:collapse;font-size:13px}
table.at th{text-align:left;font-size:11px;text-transform:uppercase;color:var(--mp-muted);padding:6px 4px;border-bottom:1px solid var(--mp-border)}
table.at td{padding:8px 4px;border-bottom:1px solid var(--mp-border)}
.at-badge{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px}
.at-badge.active{background:#D1FAE5;color:#065F46}.at-badge.draft{background:#FEF3C7;color:#B45309}
.at-badge.archived{background:#F1F5F9;color:#64748B}
.at-btn{background:var(--mp-primary,#2563EB);color:#fff;border:0;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;cursor:pointer;margin-right:6px;text-decoration:none;display:inline-block}
.at-btn.ghost{background:#F1F5F9;color:#1F2937}
.at-in{padding:8px 10px;border:1px solid var(--mp-border);border-radius:8px;font-size:13px}
textarea.at-json{width:100%;font-family:monospace;font-size:12px;min-height:120px}
</style>
<div class="mp-page-head"><div><h2>Assessment Templates</h2>
  <div class="mp-page-sub">Draft → preview → publish. Publishing creates no edits to history — assessments keep the version they were recorded on.</div>
</div></div>

<?php foreach($groups as $key => $versions): $latest = $versions[0]; ?>
<div class="at-card">
  <h4><?= htmlspecialchars($latest->name); ?> <span style="font-family:monospace;color:var(--mp-muted);font-size:12px;font-weight:400">(<?= htmlspecialchars($key); ?>)</span></h4>
  <table class="at">
    <tr><th>Version</th><th>Status</th><th>Scope</th><th>Created</th><th></th></tr>
    <?php foreach($versions as $v): ?>
    <tr>
      <td>v<?= (int)$v->version; ?><?= $v->provisional ? ' <span class="at-badge draft">provisional</span>' : ''; ?></td>
      <td><span class="at-badge <?= $v->status; ?>"><?= $v->status; ?></span></td>
      <td><?= (int)$v->store_id === 0 ? 'Platform' : 'This store'; ?></td>
      <td><?= $v->created_at ? date('M j, Y', strtotime($v->created_at)) : '—'; ?></td>
      <td>
        <a class="at-btn ghost" href="<?= base_url('assessment_templates/preview/'.$v->id); ?>">Preview</a>
        <?php if((int)$v->store_id === (int)get_current_store_id() && $v->status === 'draft'): ?>
          <a class="at-btn" href="<?= base_url('assessment_templates/edit/'.$v->id); ?>">Edit draft</a>
          <button class="at-btn" onclick="publish(<?= (int)$v->id; ?>)">Publish</button>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <div style="margin-top:10px"><button class="at-btn ghost" onclick="newVersion('<?= htmlspecialchars($key); ?>')">New version (draft)</button></div>
</div>
<?php endforeach; ?>

<div class="at-card">
  <h4>New template (draft)</h4>
  <p style="font-size:12px;color:var(--mp-muted)">Sections JSON: array of <code>{"title":…, "fields":[{"key","label","type","required"}]}</code>.</p>
  <input class="at-in" id="nk" placeholder="template_key (lowercase_underscore)" style="max-width:280px"><br><br>
  <input class="at-in" id="nn" placeholder="Display name" style="max-width:400px"><br><br>
  <textarea class="at-json" id="ns" placeholder='[{"title":"Section","fields":[{"key":"chief_complaint","label":"Chief complaint","type":"text","required":true}]}]'></textarea>
  <br><br><button class="at-btn" onclick="createTpl()">Create draft</button>
</div>

<script src="<?= base_url('theme/assets/js/jquery.min.js'); ?>"></script>
<script>
var CSRF={'<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>'};
function post(url,d,cb){ $.post('<?= base_url(); ?>'+url, Object.assign(d||{},CSRF), function(r){alert(r.message); if(r.status==='success'&&r.id)location='<?= base_url('assessment_templates/edit/'); ?>'+r.id; else if(r.status==='success')location.reload();},'json'); }
function newVersion(k){ post('assessment_templates/new_version',{template_key:k}); }
function publish(id){ if(confirm('Publish this version? The current active store version will be archived; existing assessments keep their recorded version.')) post('assessment_templates/publish/'+id,{}); }
function createTpl(){ post('assessment_templates/create',{template_key:$('#nk').val(),name:$('#nn').val(),sections_json:$('#ns').val()}); }
</script>
