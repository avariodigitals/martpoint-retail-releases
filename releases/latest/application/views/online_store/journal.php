<?php
$this->load->view('online_store/content_editor_styles');
$mobile=$this->input->get('screen')==='mobile';
$suffix=$mobile?'?screen=mobile':'';
$postMeta=json_decode((string)($edit_post->meta_json ?? ''),true);
if(!is_array($postMeta))$postMeta=[];
$postKind=$edit_post->kind ?? 'insight';
$kindLabels=['insight'=>'Insight article','project'=>'Project case study','application'=>'Application page'];
?>
<?php if(!$mobile): ?><h2>Storefront stories</h2><?php endif; ?>
<nav class="sf-admin-links"><a href="<?= base_url('online_store/content_settings').$suffix; ?>">Page settings</a><a href="<?= base_url('online_store/journal').$suffix; ?>">New entry</a><?php if(!empty($journal_building)): ?><a href="<?= base_url('store/'.$settings->store_slug.'/insights'); ?>" target="_blank" rel="noopener">Preview insights ↗</a><a href="<?= base_url('store/'.$settings->store_slug.'/projects'); ?>" target="_blank" rel="noopener">Preview projects ↗</a><a href="<?= base_url('store/'.$settings->store_slug.'/applications'); ?>" target="_blank" rel="noopener">Preview applications ↗</a><?php else: ?><a href="<?= base_url('store/'.$settings->store_slug.'/blog'); ?>" target="_blank" rel="noopener">Preview journal ↗</a><?php endif; ?></nav>
<div class="sf-admin-grid">
<section class="sf-admin-card">
<h3><?= $edit_post?'Edit entry':'Write an entry'; ?></h3>
<form method="post" enctype="multipart/form-data" action="<?= base_url('online_store/save_journal'); ?>">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
<input type="hidden" name="id" value="<?= (int)($edit_post->id??0); ?>">
<input type="hidden" name="editor_mobile" value="<?= $mobile?'1':''; ?>">
<?php if(!empty($journal_building)): ?>
<fieldset style="margin:0 0 8px;border:0;padding:0">
<legend style="font-size:13px;font-weight:600;margin-bottom:8px">Where this appears</legend>
<label for="article-kind">Content type</label>
<select id="article-kind" name="kind" class="mp-form-control" style="width:100%;padding:11px;border:1px solid var(--mp-border,#dce3df);border-radius:5px;font:inherit;background:transparent;color:inherit">
<?php foreach($kindLabels as $value=>$label): ?><option value="<?= $value; ?>" <?= $postKind===$value?'selected':''; ?>><?= htmlspecialchars($label); ?></option><?php endforeach; ?>
</select>
<p>Insight articles appear under Insights, project case studies under Projects, and application pages under Applications. All three use this same editor and publishing workflow.</p>
</fieldset>
<?php endif; ?>
<?php foreach(['title'=>'Title','slug'=>'Address — letters, numbers and hyphens','excerpt'=>'Short introduction','body'=>'Entry text','image_url'=>'Cover image URL (or upload below)'] as $key=>$label): ?>
<label for="article-<?= $key; ?>"><?= $label; ?></label>
<?php if(in_array($key,['excerpt','body'])): ?><textarea id="article-<?= $key; ?>" name="<?= $key; ?>" rows="<?= $key==='body'?14:3; ?>" maxlength="<?= $key==='body'?100000:3000; ?>" <?= $key==='body'?'required':''; ?>><?= htmlspecialchars($edit_post->$key??''); ?></textarea>
<?php else: ?><input id="article-<?= $key; ?>" name="<?= $key; ?>" type="<?= $key==='image_url'?'url':'text'; ?>" value="<?= htmlspecialchars($edit_post->$key??'',ENT_QUOTES); ?>" <?= $key==='title'?'required maxlength="255"':''; ?>>
<?php endif; endforeach; ?>
<?php if(!empty($journal_building)): ?>
<fieldset style="margin:20px 0 0;border:1px solid var(--mp-border,#dce3df);border-radius:8px;padding:16px">
<legend style="font-size:13px;font-weight:600;padding:0 6px">Project details <span style="font-weight:400;color:var(--mp-muted,#64748b)">(optional)</span></legend>
<?php foreach(['location'=>'Location','sector'=>'Sector or building type','systems'=>'Systems used','year'=>'Year'] as $key=>$label): ?>
<label for="article-<?= $key; ?>"><?= $label; ?></label>
<input id="article-<?= $key; ?>" name="<?= $key; ?>" type="text" maxlength="180" value="<?= htmlspecialchars($postMeta[$key] ?? '',ENT_QUOTES); ?>">
<?php endforeach; ?>
<p>These appear beside project and application entries. Leave them blank for an ordinary insight article.</p>
</fieldset>
<?php endif; ?>
<p>Use plain text and blank lines for paragraphs. Entries are safely rendered without executable HTML.</p>
<label for="article-photo">Upload cover photo — JPG, PNG, WebP or AVIF, up to 3 MB</label>
<input id="article-photo" name="article_image" type="file" accept="image/jpeg,image/png,image/webp,image/avif,.avif">
<?php if(!empty($edit_post->image_url)): ?><img src="<?= htmlspecialchars($edit_post->image_url,ENT_QUOTES); ?>" alt="Current cover" style="display:block;width:180px;max-width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:6px;margin-top:14px"><?php endif; ?>
<fieldset style="margin:20px 0;border:0;padding:0"><legend>Publishing status</legend><?php foreach(['draft'=>'Draft','published'=>'Published','archived'=>'Archived'] as $value=>$label): ?><label><input type="radio" name="status" value="<?= $value; ?>" <?= ($edit_post->status??'draft')===$value?'checked':''; ?>><?= $label; ?></label><?php endforeach; ?></fieldset>
<label for="article-date">Publish from (optional, store local time)</label>
<input id="article-date" type="datetime-local" name="published_at" value="<?= !empty($edit_post->published_at)?(new DateTimeImmutable($edit_post->published_at,new DateTimeZone('UTC')))->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d\TH:i'):''; ?>">
<p>Future dates stay hidden until their publishing time. Archive an entry to remove it from the public store while keeping its content.</p>
<button type="submit">Save entry</button>
</form>
</section>
<aside class="sf-admin-card">
<h3>Your entries</h3>
<?php foreach($journal_posts as $post): ?>
<article class="sf-admin-article">
<strong><?= htmlspecialchars($post->title); ?></strong>
<p><?= htmlspecialchars($kindLabels[$post->kind ?? 'insight'] ?? 'Insight article'); ?> · <?= htmlspecialchars(ucfirst($post->status)); ?> · <?= htmlspecialchars($post->slug); ?></p>
<a href="<?= base_url('online_store/journal/'.(int)$post->id).$suffix; ?>">Edit entry</a>
<?php if($post->status==='published'): ?> · <a target="_blank" rel="noopener" href="<?= base_url('store/'.$settings->store_slug.'/blog/'.$post->slug); ?>">View ↗</a><?php endif; ?>
</article>
<?php endforeach; ?>
<?php if(!$journal_posts): ?><p>No entries yet. Write your first insight, project story or application page here.</p><?php endif; ?>
</aside>
</div>
