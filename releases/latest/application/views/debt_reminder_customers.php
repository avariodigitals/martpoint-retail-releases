<?php /* Invoice-level debt reminder controls. */ ?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.dr-table td{vertical-align:middle!important}.dr-controls{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.dr-controls select,.dr-controls input{min-height:34px;padding:5px 8px;border:1px solid #dbe2ea;border-radius:6px}.dr-controls .dr-max{width:78px}.dr-meta{color:#64748b;font-size:12px}.dr-invoice{font-weight:700;color:#1d4ed8}
</style>
<div class="mp-page-head"><h1 class="mp-page-title"><?= htmlspecialchars($page_title); ?></h1></div>
<div class="mp-card">
  <div class="mp-card-header">
    <div class="mp-card-title"><i class="fa fa-file-text-o text-blue"></i> Unpaid invoices</div>
    <small class="text-muted">Store default: <?= htmlspecialchars(ucfirst($store_defaults->frequency)); ?> · <?= $store_defaults->enabled ? '<span style="color:#10B981">Enabled</span>' : '<span style="color:#EF4444">Disabled</span>'; ?></small>
  </div>
  <div class="mp-card-body">
    <p class="dr-meta">Each row is an individual invoice. Its reminder frequency, limit, channels and send history are tracked separately.</p>
    <?php if(!empty($invoices)): ?>
    <div class="table-responsive">
      <table class="mp-table dr-table" id="tbl_invoices">
        <thead><tr><th>Invoice</th><th>Customer</th><th>Due date</th><th>Balance due</th><th>Sent</th><th>Last reminder</th><th>Reminder settings</th><th></th></tr></thead>
        <tbody>
        <?php foreach($invoices as $inv): ?>
          <tr data-invoice-id="<?= (int)$inv->invoice_id; ?>">
            <td><span class="dr-invoice"><?= htmlspecialchars($inv->sales_code); ?></span></td>
            <td><?= htmlspecialchars($inv->customer_name); ?><br><span class="dr-meta"><?= htmlspecialchars($inv->email ?: $inv->mobile); ?></span></td>
            <td><?= !empty($inv->due_date) ? show_date($inv->due_date) : '—'; ?></td>
            <td><?= store_number_format($inv->amount_due); ?></td>
            <td><?= (int)$inv->reminder_count; ?></td>
            <td><?= !empty($inv->last_reminder_sent) ? date('M d, Y H:i',strtotime($inv->last_reminder_sent)) : '<span class="text-muted">Never</span>'; ?></td>
            <td>
              <div class="dr-controls">
                <label title="Enable reminders for this invoice"><input class="dr-enabled" type="checkbox" <?= $inv->enabled ? 'checked' : ''; ?>> On</label>
                <select class="dr-frequency" aria-label="Reminder frequency">
                  <?php foreach(['daily'=>'Daily','3days'=>'Every 3 days','weekly'=>'Weekly','biweekly'=>'Bi-weekly','monthly'=>'Monthly'] as $key=>$label): ?>
                    <option value="<?= $key; ?>" <?= $inv->frequency===$key ? 'selected' : ''; ?>><?= $label; ?></option>
                  <?php endforeach; ?>
                </select>
                <input class="dr-max" type="number" min="0" value="<?= (int)$inv->max_reminders; ?>" title="Maximum reminders for this invoice; 0 means unlimited" aria-label="Maximum reminders">
                <label title="Send email"><input class="dr-email" type="checkbox" <?= $inv->send_email ? 'checked' : ''; ?>> Email</label>
                <label title="Send SMS"><input class="dr-sms" type="checkbox" <?= $inv->send_sms ? 'checked' : ''; ?>> SMS</label>
              </div>
            </td>
            <td><button type="button" class="mp-btn-save btn-save-invoice"><i class="fa fa-save"></i> Save</button></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="mp-empty"><i class="fa fa-check-circle" style="font-size:32px;color:#10B981;margin-bottom:10px"></i><p>No unpaid invoices found.</p></div>
    <?php endif; ?>
  </div>
</div>
<script>
$(function(){
  var csrfName='<?= $this->security->get_csrf_token_name(); ?>';
  var csrfHash='<?= $this->security->get_csrf_hash(); ?>';
  $('.btn-save-invoice').on('click',function(){
    var btn=$(this), row=btn.closest('tr');
    var data={invoice_id:row.data('invoice-id'),enabled:row.find('.dr-enabled').is(':checked')?1:0,
      frequency:row.find('.dr-frequency').val(),max_reminders:row.find('.dr-max').val(),
      send_email:row.find('.dr-email').is(':checked')?1:0,send_sms:row.find('.dr-sms').is(':checked')?1:0};
    data[csrfName]=csrfHash;
    btn.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.ajax({url:'<?= base_url('debt_reminder/update_customer'); ?>',type:'POST',dataType:'json',data:data})
      .done(function(res){if(res&&res.status==='success'){if(typeof toastr!=='undefined')toastr.success(res.message);else alert(res.message);}else{var m=res&&res.message?res.message:'Unable to save invoice settings';if(typeof toastr!=='undefined')toastr.error(m);else alert(m);}})
      .fail(function(){if(typeof toastr!=='undefined')toastr.error('Unable to save invoice settings.');else alert('Unable to save invoice settings.');})
      .always(function(){btn.prop('disabled',false).html('<i class="fa fa-save"></i> Save');});
  });
});
</script>
