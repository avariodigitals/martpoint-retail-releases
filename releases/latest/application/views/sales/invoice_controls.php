<?php
$record=$invoice_record ?? null;
$state=$record && ($record->sales_status ?? '')==='Draft'?'draft':(($record->invoice_status ?? '')==='sent'?'sent':'issued');
if(!empty($record) && $state==='draft' && ($_GET['record_payment'] ?? '')==='1')$state='issued';
?>
<input type="hidden" id="standard_invoice_editor" value="1">
<input type="hidden" name="invoice_payment_mode" value="amount">
<input type="hidden" id="invoice_payment_timing" name="payment_timing" value="later">
<input type="hidden" id="sales_status" name="sales_status" value="<?= $state==='draft'?'Draft':'Final'; ?>">
<input type="hidden" id="invoice_previous_paid" value="<?= htmlspecialchars((string)($record->paid_amount ?? 0),ENT_QUOTES,'UTF-8'); ?>">
<section class="invoice-workflow-controls" aria-label="Invoice status">
  <div class="iw-status-field">
    <label for="invoice_status">Invoice status</label>
    <select id="invoice_status" name="invoice_status" style="display:none" aria-describedby="invoice-workflow-help">
      <option value="issued" <?= $state!=='draft'?'selected':''; ?>>Issued</option>
      <option value="draft" <?= $state==='draft'?'selected':''; ?>>Draft</option>
    </select>
  </div>
  <p id="invoice-workflow-help" role="status">Issued invoices can be saved unpaid. Enter only money actually received in Payment, or record a payment from the saved invoice later.</p>
  <?php if($state==='sent'): ?><p class="iw-sent-note">Already sent — saving preserves the sent history.</p><?php endif; ?>
</section>
<script src="<?= htmlspecialchars(base_url('theme/js/invoice_workflow.js?v='.app_version()),ENT_QUOTES,'UTF-8'); ?>"></script>
