<?php
// MartPoint's own email design shelf. Stores add a copy of anything published here.
$sampleStore='Your store';
?>
<section class="comm-card">
    <h2>MartPoint email design library</h2>
    <p class="muted">Publish a design once and every store on every account can add it to their own email designs. A store gets a copy, so editing a library design never rewrites an email they already sent.</p>
    <div class="comm-toolbar">
        <a class="btn" href="<?= base_url('communications_admin?hub_design=new'.$suffix); ?>">+ Publish a new design</a>
        <?php $open('hub_seed'); ?><button class="btn secondary">Add the built-in designs</button><?= form_close(); ?>
        <span class="muted"><?= count($hub_designs); ?> design<?= count($hub_designs)===1?'':'s'; ?> published</span>
    </div>
<?php if(!$hub_ready): ?>
    <div class="notice">Run migration 4.0.9.220_client_release_prep_billing_caps_audience_templates_reports.sql, then refresh this page to publish email designs.</div>
<?php else: ?>
    <?php if($hub_editing!==null):
        $hubId=(int)($hub_editing['id']??0);
        $this->load->view('communications/_email_builder',array_merge(get_defined_vars(),array(
            'editing'=>$hub_editing,
            'builderTarget'=>'communications_admin/save',
            'builderExtra'=>array('action'=>'hub_template_save'),
            'builderPreview'=>base_url('communications_admin/preview'),
            'builderBack'=>base_url('communications_admin').$suffix,
            'builderUse'=>'',
            'builderSaveLabel'=>'Publish to the library',
            'builderScreen'=>$mobile?'mobile':'desktop',
            'builderDelete'=>$hubId?array('url'=>'communications_admin/save','extra'=>array('action'=>'hub_template_delete','template_id'=>$hubId)):null)));
    endif; ?>
    <?php if(!$hub_designs): ?>
        <p class="empty">No designs are published yet. Use “Add the built-in designs” to load the designs shipped with this build.</p>
    <?php else: ?>
    <div class="comm-template-grid">
    <?php foreach($hub_designs as $hubRow):$ht=Communication_designs::normalize($hubRow); ?>
        <article class="comm-template-card">
            <div class="comm-template-thumb"><iframe srcdoc="<?= $h(Communication_designs::preview($hubRow,$sampleStore)); ?>" title="<?= $h($ht['name']); ?> library design preview" sandbox tabindex="-1" loading="lazy"></iframe></div>
            <div class="comm-template-caption">
                <div class="row"><h3><?= $h($ht['name']); ?></h3><span class="comm-badge <?= empty($hubRow['enabled'])?'off':'ok'; ?>"><?= empty($hubRow['enabled'])?'Hidden':'Visible' ?></span></div>
                <p class="muted"><?= $h(Communication_designs::label(Communication_designs::FORMATS,$ht['format'])); ?> · <?= $h(Communication_designs::label(Communication_designs::CATEGORIES,$ht['category'])); ?> · <?= $h(Communication_designs::label(Communication_designs::INDUSTRIES,$ht['industry'])); ?> · <?= $h(Communication_designs::label(Communication_designs::OCCASIONS,$ht['occasion'])); ?></p>
                <div class="row comm-design-actions"><a class="btn secondary" href="<?= base_url('communications_admin?hub_design='.(int)$ht['id']).$suffix; ?>">Edit design</a></div>
                <?php $open('hub_template_enable',array('template_id'=>(int)$ht['id'])); ?>
                <label class="row" style="margin:0"><input class="switch" type="checkbox" name="enabled" value="1" <?= empty($hubRow['enabled'])?'':'checked'; ?> onchange="this.form.submit()" aria-label="Show <?= $h($ht['name']); ?> to stores"> <?= empty($hubRow['enabled'])?'Hidden from stores':'Visible to stores'; ?></label><?= form_close(); ?>
                <div class="comm-inline-form"><?php $open('hub_template_delete',array('template_id'=>(int)$ht['id'])); ?><button type="button" class="btn secondary comm-design-delete" data-design-remove="<?= $h($ht['name']); ?>">Remove</button><?= form_close(); ?></div>
            </div>
        </article>
    <?php endforeach; ?></div>
    <?php endif; ?>
<?php endif; ?>
</section>
