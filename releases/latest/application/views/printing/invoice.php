<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
  $CI =& get_instance();
  $fmt = function ($v) { return number_format((float) $v, 2); };
?>
<div class="mp-section print-invoice">
  <div class="mp-page-head">
    <h2><?= isset($sales_id) && $sales_id ? 'Edit Print Invoice' : 'Print Invoice'; ?></h2>
    <div class="mp-page-sub">
      Pricing comes from the accepted quotation — a print job is priced by quote, not by a
      price list, so there is no wholesale/retail switch here.
    </div>
  </div>

  <?php
    // How this job's lines are grouped. Both styles sum the SAME rows, so the
    // total never changes — only whether the customer sees one line or the
    // itemised breakdown. That is why it can be switched at any time, including
    // after the quotation was accepted: nothing about the quote is touched.
    $style = strtolower((string) ($job->invoice_style ?? 'combined'));
    if (!in_array($style, ['combined', 'detailed'], true)) $style = 'combined';
  ?>
  <div class="mp-card" style="margin-bottom:14px"><div class="mp-card-body" style="padding:12px 14px">
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
      <b style="font-size:13px">Invoice style</b>
      <div class="btn-group" id="pi-style">
        <button type="button" class="btn btn-sm btn-default <?= $style === 'combined' ? 'active' : ''; ?>"
                data-style="combined" onclick="setInvoiceStyle('combined', this)">One line per job</button>
        <button type="button" class="btn btn-sm btn-default <?= $style === 'detailed' ? 'active' : ''; ?>"
                data-style="detailed" onclick="setInvoiceStyle('detailed', this)">Itemise each line</button>
      </div>
      <span id="pi-style-note" class="text-muted" style="font-size:12px">
        <?= $style === 'combined'
            ? 'The client sees one line for this job.'
            : 'The client sees every line on this job.'; ?>
      </span>
    </div>
  </div></div>

  <form id="print-invoice-form" onsubmit="return false;">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="sales_id" value="<?= (int) ($sales_id ?? 0); ?>">
    <input type="hidden" name="job_id" value="<?= (int) ($job_id ?? 0); ?>">
    <input type="hidden" name="quotation_id" value="<?= (int) ($quotation_id ?? 0); ?>">
    <input type="hidden" id="customer_id" name="customer_id" value="<?= (int) ($customer->id ?? 0); ?>">

    <div class="row">
      <div class="col-md-7">
        <div class="mp-card"><div class="mp-card-body">
          <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:.4px;color:#78716C;margin:0 0 12px">Invoice Details</h3>
          <div class="row">
            <div class="col-sm-6"><div class="form-group">
              <label>Client</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($customer->customer_name ?? 'Walk-in'); ?>" readonly style="background:#F5F5F4">
              <?php if (empty($customer->id)): ?><small class="text-muted">No client on this invoice.</small><?php endif; ?>
            </div></div>
            <div class="col-sm-6"><div class="form-group">
              <label>Invoice date</label>
              <input type="date" name="sales_date" class="form-control" value="<?= htmlspecialchars($sales_date ?? date('Y-m-d')); ?>">
            </div></div>
          </div>
          <div class="row">
            <div class="col-sm-6"><div class="form-group">
              <label>Payment status</label>
              <select name="payment_status" class="form-control">
                <?php foreach (['Unpaid' => 'Unpaid', 'Partially paid' => 'Partially paid', 'Paid' => 'Paid'] as $k => $v): ?>
                <option value="<?= $k ?>" <?= (($payment_status ?? 'Unpaid') === $k) ? 'selected' : ''; ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </div></div>
            <div class="col-sm-6"><div class="form-group">
              <label>Amount received</label>
              <input type="number" name="paid_amount" id="paid_amount" class="form-control" step="0.01" min="0"
                     value="<?= htmlspecialchars($paid_amount ?? '0.00'); ?>">
            </div></div>
          </div>
          <div class="form-group">
            <label>Notes on the invoice</label>
            <textarea name="sales_note" class="form-control" rows="2" placeholder="e.g. Balance due on collection"><?= htmlspecialchars($sales_note ?? ''); ?></textarea>
          </div>
        </div></div>
      </div>

      <div class="col-md-5">
        <div class="mp-card"><div class="mp-card-body">
          <h3 style="font-size:13px;text-transform:uppercase;letter-spacing:.4px;color:#78716C;margin:0 0 12px">What This Covers</h3>
          <?php if (empty($lines)): ?>
            <div class="mp-empty-state" style="padding:16px">No jobs on this invoice.</div>
          <?php else: ?>
            <?php foreach ($lines as $ln): ?>
            <div style="padding:8px 0;border-bottom:1px solid #F1F0EF">
              <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($ln['label']); ?></div>
              <div style="font-size:11.5px;color:#78716C">
                <?= htmlspecialchars($ln['meta'] ?: 'Print job'); ?>
                <?php if ($ln['job_code'] !== ''): ?> · <?= htmlspecialchars($ln['job_code']); ?><?php endif; ?>
              </div>
              <div style="display:flex;justify-content:space-between;margin-top:4px;font-size:12.5px">
                <span><?= htmlspecialchars($ln['qty_label']); ?></span>
                <b><?= $CI->currency($ln['line_total'], true); ?></b>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div></div>
      </div>
    </div>

    <!-- The invoice lines. One row per job, priced by its quotation. -->
    <div class="mp-card" style="margin-top:14px"><div class="mp-card-body" style="padding:0">
      <table class="table" style="margin:0" id="pi-lines">
        <thead>
          <tr>
            <th>Job</th>
            <th style="width:200px">Description</th>
            <th style="width:90px" class="text-right">Qty</th>
            <th style="width:120px" class="text-right">Unit price</th>
            <th style="width:120px" class="text-right">Amount</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($lines)): ?>
          <tr><td colspan="5" class="text-center text-muted" style="padding:24px">No lines to invoice.</td></tr>
        <?php else: foreach ($lines as $ln): ?>
          <tr>
            <td>
              <b><?= htmlspecialchars($ln['job_code'] ?: '—'); ?></b>
              <?php if ($ln['title'] !== ''): ?><div style="font-size:11.5px;color:#78716C"><?= htmlspecialchars($ln['title']); ?></div><?php endif; ?>
            </td>
            <td style="font-size:12.5px"><?= htmlspecialchars($ln['meta']); ?></td>
            <td class="text-right"><?= htmlspecialchars($ln['qty_label']); ?></td>
            <td class="text-right"><?= $CI->currency($ln['unit_price'], true); ?></td>
            <td class="text-right"><b><?= $CI->currency($ln['line_total'], true); ?></b></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" class="text-right" style="border-top:2px solid #E7E5E4">Subtotal</td>
            <td class="text-right" style="border-top:2px solid #E7E5E4"><?= $CI->currency($subtotal ?? 0, true); ?></td>
          </tr>
          <?php if (!empty($discount_total)): ?>
          <tr>
            <td colspan="4" class="text-right">Discount</td>
            <td class="text-right">−<?= $CI->currency($discount_total, true); ?></td>
          </tr>
          <?php endif; ?>
          <?php if (!empty($tax_total)): ?>
          <tr>
            <td colspan="4" class="text-right">Tax</td>
            <td class="text-right"><?= $CI->currency($tax_total, true); ?></td>
          </tr>
          <?php endif; ?>
          <tr>
            <td colspan="4" class="text-right" style="font-size:15px;font-weight:700">Total</td>
            <td class="text-right" style="font-size:15px;font-weight:700"><?= $CI->currency($grand_total ?? 0, true); ?></td>
          </tr>
        </tfoot>
      </table>
    </div></div>

    <div style="margin-top:14px;display:flex;gap:6px">
      <button class="btn btn-primary" id="pi-save" onclick="savePrintInvoice()">
        <i class="fa fa-check"></i> <?= isset($sales_id) && $sales_id ? 'Save Invoice' : 'Create Invoice'; ?>
      </button>
      <?php if (!empty($quotation_id)): ?>
      <a class="btn btn-default" href="<?= base_url('printing/quote_view/' . (int) ($job_id ?? 0)); ?>">
        <i class="fa fa-arrow-left"></i> Back to Quote
      </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<script>
