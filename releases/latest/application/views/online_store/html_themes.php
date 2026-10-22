<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.mp-theme-manager{display:grid;gap:18px}.mp-theme-manager .mp-card-body{padding:20px}.mp-theme-table{width:100%;border-collapse:collapse}.mp-theme-table th,.mp-theme-table td{padding:12px 10px;border-bottom:1px solid var(--mp-border);text-align:left;vertical-align:middle}.mp-theme-badge{display:inline-block;padding:4px 8px;border-radius:999px;background:#E7F7EF;color:#136C42;font-size:11px;font-weight:700}.mp-theme-warning{margin-top:8px;padding:8px 10px;background:#FFF7ED;color:#9A3412;border-radius:8px;font-size:12px}.mp-theme-actions{display:flex;gap:8px;flex-wrap:wrap}.mp-theme-actions form{display:inline}.mp-theme-help{color:var(--mp-muted);font-size:13px;line-height:1.55}.mp-theme-manager code{font-size:12px}
</style>
<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2><div class="mp-page-sub">Upload safe HTML/CSS/JavaScript storefront packages without changing your catalogue or checkout backend.</div></div>
  <div><a href="<?= base_url('online_store/appearance'); ?>" class="mp-qa-btn">Back to Appearance</a> <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" target="_blank" class="mp-qa-btn blue">View live store</a></div>
</div>
<div class="mp-theme-manager">
  <div class="mp-card-form"><div class="mp-card-head"><h3><i class="fa fa-upload"></i> Upload a theme ZIP</h3></div><div class="mp-card-body">
    <p class="mp-theme-help">Packages are validated before they are installed. PHP, server configuration files, unsafe paths and unsupported file types are rejected. Uploading never activates a theme.</p>
    <form id="mp-theme-upload" action="<?= base_url('online_store/upload_theme'); ?>" method="post" enctype="multipart/form-data">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input class="mp-form-control" type="file" name="theme_zip" accept=".zip,application/zip" required>
      <button class="mp-btn-primary" type="submit" style="margin-top:12px;"><i class="fa fa-check"></i> Validate &amp; upload</button>
      <span id="mp-theme-upload-status" class="mp-theme-help" style="margin-left:10px;"></span>
    </form>
  </div></div>
  <div class="mp-card-form"><div class="mp-card-head"><h3><i class="fa fa-history"></i> Installed HTML themes</h3></div><div class="mp-card-body">
    <?php if(empty($uploaded_themes)): ?><p class="mp-theme-help">No uploadable themes are installed yet. Existing MartPoint templates remain unchanged.</p><?php else: ?>
    <table class="mp-theme-table"><thead><tr><th>Theme</th><th>Version</th><th>Status</th><th>Actions</th></tr></thead><tbody>
      <?php foreach($uploaded_themes as $uploaded): $isCurrent = $current_theme && ($current_theme->theme_key ?? '') === ($uploaded->theme_key ?? ''); $warnings = json_decode($uploaded->unsupported_capabilities_json ?? '[]', true); ?>
      <tr><td><strong><?= htmlspecialchars($uploaded->theme_name); ?></strong><br><code><?= htmlspecialchars($uploaded->theme_key); ?></code><?php if(!empty($warnings)): ?><div class="mp-theme-warning">Unsupported for this store: <?= htmlspecialchars(implode(', ', $warnings)); ?></div><?php endif; ?></td><td><?= htmlspecialchars($uploaded->theme_version); ?></td><td><?= $isCurrent ? '<span class="mp-theme-badge">Active</span>' : '<span class="mp-theme-help">Uploaded</span>'; ?></td><td><div class="mp-theme-actions"><a class="mp-qa-btn blue" target="_blank" href="<?= base_url('online_store/preview_html_theme/' . (int)$uploaded->theme_id); ?>">Preview</a><?php if(!$isCurrent): ?><button class="mp-qa-btn" type="button" onclick="activateHtmlTheme(<?= (int)$uploaded->theme_id; ?>)">Activate</button><?php endif; ?></div></td></tr>
      <?php endforeach; ?>
    </tbody></table>
    <?php endif; ?>
    <?php if(!empty($settings->previous_theme_id)): ?><div style="margin-top:16px"><button type="button" class="mp-qa-btn" onclick="restorePreviousTheme()"><i class="fa fa-undo"></i> Restore previous theme</button></div><?php endif; ?>
  </div></div>
  <div class="mp-card-form"><div class="mp-card-head"><h3><i class="fa fa-book"></i> Authoring contract</h3></div><div class="mp-card-body"><p class="mp-theme-help">See <a href="<?= base_url('docs/theme-authoring.md'); ?>">docs/theme-authoring.md</a> in the source package for the exact manifest, bindings and backend actions. A sample pharmacy ZIP is included under <code>resources/themes/</code>.</p></div></div>
</div>
<script>
(function(){var csrfName=<?= json_encode($this->security->get_csrf_token_name()); ?>,csrfHash=<?= json_encode($this->security->get_csrf_hash()); ?>;
document.getElementById('mp-theme-upload').addEventListener('submit',function(e){e.preventDefault();var s=document.getElementById('mp-theme-upload-status');s.textContent='Validating…';fetch(this.action,{method:'POST',body:new FormData(this),credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){s.textContent=j.message||'Done';if(j.status==='success')setTimeout(function(){location.reload();},700);}).catch(function(){s.textContent='Upload failed. Please try again.';});});
window.activateHtmlTheme=function(id){var body=new URLSearchParams();body.append(csrfName,csrfHash);fetch('<?= base_url('online_store/activate_html_theme'); ?>/'+id,{method:'POST',body:body,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){if(j.redirect)location.href=j.redirect;else alert(j.message||'Could not activate theme.');});};
window.restorePreviousTheme=function(){var body=new URLSearchParams();body.append(csrfName,csrfHash);fetch('<?= base_url('online_store/restore_previous_theme'); ?>',{method:'POST',body:body,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){if(j.redirect)location.href=j.redirect;else alert(j.message||'Could not restore theme.');});};})();
</script>
