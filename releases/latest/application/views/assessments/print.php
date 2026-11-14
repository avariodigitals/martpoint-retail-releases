<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html><html><head><meta charset="utf-8"><style>
@page{margin:15mm 14mm}body{font-family:DejaVu Sans,Arial,sans-serif;color:#172033;font-size:10px;line-height:1.45;max-width:100%;overflow-wrap:break-word}h1{font-size:18px;margin:0 0 4px;color:#104d40}h2{font-size:12px;text-transform:uppercase;letter-spacing:.5px;background:#eaf3ee;padding:7px 9px;margin:16px 0 8px;color:#104d40;border-left:3px solid #176753;page-break-after:avoid}h3{font-size:10px;margin:0 0 3px;color:#475569}p{margin:0 0 5px;white-space:pre-wrap;overflow-wrap:break-word}.meta{width:100%;border:1px solid #cbd5e1;border-collapse:collapse;margin:12px 0;table-layout:fixed}.meta td{width:50%;padding:7px 8px;vertical-align:top}.meta b{color:#64748b}.assessment-fields{width:100%;border-collapse:collapse;table-layout:fixed;page-break-inside:auto}.assessment-fields td{width:50%;vertical-align:top;border-bottom:1px solid #e2e8f0;padding:7px 8px 9px 0;overflow-wrap:break-word;page-break-inside:avoid}.assessment-fields td+td{padding-left:12px;padding-right:0}.assessment-fields .field-heading td{width:100%;border-bottom:0;padding:9px 0 4px}.field-value{min-height:34px;white-space:pre-wrap;overflow-wrap:break-word}.signature{max-width:180px;max-height:55px}.brand{width:100%;border-bottom:2px solid #176753;padding:0 0 8px;margin-bottom:10px;white-space:nowrap}.brand img{display:inline-block;vertical-align:middle;max-width:180px;max-height:58px;width:auto;height:auto;object-fit:contain}.brand-name{display:inline-block;vertical-align:middle;margin-left:12px;font-size:14px;font-weight:bold;color:#104d40;white-space:normal}.footer{clear:both;margin-top:20px;color:#64748b;font-size:9px;border-top:1px solid #cbd5e1;padding-top:6px}
</style></head><body>
<?php
$logoData = '';
$logoPath = function_exists('mp_get_store_theme_setting') ? trim((string)mp_get_store_theme_setting((int)$assessment->store_id, 'store_logo')) : '';
if($logoPath === '' && function_exists('get_site_logo')) $logoPath = trim((string)get_site_logo());
if($logoPath !== '' && defined('FCPATH')){
  $logoFile = FCPATH . ltrim($logoPath, '/');
  if(is_file($logoFile)){
    $logoMime = function_exists('mime_content_type') ? mime_content_type($logoFile) : 'image/png';
    $logoData = 'data:'.$logoMime.';base64,'.base64_encode(file_get_contents($logoFile));
  }
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
<div>Assessment Copy</div>
<table class="meta"><tbody><tr><td><b>Patient</b><br><?= htmlspecialchars($patient->customer_name ?? ''); ?> (<?= htmlspecialchars($patient->patient_code ?? ''); ?>)</td><td><b>Visit</b><br><?= htmlspecialchars($enc->encounter_code ?? ''); ?></td></tr><tr><td><b>Assessor</b><br><?= htmlspecialchars($assessment->assessor_name ?? ''); ?></td><td><b>Date</b><br><?= htmlspecialchars($assessment->created_at ?? ''); ?></td></tr></tbody></table>
<?php foreach($sections as $section): ?>
  <h2><?= htmlspecialchars($section['title'] ?? $section['key']); ?></h2>
  <table class="assessment-fields"><tbody>
  <?php $pair = array(); foreach(($section['fields'] ?? array()) as $field):
    if(($field['type'] ?? '') === 'heading'):
      if(!empty($pair)): ?><tr><?php foreach($pair as $pairField): $pairKey=$section['key'].'_'.$pairField['key']; $pairValue=$answers[$pairKey] ?? ''; if(is_array($pairValue)) $pairValue=implode(', ', $pairValue); ?><td><h3><?= htmlspecialchars($pairField['label'] ?? $pairField['key']); ?></h3><div class="field-value"><?php if($pairField['key'] === 'signature' && $signatureData): ?><img class="signature" src="<?= $signatureData; ?>" alt="Clinician signature"><?php else: ?><p><?= htmlspecialchars((string)$pairValue); ?></p><?php endif; ?></div></td><?php endforeach; ?><?php if(count($pair) === 1): ?><td></td><?php endif; ?></tr><?php $pair=array(); endif; ?>
      <tr class="field-heading"><td colspan="2"><h3><?= htmlspecialchars($field['label'] ?? $field['key']); ?></h3></td></tr>
      <?php continue;
    endif;
    $pair[] = $field;
    if(count($pair) < 2) continue; ?>
    <tr><?php foreach($pair as $pairField): $pairKey=$section['key'].'_'.$pairField['key']; $pairValue=$answers[$pairKey] ?? ''; if(is_array($pairValue)) $pairValue=implode(', ', $pairValue); ?><td><h3><?= htmlspecialchars($pairField['label'] ?? $pairField['key']); ?></h3><div class="field-value"><?php if($pairField['key'] === 'signature' && $signatureData): ?><img class="signature" src="<?= $signatureData; ?>" alt="Clinician signature"><?php else: ?><p><?= htmlspecialchars((string)$pairValue); ?></p><?php endif; ?></div></td><?php endforeach; ?></tr>
    <?php $pair=array();
  endforeach;
  if(!empty($pair)): ?><tr><?php foreach($pair as $pairField): $pairKey=$section['key'].'_'.$pairField['key']; $pairValue=$answers[$pairKey] ?? ''; if(is_array($pairValue)) $pairValue=implode(', ', $pairValue); ?><td><h3><?= htmlspecialchars($pairField['label'] ?? $pairField['key']); ?></h3><div class="field-value"><?php if($pairField['key'] === 'signature' && $signatureData): ?><img class="signature" src="<?= $signatureData; ?>" alt="Clinician signature"><?php else: ?><p><?= htmlspecialchars((string)$pairValue); ?></p><?php endif; ?></div></td><?php endforeach; ?><?php if(count($pair) === 1): ?><td></td><?php endif; ?></tr><?php endif; ?>
  </tbody></table>
<?php endforeach; ?>
<div class="footer">Generated from MartPoint · This copy reflects the saved assessment record. Finalised records are locked; corrections are recorded as amendments.</div>
</body></html>