/**
 * Switch how this job's invoice lines are grouped.
 *
 * A RELOAD is deliberate rather than patching the table in the browser: the
 * grouping is decided server-side from the job's stored style, so reloading is
 * what proves the setting actually persisted. Re-drawing the rows client-side
 * would show the new layout even if the save had failed, which is exactly the
 * kind of silent no-op this screen should not have.
 */
function setInvoiceStyle(style, btn) {
  $('#pi-style button').prop('disabled', true);
  $(btn).addClass('active').siblings().removeClass('active');
  var data = { job_id: <?= (int) ($job_id ?? 0); ?>, style: style };
  if (window.csrfName) data[window.csrfName] = window.csrfHash;
  $.post('<?= base_url('printing/invoice_style_set'); ?>', data, function (r) {
    $('#pi-style button').prop('disabled', false);
    if (r.status === 'ok') {
      if (r.csrf_hash) window.csrfHash = r.csrf_hash;
      toastr.success(r.message);
      setTimeout(function () { location.reload(); }, 600);
    } else {
      toastr.error(r.message || 'Could not change the style.');
    }
  }, 'json').fail(function () {
    $('#pi-style button').prop('disabled', false);
    toastr.error('Server error');
  });
}

function savePrintInvoice() {
  var btn = $('#pi-save').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving…');
  var fd = new FormData(document.getElementById('print-invoice-form'));
  $.ajax({
    url: '<?= base_url('printing/invoice_save'); ?>',
    type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json'
  }).done(function (r) {
    btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Invoice');
    if (r.status === 'ok') {
      if (r.csrf_hash) window.csrfHash = r.csrf_hash;
      toastr.success(r.message);
      if (r.redirect) { setTimeout(function () { location.href = r.redirect; }, 800); }
    } else {
      toastr.error(r.message || 'Could not save the invoice.');
    }
  }).fail(function () {
    btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Invoice');
    toastr.error('Server error');
  });
}
</script>
