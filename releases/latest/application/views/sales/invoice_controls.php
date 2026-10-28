<?php
$record=$invoice_record ?? null;
$state=$record && ($record->sales_status ?? '')==='Draft'?'draft':(($record->invoice_status ?? '')==='sent'?'sent':'issued');
$canDraft=!$record || $state==='draft';
?>
<input type="hidden" id="standard_invoice_editor" value="1">
<input type="hidden" id="sales_status" name="sales_status" value="<?= $state==='draft'?'Draft':'Final'; ?>">
<input type="hidden" id="invoice_previous_paid" value="<?= htmlspecialchars((string)($record->paid_amount ?? 0),ENT_QUOTES,'UTF-8'); ?>">
<section class="invoice-workflow-controls" style="padding:18px;margin:14px 0;background:var(--mp-surface,#fff);color:var(--mp-text,#172033);border:1px solid #cbd5e1;border-radius:12px;">
<fieldset style="border:0;padding:0;margin:0 0 14px;"><legend style="font-size:16px;font-weight:700;margin-bottom:8px;">Invoice document</legend>
<label style="display:inline-flex;gap:7px;align-items:center;margin-right:18px;"><input type="radio" name="invoice_status" value="draft" <?= $state==='draft'?'checked':''; ?> <?= !$canDraft?'disabled':''; ?>> Draft — not yet issued</label>
<label style="display:inline-flex;gap:7px;align-items:center;"><input type="radio" name="invoice_status" value="issued" <?= $state!=='draft'?'checked':''; ?>> Issued — ready for payment</label>
<?php if($state==='sent'): ?><p>This invoice is already marked as sent. Saving keeps that history.</p><?php endif; ?>
</fieldset>
<fieldset style="border:0;padding:0;margin:0;"><legend style="font-size:16px;font-weight:700;margin-bottom:8px;">When will payment be recorded?</legend>
<label style="display:inline-flex;gap:7px;align-items:center;margin-right:18px;"><input type="radio" name="payment_timing" value="later" checked> Pay later — save without taking payment</label>
<label style="display:inline-flex;gap:7px;align-items:center;"><input type="radio" name="payment_timing" value="now"> Record a payment now</label>
</fieldset>
<p id="invoice-workflow-help" role="status" style="margin:12px 0 0;font-size:13px;line-height:1.6;">A named customer can receive an unpaid invoice. Record partial or full payments later from the invoice register. Drafts do not take payments or consume stock.</p>
</section>
<script src="<?= htmlspecialchars(base_url('theme/js/invoice_workflow.js?v='.app_version()),ENT_QUOTES,'UTF-8'); ?>"></script>
