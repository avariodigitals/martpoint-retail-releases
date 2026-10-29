<?php
// Without the stored-design tables the old four layouts still work, so nobody loses the page.
if(empty($designs_saved)): ?>
<div class="notice">Your email designs library needs an application update. Contact your administrator. Until then the built-in layouts below are available.</div>
<div class="comm-template-grid">
<?php foreach($templates as $templateKey=>$template):$preview=Communication_templates::render($templateKey,$store_name,'Customer',$template['message'],$template['subject'],'#6554c0',$templateKey==='offer'?'Explore the offer':'','https://example.com/'); ?>
<article class="comm-template-card"><div class="comm-template-thumb"><iframe srcdoc="<?= $h($preview); ?>" title="<?= $h($template['name']); ?> email template preview" sandbox tabindex="-1" loading="lazy"></iframe></div><div class="comm-template-caption"><div class="row"><h3><?= $h($template['name']); ?></h3><a class="btn secondary" href="<?= $h($workspace_url('compose',array('template'=>$templateKey))); ?>">Use template</a></div><p class="muted"><?= $h(array('letter'=>'A simple, personal note for announcements and direct updates.','welcome'=>'A warm introduction for new contacts and customers.','offer'=>'Put your latest promotion and call-to-action in the spotlight.','newsletter'=>'Share news, useful tips and upcoming events.')[$templateKey]??'Make this design your own.'); ?></p></div></article>
<?php endforeach; ?></div>
<?php return; endif; ?>

<div class="comm-designs" data-design-family="<?= $h($design_family); ?>">
<div class="comm-toolbar" aria-label="Filter email designs">
    <input type="search" id="comm-design-search" placeholder="Search your designs" aria-label="Search designs" style="max-width:230px">
    <a class="btn" href="<?= $h($workspace_url('build')); ?>">+ New design</a>
</div>
<div class="comm-toolbar" aria-label="Filter designs by purpose">
    <button type="button" class="active" data-design-filter="all">All designs</button>
    <button type="button" data-design-filter="family">For your business</button>
<?php foreach(Communication_designs::CATEGORIES as $categoryKey=>$categoryLabel): ?>
    <button type="button" data-design-filter="<?= $h($categoryKey); ?>"><?= $h($categoryLabel); ?></button>
<?php endforeach; ?></div>
<div class="comm-toolbar" aria-label="Filter designs by format">
    <button type="button" class="active" data-design-format="all">Every format</button>
<?php foreach(Communication_designs::FORMATS as $formatKey=>$formatLabel): ?>
    <button type="button" data-design-format="<?= $h($formatKey); ?>"><?= $h($formatLabel); ?></button>
<?php endforeach; ?></div>

<h2>Your designs <span class="muted">(<?= count($designs); ?>)</span></h2>
<?php if(!$designs): ?>
<div class="comm-card comm-empty">You have no designs yet. Add one from the MartPoint library below, or <a href="<?= $h($workspace_url('build')); ?>">create a blank design</a>.</div>
<?php else: ?>
<div class="comm-template-grid">
<?php foreach($designs as $design):$t=Communication_designs::normalize($design); ?>
<article class="comm-template-card" data-design-card data-category="<?= $h($t['category']); ?>" data-format="<?= $h($t['format']); ?>" data-industry="<?= $h($t['industry']); ?>" data-occasion="<?= $h($t['occasion']); ?>" data-name="<?= $h($t['name']); ?>">
    <div class="comm-template-thumb"><iframe srcdoc="<?= $h(Communication_designs::preview($design,$store_name)); ?>" title="<?= $h($t['name']); ?> email design preview" sandbox tabindex="-1" loading="lazy"></iframe></div>
    <div class="comm-template-caption">
        <div class="row"><h3><?= $h($t['name']); ?></h3><span class="comm-badge"><?= $h(Communication_designs::label(Communication_designs::FORMATS,$t['format'])); ?></span></div>
        <p class="muted"><?= $h(Communication_designs::label(Communication_designs::CATEGORIES,$t['category'])); ?> · <?= $h(Communication_designs::label(Communication_designs::INDUSTRIES,$t['industry'])); ?> · <?= $h(Communication_designs::label(Communication_designs::OCCASIONS,$t['occasion'])); ?><?php if($design_family!=='any' && $t['industry']===$design_family): ?> · <b>Matched to your business</b><?php endif; ?></p>
        <div class="row comm-design-actions">
            <a class="btn" href="<?= $h($workspace_url('compose',array('use_design'=>$t['id']))); ?>">Use in email</a>
            <a class="btn secondary" href="<?= $h($workspace_url('build',array('design'=>$t['id']))); ?>">Edit design</a>
        </div>
        <?= form_open('communications/design_remove'); ?><input type="hidden" name="template_id" value="<?= (int)$t['id']; ?>">
        <button type="submit" class="btn secondary comm-design-delete" data-design-remove="<?= $h($t['name']); ?>">Remove</button><?= form_close(); ?>
    </div>
</article>
<?php endforeach; ?></div>
<?php endif; ?>

<h2 style="margin-top:28px">MartPoint library</h2>
<p class="muted">Add a copy of any MartPoint design to your own library, then make it yours. Your copy is never changed by us, so a campaign you already sent always looks the way you sent it.</p>
<?php if($hub_message): ?><div class="notice"><?= $h($hub_message); ?></div><?php endif; ?>
<?php if(!$hub_designs): ?>
<div class="comm-card comm-empty">The MartPoint library is empty right now. Contact your administrator to have designs published.</div>
<?php else: ?>
<div class="comm-template-grid">
<?php foreach($hub_designs as $hubDesign):$t=Communication_designs::normalize($hubDesign); ?>
<article class="comm-template-card" data-design-card data-category="<?= $h($t['category']); ?>" data-format="<?= $h($t['format']); ?>" data-industry="<?= $h($t['industry']); ?>" data-occasion="<?= $h($t['occasion']); ?>" data-name="<?= $h($t['name']); ?>">
    <div class="comm-template-thumb"><iframe srcdoc="<?= $h(Communication_designs::preview($hubDesign,$store_name)); ?>" title="<?= $h($t['name']); ?> MartPoint design preview" sandbox tabindex="-1" loading="lazy"></iframe></div>
    <div class="comm-template-caption">
        <div class="row"><h3><?= $h($t['name']); ?></h3><span class="comm-badge"><?= $h(Communication_designs::label(Communication_designs::FORMATS,$t['format'])); ?></span></div>
        <p class="muted"><?= $h(Communication_designs::label(Communication_designs::CATEGORIES,$t['category'])); ?> · <?= $h(Communication_designs::label(Communication_designs::INDUSTRIES,$t['industry'])); ?> · <?= $h(Communication_designs::label(Communication_designs::OCCASIONS,$t['occasion'])); ?></p>
        <?= form_open('communications/design_adopt'); ?><input type="hidden" name="hub_template_id" value="<?= (int)$t['id']; ?>">
        <button type="submit" class="btn">Add to my designs</button><?= form_close(); ?>
    </div>
</article>
<?php endforeach; ?></div>
<?php endif; ?>
</div>
