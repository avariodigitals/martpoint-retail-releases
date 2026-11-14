<?php
// Shared by stores and the MartPoint design library: the same editor, different targets.
$t=(isset($editing)&&$editing)?Communication_designs::normalize($editing):array();
$blockRows=$t?$t['blocks']:array(array('type'=>'body','text'=>''));
$builderTarget=isset($builderTarget)?$builderTarget:'communications/design_save';
$builderExtra=isset($builderExtra)?$builderExtra:array();
$builderPreview=isset($builderPreview)?$builderPreview:base_url('communications/preview');
$builderBack=isset($builderBack)?$builderBack:(isset($workspace_url)?$workspace_url('templates'):base_url('communications_admin'));
$builderUse=isset($builderUse)?$builderUse:(!empty($t['id'])&&isset($workspace_url)?$workspace_url('compose',array('use_design'=>(int)$t['id'])):'');
$builderDelete=isset($builderDelete)?$builderDelete:(!empty($t['id'])?array('url'=>'communications/design_remove','extra'=>array('template_id'=>(int)$t['id'])):null);
$builderScreen=isset($builderScreen)?$builderScreen:($mobile?'mobile':'desktop');
$option=function($map,$selected,$field)use($h){
    echo '<select name="'.$h($field).'">';
    foreach($map as $key=>$label)echo '<option value="'.$h($key).'" '.((string)$selected===(string)$key?'selected':'').'>'.$h($label).'</option>';
    echo '</select>';
};
?>
<div class="comm-editor">
<section class="comm-card">
    <h2><?= $t&&$t['name']?'Edit “'.$h($t['name']).'”':'Create an email design'; ?></h2>
    <?= form_open($builderTarget,array('id'=>'comm-builder')); ?>
    <input type="hidden" name="template_id" value="<?= (int)($t['id']??0); ?>">
    <input type="hidden" name="screen" value="<?= $h($builderScreen); ?>">
<?php foreach($builderExtra as $builderField=>$builderValue): ?><input type="hidden" name="<?= $h($builderField); ?>" value="<?= $h($builderValue); ?>"><?php endforeach; ?>
    <input type="hidden" name="blocks" value="">
    <label>Design name<input name="name" maxlength="120" required value="<?= $h($t['name']??''); ?>" placeholder="e.g. Our monthly newsletter"></label>
    <div class="comm-split">
        <label>Format<?php $option(Communication_designs::FORMATS,$t['format']??'html','format'); ?></label>
        <label>Purpose<?php $option(Communication_designs::CATEGORIES,$t['category']??'announcement','category'); ?></label>
    </div>
    <div class="comm-split">
        <label>Best for<?php $option(Communication_designs::INDUSTRIES,$t['industry']??'any','industry'); ?></label>
        <label>Occasion<?php $option(Communication_designs::OCCASIONS,$t['occasion']??'any','occasion'); ?></label>
    </div>
    <p class="muted">Purpose, best-for and occasion only help you find the design again in your gallery. The email itself is built from the blocks below.</p>

    <h3 style="margin-top:22px">Words inside the design</h3>
    <label>Example subject line<input name="subject" maxlength="255" value="<?= $h($t['subject']??''); ?>" placeholder="e.g. Fresh stock is in, {name}"></label>
    <label>Inbox preview text<small class="muted"> the short second line in the inbox</small><input name="preheader" maxlength="180" value="<?= $h($t['preheader']??''); ?>" placeholder="What the customer sees before opening"></label>
    <p class="muted">Use <code>{name}</code> and <code>{store}</code> anywhere in these fields — each email fills them in for the customer it goes to.</p>

    <h3 style="margin-top:22px">Layout blocks</h3>
    <p class="muted">The <b>Your message</b> block is where the campaign text you type when composing will appear. Keep it once, anywhere in the layout.</p>
    <div id="comm-block-list" data-block-limit="<?= Communication_designs::MAX_BLOCKS; ?>">
<?php foreach($blockRows as $row): ?>
        <div class="comm-block-row" data-block-row>
            <div class="comm-block-head">
                <select name="block_type" class="comm-block-type" aria-label="Block type"><?php foreach(Communication_designs::BLOCKS as $key=>$meta): ?><option value="<?= $h($key); ?>" data-field="<?= $h($meta['field']); ?>" <?= $row['type']===$key?'selected':''; ?>><?= $h($meta['label']); ?></option><?php endforeach; ?></select>
                <div class="row">
                    <button type="button" class="comm-icon-btn" data-block-up title="Move up" aria-label="Move block up">↑</button>
                    <button type="button" class="comm-icon-btn" data-block-down title="Move down" aria-label="Move block down">↓</button>
                    <button type="button" class="comm-icon-btn" data-block-remove title="Remove block" aria-label="Remove block">✕</button>
                </div>
            </div>
            <label class="comm-block-field comm-block-text-field">Text<textarea name="block_text" class="comm-block-text" rows="3" maxlength="2000" placeholder="Write the wording for this block. {name} and {store} work here too."><?= $h($row['text']??''); ?></textarea></label>
            <div class="comm-split comm-block-field comm-block-cta">
                <label>Button text<input name="block_label" class="comm-block-label" maxlength="80" value="<?= $h($row['label']??''); ?>" placeholder="Book an appointment"></label>
                <label>Button link<input name="block_url" class="comm-block-url" type="url" value="<?= $h($row['url']??''); ?>" placeholder="https://your-store.com/booking"></label>
            </div>
        </div>
