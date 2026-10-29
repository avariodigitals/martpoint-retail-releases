<?php
// The design picker carries two kinds of value: d:<id> is one of this store's own
// designs, k:<key> is a simple built-in layout. Only the picker value is posted.
$pickerValue=(string)($selected_design??'');
if(preg_match('/^d:([1-9]\d{0,9})$/D',$pickerValue,$m))$pickerValue='d:'.$m[1];
elseif(preg_match('/^k:([a-z][a-z0-9_]{1,19})$/D',$pickerValue,$m) && isset($templates[$m[1]]))$pickerValue='k:'.$m[1];
else $pickerValue='k:'.(isset($templates[$selected_template??''])?$selected_template:'letter');
$isBuilt=$pickerValue[0]==='k';
$builtDesign=$isBuilt?$templates[substr($pickerValue,2)]:array();
$prefillSubject=$isBuilt?(string)$builtDesign['subject']:'';
$prefillMessage=$isBuilt?(string)$builtDesign['message']:'';
$designData=array();
foreach($designs as $ownRow){
    $own=Communication_designs::normalize($ownRow);
    $designData['d:'.$own['id']]=array('kind'=>'stored','name'=>$own['name'],'format'=>Communication_designs::label(Communication_designs::FORMATS,$own['format']),'subject'=>$own['subject']);
}
foreach($templates as $builtKey=>$templateRow){
    $designData['k:'.$builtKey]=array('kind'=>'built','name'=>$templateRow['name'],'format'=>'Plain letter','subject'=>$templateRow['subject'],'message'=>$templateRow['message']);
}
?>
<div class="comm-editor"><section class="comm-card"><h2>Campaign &amp; content</h2><?= form_open('communications/save_campaign',array('id'=>'comm-composer')); ?><input type="hidden" name="channel" value="email"><input type="hidden" name="screen" value="<?= $mobile?'mobile':'desktop'; ?>">
<label>Campaign name<input name="name" maxlength="150" required placeholder="e.g. November newsletter"></label><?php $audience_channel='email';$this->load->view('communications/_audience_select',get_defined_vars()); ?>
<div class="comm-split"><label>Email design<select name="design" id="comm-design"><?php if($designs): ?><optgroup label="Your designs"><?php foreach($designs as $ownRow):$own=Communication_designs::normalize($ownRow); ?><option value="d:<?= (int)$own['id']; ?>" <?= $pickerValue==='d:'.$own['id']?'selected':''; ?>><?= $h($own['name']); ?> · <?= $h(Communication_designs::label(Communication_designs::FORMATS,$own['format'])); ?></option><?php endforeach; ?></optgroup><?php endif; ?><optgroup label="Simple layouts"><?php foreach($templates as $builtKey=>$templateRow): ?><option value="k:<?= $h($builtKey); ?>" <?= $pickerValue==='k:'.$builtKey?'selected':''; ?>><?= $h($templateRow['name']); ?></option><?php endforeach; ?></optgroup></select></label><div style="align-self:end"><button type="button" class="btn secondary" id="comm-use-template" <?= $isBuilt?'':'hidden'; ?>>Use layout wording</button></div></div>
<p class="muted">Want a design of your own? <a href="<?= $h($workspace_url('templates')); ?>">Browse your email designs</a> or <a href="<?= $h($workspace_url('build')); ?>">create one</a>.</p>
<label>Email subject<input name="subject" maxlength="255" required value="<?= $h($prefillSubject); ?>" placeholder="<?= $h(($designData[$pickerValue]['subject']??'')?:'e.g. A note from our store'); ?>"></label><label>Your message<textarea name="message" rows="8" maxlength="20000" required placeholder="Tell your customers what you want them to know."><?= $h($prefillMessage); ?></textarea></label><p class="muted">Use {name} for the recipient’s name. An unsubscribe link is included in campaign emails.</p><div class="comm-split"><label>Button text (optional)<input name="button_label" maxlength="80" placeholder="Explore the offer"></label><label>Button link<input name="button_url" type="url" placeholder="https://your-store.com"></label></div><?php $this->load->view('communications/_send_options',get_defined_vars()); ?><div class="row" style="margin-top:20px"><button class="btn" data-send-submit <?= !$m['allowed']?'disabled':''; ?>>Save email draft</button><a class="btn secondary" href="<?= $h($workspace_url('templates')); ?>">Change design</a></div><?= form_close(); ?></section><aside class="comm-editor-preview"><div class="row" style="justify-content:space-between;margin-bottom:14px"><h3>Email preview</h3><span class="comm-badge">Live preview</span></div><iframe id="comm-preview" title="Email preview" sandbox class="preview" style="height:620px"></iframe><p class="muted">Preview personalized for a sample customer. Save your draft to test-send and review the credit estimate.</p></aside></div>
<script>window.COMM_TEMPLATE_DATA=<?= json_encode($templates,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;window.COMM_STORE_NAME=<?= json_encode($store_name,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;window.COMM_DESIGN_DATA=<?= json_encode($designData,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;window.COMM_PREVIEW_URL=<?= json_encode(base_url('communications/preview'),JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;</script>
