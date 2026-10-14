<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html><html><head><meta charset="utf-8"><style>
@page{margin:18mm 15mm}body{font-family:DejaVu Sans,Arial,sans-serif;color:#172033;font-size:10px;line-height:1.45}h1{font-size:18px;margin:0 0 4px;color:#104d40}h2{font-size:12px;text-transform:uppercase;letter-spacing:.5px;background:#eaf3ee;padding:7px 9px;margin:16px 0 8px;color:#104d40;border-left:3px solid #176753;clear:both}h3{font-size:10px;margin:8px 0 2px;color:#475569}p{margin:0 0 5px;white-space:pre-wrap}.meta{border:1px solid #cbd5e1;padding:8px;margin:12px 0;display:grid;grid-template-columns:1fr 1fr;gap:4px}.meta b{color:#64748b}.field{float:left;width:48%;min-height:62px;border-bottom:1px solid #e2e8f0;padding:5px 1%;page-break-inside:avoid}.section-end{clear:both}.signature{max-width:180px;max-height:55px}.brand{display:flex;align-items:center;gap:12px;border-bottom:2px solid #176753;padding-bottom:8px;margin-bottom:10px}.brand img{max-width:78px;max-height:58px;object-fit:contain}.brand-name{font-size:14px;font-weight:bold;color:#104d40}.footer{clear:both;margin-top:20px;color:#64748b;font-size:9px;border-top:1px solid #cbd5e1;padding-top:6px}
</style></head><body>
<?php
$logoData = '';
if(function_exists('mp_get_store_theme_setting') && function_exists('mp_store_logo_round_base64')){
  $logoPath = mp_get_store_theme_setting((int)$assessment->store_id, 'store_logo');
  if($logoPath) $logoData = mp_store_logo_round_base64($logoPath, 96);
}
$signatureData = '';
$signaturePath = $answers['administration_signature'] ?? '';
if($signaturePath && defined('FCPATH') && is_file(FCPATH.$signaturePath)){
  $mime = function_exists('mime_content_type') ? mime_content_type(FCPATH.$signaturePath) : 'image/png';
  $signatureData = 'data:'.$mime.';base64,'.base64_encode(file_get_contents(FCPATH.$signaturePath));
}
?>
<?php if($logoData): ?><div class="brand"><img src="<?= $logoData; ?>" alt="Store logo"><span class="brand-name"><?= htmlspecialchars(get_store_name($assessment->store_id) ?: ''); ?></span></div><?php endif; ?>
<h1><?= htmlspecialchars(($template->template_key ?? '') === 'physio_initial' ? 'Physiotherapy Initial Assessment' : $template->name); ?></h1>
<div>Assessment copy · <?= htmlspecialchars($assessment->status); ?> · Version <?= (int)$assessment->template_version; ?></div>
<div class="meta"><div><b>Patient</b><br><?= htmlspecialchars($patient->customer_name ?? ''); ?> (<?= htmlspecialchars($patient->patient_code ?? ''); ?>)</div><div><b>Visit</b><br><?= htmlspecialchars($enc->encounter_code ?? ''); ?></div><div><b>Assessor</b><br><?= htmlspecialchars($assessment->assessor_name ?? ''); ?></div><div><b>Date</b><br><?= htmlspecialchars($assessment->created_at ?? ''); ?></div></div>
<?php foreach($sections as $section): ?><h2><?= htmlspecialchars($section['title'] ?? $section['key']); ?></h2><?php foreach(($section['fields'] ?? array()) as $field): if(($field['type'] ?? '') === 'heading'): ?><div class="section-end"></div><h3><?= htmlspecialchars($field['label'] ?? $field['key']); ?></h3><?php continue; endif; $key=$section['key'].'_'.$field['key']; $value=$answers[$key] ?? ''; if(is_array($value)) $value=implode(', ', $value); ?><div class="field"><h3><?= htmlspecialchars($field['label'] ?? $field['key']); ?></h3><?php if($field['key'] === 'signature' && $signatureData): ?><img class="signature" src="<?= $signatureData; ?>" alt="Clinician signature"><?php else: ?><p><?= htmlspecialchars((string)$value); ?></p><?php endif; ?></div><?php endforeach; ?><div class="section-end"></div><?php endforeach; ?>
<div class="footer">Generated from MartPoint · This copy reflects the saved assessment record. Finalised records are locked; corrections are recorded as amendments.</div>
</body></html>