<?php endforeach; ?>
    </div>
    <div class="row" style="margin-top:12px"><button type="button" class="btn secondary" id="comm-block-add" <?= count($blockRows)>=Communication_designs::MAX_BLOCKS?'disabled':''; ?>>+ Add block</button><span class="muted" id="comm-block-count"><?= count($blockRows); ?> of <?= Communication_designs::MAX_BLOCKS; ?> blocks</span></div>

    <h3 style="margin-top:22px">Brand and footer</h3>
    <div class="comm-split">
        <label>Brand colour<input type="color" name="accent" class="comm-color" value="<?= $h($t['accent']??Communication_designs::DEFAULT_ACCENT); ?>"></label>
        <label>Your name in the header<?php $option(Communication_designs::BRANDS,$t['brand']??'name','brand_display'); ?></label>
    </div>
    <input type="hidden" name="show_unsubscribe" value="0">
    <label class="choice"><input type="checkbox" name="show_unsubscribe" value="1" <?= empty($t['id'])||!empty($t['unsubscribe'])?'checked':''; ?>><span>Show the unsubscribe line in the footer</span></label>
    <p class="muted">Keeping this on is the safest choice for marketing emails, and matches what your customers expect from a bulk email.</p>
    <div class="comm-split">
        <label>Default button text<input name="cta_label" maxlength="80" value="<?= $h($t['cta_label']??''); ?>" placeholder="Shop now"></label>
        <label>Default button link<input name="cta_url" type="url" value="<?= $h($t['cta_url']??''); ?>" placeholder="https://your-store.com"></label>
    </div>
    <p class="muted">A campaign can override these two fields, so the design keeps a sensible button when the campaign sets none.</p>

    <div class="row" style="margin-top:22px">
        <button class="btn" id="comm-builder-save"><?= $h(isset($builderSaveLabel)?$builderSaveLabel:'Save design'); ?></button>
        <?php if($builderUse): ?><a class="btn secondary" href="<?= $h($builderUse); ?>">Use in an email</a><?php endif; ?>
        <a class="btn secondary" href="<?= $h($builderBack); ?>">Back to designs</a>
    </div>
    <?= form_close(); ?>
<?php if($builderDelete): ?>
    <?= form_open($builderDelete['url'],array('class'=>'comm-inline-form')); ?><input type="hidden" name="screen" value="<?= $h($builderScreen); ?>"><?php foreach($builderDelete['extra'] as $builderField=>$builderValue): ?><input type="hidden" name="<?= $h($builderField); ?>" value="<?= $h($builderValue); ?>"><?php endforeach; ?>
    <button type="button" class="btn secondary comm-design-delete" data-design-remove="<?= $h($t['name']); ?>">Remove this design</button><?= form_close(); ?>
<?php endif; ?>
</section>

<aside class="comm-editor-preview">
    <div class="row" style="justify-content:space-between;margin-bottom:14px"><h3>Live preview</h3><span class="comm-badge">Exactly what sends</span></div>
    <iframe id="comm-builder-preview" title="Email design preview" sandbox class="preview" style="height:640px"></iframe>
    <p class="muted">Previewed for a sample customer, Ada. Your own campaign text fills the <b>Your message</b> block when you compose.</p>
</aside>
</div>
<script type="text/template" id="comm-block-row-template">
    <div class="comm-block-row" data-block-row>
        <div class="comm-block-head">
            <select name="block_type" class="comm-block-type" aria-label="Block type"><?php foreach(Communication_designs::BLOCKS as $key=>$meta): ?><option value="<?= $h($key); ?>" data-field="<?= $h($meta['field']); ?>"><?= $h($meta['label']); ?></option><?php endforeach; ?></select>
            <div class="row">
                <button type="button" class="comm-icon-btn" data-block-up title="Move up" aria-label="Move block up">↑</button>
                <button type="button" class="comm-icon-btn" data-block-down title="Move down" aria-label="Move block down">↓</button>
                <button type="button" class="comm-icon-btn" data-block-remove title="Remove block" aria-label="Remove block">✕</button>
            </div>
        </div>
        <label class="comm-block-field comm-block-text-field">Text<textarea name="block_text" class="comm-block-text" rows="3" maxlength="2000" placeholder="Write the wording for this block. {name} and {store} work here too."></textarea></label>
        <div class="comm-split comm-block-field comm-block-cta">
            <label>Button text<input name="block_label" class="comm-block-label" maxlength="80" placeholder="Book an appointment"></label>
            <label>Button link<input name="block_url" class="comm-block-url" type="url" placeholder="https://your-store.com/booking"></label>
        </div>
    </div>
</script>
<script>window.COMM_PREVIEW_URL=<?= json_encode($builderPreview,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;</script>
