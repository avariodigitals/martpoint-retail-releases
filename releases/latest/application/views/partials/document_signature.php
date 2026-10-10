<?php
$this->load->helper('document_signature');
$signature_tag = mp_document_signature_tag(isset($signature_store_id) ? $signature_store_id : null);
?>
<?php if ($signature_tag !== ''): ?>
<table style="width:100%;border-collapse:collapse;margin-top:16px;page-break-inside:avoid;">
  <tr><td style="width:100%;text-align:right;border:0;padding:0;vertical-align:bottom;">
    <?= $signature_tag; ?><br>
    <span style="display:inline-block;min-width:180px;border-top:1px solid #94a3b8;padding-top:4px;font-size:9pt;color:#374151;">Authorized signature</span>
  </td></tr>
</table>
<?php endif; ?>
