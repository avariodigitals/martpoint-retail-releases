<?php
$groups=[]; foreach($content_fields as $key=>$field) $groups[$field['group']][$key]=$field;
$esc=function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');};
?>
<style>.html-content{max-width:920px;margin:auto;font:inherit}.html-content a{color:#146b5a}.html-content fieldset{border:1px solid #dce3e0;border-radius:12px;padding:20px;margin:20px 0;background:#fff}.html-content legend{font-weight:700}.html-content label{display:block;margin-bottom:6px}.html-content input:not([type=checkbox]):not([type=hidden]),.html-content textarea{box-sizing:border-box;width:100%;padding:12px;border:1px solid #b7c4bd;border-radius:8px;font:inherit}.html-content .field{margin:16px 0}.html-content .actions{display:flex;gap:16px;align-items:center;flex-wrap:wrap}.html-content button{background:#146b5a;color:#fff;border:0;border-radius:8px;padding:12px 18px;font:inherit;cursor:pointer}.html-content [role=status]{line-height:1.5}</style>
<section class="html-content">
<h2><?= $esc($html_theme->theme_name); ?></h2>
<p>Edit this website’s wording and images. You can paste an image URL or upload a replacement. Products, stock, locations and payment options continue to come from Online Store Control.</p>
<?php if(($html_theme->theme_key ?? '')==='martpoint_gadgets'): ?><p><strong>For the banner shown on the homepage:</strong> open <em>Homepage</em> and change <em>Feature heading</em>, <em>Feature text</em> and <em>Feature image</em>. The larger top section uses the fields labelled Hero.</p><?php endif; ?>
<p><a href="<?= base_url('online_store/html_themes'); ?>">HTML themes</a> · <a href="<?= base_url('online_store/preview_html_theme/'.(int)$html_theme->id); ?>" target="_blank" rel="noopener">Preview this website</a></p>
<?php if(!$content_fields): ?><p>This package has no editable fields. Add settings and an optional settings_schema to its manifest.</p><?php else: ?>
<form id="html-theme-content" action="<?= base_url('online_store/save_html_theme_content/'.(int)$html_theme->id); ?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="<?= $esc($this->security->get_csrf_token_name()); ?>" value="<?= $esc($this->security->get_csrf_hash()); ?>" data-content-csrf>
<?php foreach($groups as $group=>$fields): ?><fieldset><legend><?= $esc($group); ?></legend>
<?php foreach($fields as $key=>$field): ?><div class="field">
<?php if($field['type']==='boolean'): ?>
<input type="hidden" name="content[<?= $esc($key); ?>]" value="0"><label><input type="checkbox" name="content[<?= $esc($key); ?>]" value="1" <?= !empty($content_values[$key])?'checked':''; ?>> <?= $esc($field['label']); ?></label>
<?php else: ?><label for="website-<?= $esc($key); ?>"><?= $esc($field['label']); ?></label>
<?php if($field['type']==='textarea'): ?><textarea id="website-<?= $esc($key); ?>" name="content[<?= $esc($key); ?>]" rows="4" maxlength="<?= (int)$field['max_length']; ?>"><?= $esc($content_values[$key]); ?></textarea>
<?php elseif($field['type']==='image'): ?><input id="website-<?= $esc($key); ?>" type="text" name="content[<?= $esc($key); ?>]" maxlength="<?= (int)$field['max_length']; ?>" value="<?= $esc($content_values[$key]); ?>" placeholder="Paste an image URL, or upload a file below"><label for="upload-<?= $esc($key); ?>">Upload a replacement image</label><input id="upload-<?= $esc($key); ?>" type="file" name="theme_image_<?= $esc($key); ?>" accept="image/jpeg,image/png,image/webp,image/avif,.avif"><small>JPG, PNG, WebP or AVIF, up to 3 MB. A new upload takes priority over the URL.</small>
<?php else: ?><input id="website-<?= $esc($key); ?>" type="text" name="content[<?= $esc($key); ?>]" maxlength="<?= (int)$field['max_length']; ?>" value="<?= $esc($content_values[$key]); ?>"><?php endif; ?>
<?php endif; ?></div><?php endforeach; ?></fieldset><?php endforeach; ?>
<div class="actions"><button type="submit">Save website content</button><span role="status" id="html-content-status"></span></div>
</form>
<script>(function(){var f=document.getElementById('html-theme-content'),s=document.getElementById('html-content-status');f.addEventListener('submit',function(e){e.preventDefault();var b=f.querySelector('button[type=submit]');b.disabled=true;s.textContent='Saving…';fetch(f.action,{method:'POST',body:new FormData(f),credentials:'same-origin',headers:{Accept:'application/json'}}).then(function(r){return r.json();}).then(function(r){if(r.csrf_hash)f.querySelector('[data-content-csrf]').value=r.csrf_hash;s.textContent=r.message||'Please try again.';}).catch(function(){s.textContent='Could not save. Your edits are still here; please try again.';}).finally(function(){b.disabled=false;});});})();</script>
<?php endif; ?></section>
