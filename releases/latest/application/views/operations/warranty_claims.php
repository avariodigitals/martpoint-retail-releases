<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2><div class="mp-page-sub">Register, inspect, decide and resolve after-sales warranty cases.</div></div>
  <a class="mp-btn-secondary" href="<?= base_url('operations/warranty_lookup'); ?>"><i class="fa fa-search"></i> Warranty lookup</a>
</div>

<?php if ($this->session->flashdata('success')): ?><div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('success')); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('error')): ?><div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error')); ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error); ?></div><?php endif; ?>

<?php if (!empty($unit)): ?>
<div class="mp-card-form" style="margin-bottom:20px;">
  <div class="mp-card-head"><h3>Open a warranty case</h3></div>
  <div class="mp-card-body">
    <p><strong><?= htmlspecialchars($unit->item_name ?? 'Device'); ?></strong> · <?= htmlspecialchars($unit->customer_name ?? 'Customer'); ?> · Invoice <?= htmlspecialchars($unit->sales_code ?? ''); ?></p>
    <p>Serial: <code><?= htmlspecialchars($unit->sold_serial_number ?? '—'); ?></code> · IMEI 1: <code><?= htmlspecialchars($unit->sold_imei_number ?? '—'); ?></code> · IMEI 2: <code><?= htmlspecialchars($unit->sold_imei_number_2 ?? '—'); ?></code> · Warranty ends <?= !empty($unit->warranty_end) ? htmlspecialchars(show_date($unit->warranty_end)) : 'not recorded'; ?></p>
    <?= form_open('operations/warranty_claims'); ?>
      <input type="hidden" name="salesitems_id" value="<?= (int)$unit->id; ?>">
      <div class="mp-form-group"><label>Customer reported issue</label><textarea class="mp-form-control" name="issue" rows="4" maxlength="8000" required placeholder="Describe the fault, when it started, and any visible damage."></textarea></div>
      <button class="mp-btn-primary" type="submit"><i class="fa fa-plus"></i> Open case</button>
    <?= form_close(); ?>
  </div>
</div>
<?php endif; ?>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Warranty cases</h3></div>
  <div class="mp-card-body">
    <form method="get" action="<?= base_url('operations/warranty_claims'); ?>" style="display:flex;gap:8px;margin-bottom:16px;">
      <input class="mp-form-control" type="search" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Claim, serial, IMEI, customer or invoice">
      <button class="mp-btn-primary" type="submit">Search</button>
    </form>
    <div class="mp-table-wrap"><div class="mp-dt-scroll"><table class="table mp-dt-table">
      <thead><tr><th>Case</th><th>Device / identity</th><th>Customer / invoice</th><th>Reported issue</th><th>Status</th><th>Update case</th></tr></thead>
      <tbody>
      <?php foreach (($claims ?? array()) as $claim): ?>
        <tr>
          <td><strong><?= htmlspecialchars($claim->claim_code); ?></strong><br><small><?= htmlspecialchars($claim->received_at); ?></small></td>
          <td><?= htmlspecialchars($claim->item_name ?? 'Device'); ?><br><small>SN <?= htmlspecialchars($claim->sold_serial_number ?? '—'); ?></small><br><small>IMEI <?= htmlspecialchars($claim->sold_imei_number ?? '—'); ?><?= !empty($claim->sold_imei_number_2) ? ' / '.htmlspecialchars($claim->sold_imei_number_2) : ''; ?></small></td>
          <td><?= htmlspecialchars($claim->customer_name ?? 'Customer'); ?><br><small><?= htmlspecialchars($claim->mobile ?? ''); ?></small><br><small><?= htmlspecialchars($claim->sales_code ?? ''); ?></small></td>
          <td style="min-width:220px;white-space:pre-wrap;"><?= htmlspecialchars($claim->issue); ?><?php if(!empty($claim->decision_note)): ?><br><small><strong>Decision:</strong> <?= htmlspecialchars($claim->decision_note); ?></small><?php endif; ?><?php if(!empty($claim->resolution_note)): ?><br><small><strong>Resolution:</strong> <?= htmlspecialchars($claim->resolution_note); ?></small><?php endif; ?></td>
          <td><span class="mp-pill"><?= htmlspecialchars(ucwords(str_replace('_',' ',$claim->status))); ?></span></td>
          <td style="min-width:250px;">
            <?php if (!empty($claim->next_statuses)): ?>
            <?= form_open('operations/warranty_claim_update/'.(int)$claim->id); ?>
              <select class="mp-form-control" name="status" required>
                <?php foreach($claim->next_statuses as $next): ?>
                  <option value="<?= $next; ?>"><?= htmlspecialchars(ucwords(str_replace('_',' ',$next))); ?></option>
                <?php endforeach; ?>
              </select>
              <textarea class="mp-form-control" name="decision_note" rows="2" placeholder="Inspection decision (required for approve/reject)"><?= htmlspecialchars($claim->decision_note ?? ''); ?></textarea>
              <textarea class="mp-form-control" name="resolution_note" rows="2" placeholder="Repair, replacement or resolution notes"><?= htmlspecialchars($claim->resolution_note ?? ''); ?></textarea>
              <button class="mp-btn-secondary" type="submit">Save update</button>
            <?= form_close(); ?>
            <?php else: ?><small>Closed <?= htmlspecialchars($claim->closed_at ?? ''); ?></small><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($claims)): ?><tr><td colspan="6" class="text-center">No warranty cases found.</td></tr><?php endif; ?>
      </tbody>
    </table></div></div>
  </div>
</div>
