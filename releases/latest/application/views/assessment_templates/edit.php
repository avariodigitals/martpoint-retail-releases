<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.at-card{background:var(--mp-surface,#fff);border:1px solid var(--mp-border);border-radius:14px;padding:18px;margin-bottom:16px}
.at-in{padding:8px 10px;border:1px solid var(--mp-border);border-radius:8px;font-size:13px;width:100%;max-width:480px}
textarea.at-json{width:100%;font-family:monospace;font-size:12px;min-height:340px}
.at-btn{background:var(--mp-primary,#2563EB);color:#fff;border:0;border-radius:8px;padding:7px 16px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block}
.at-btn.ghost{background:#F1F5F9;color:#1F2937}
</style>
<div class="mp-page-head"><div><h2><?= $t->status==='draft' ? 'Edit draft' : 'Template'; ?> — <?= htmlspecialchars($t->name); ?> v<?= (int)$t->version; ?></h2>
  <div class="mp-page-sub"><a href="<?= base_url('assessment_templates'); ?>">&larr; Templates</a>
    &nbsp;·&nbsp; <a href="<?= base_url('assessment_templates/preview/'.$t->id); ?>">Preview</a></div>
</div></div>
<div class="at-card">
<?php if($t->status === 'draft'): ?>
  <label style="font-size:12px;color:var(--mp-muted)">Name</label><br>
  <input class="at-in" id="tn" value="<?= htmlspecialchars($t->name); ?>"><br><br>
  <label style="font-size:12px;color:var(--mp-muted)">Sections JSON</label><br>
  <textarea class="at-json" id="tj"><?= htmlspecialchars($t->sections_json); ?></textarea><br><br>
  <label style="font-size:13px"><input type="checkbox" id="tp" <?= $t->provisional ? 'checked' : ''; ?>> Provisional (still being validated clinically)</label><br><br>
  <button class="at-btn" onclick="save()">Save draft</button>
  <a class="at-btn ghost" href="<?= base_url('assessment_templates/preview/'.$t->id); ?>">Preview</a>
<?php else: ?>
  <p style="font-size:13px;color:var(--mp-muted)">This version is <b><?= $t->status; ?></b> — published versions are immutable. Create a new version from the templates list to make changes.</p>
  <textarea class="at-json" readonly><?= htmlspecialchars($t->sections_json); ?></textarea>
<?php endif; ?>
</div>
<div class="at-card">
  <h4 style="margin:0 0 10px;font-size:13px;text-transform:uppercase;color:var(--mp-muted)">Version history — <?= htmlspecialchars($t->template_key); ?></h4>
  <ul style="font-size:13px;margin:0;padding-left:18px">
    <?php foreach($versions as $v): ?><li>v<?= (int)$v->version; ?> — <?= $v->status; ?><?= (int)$v->store_id===0?' (platform)':''; ?></li><?php endforeach; ?>
  </ul>
</div>
<script src="<?= base_url('theme/assets/js/jquery.min.js'); ?>"></script>
<script>
var CSRF={'<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>'};
function save(){ $.post('<?= base_url('assessment_templates/save/'.$t->id); ?>', Object.assign({name:$('#tn').val(),sections_json:$('#tj').val(),provisional:$('#tp').is(':checked')?1:0},CSRF), function(r){alert(r.message);},'json'); }
</script>
