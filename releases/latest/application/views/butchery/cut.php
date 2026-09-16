<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head">
  <div>
    <h2>Cutting Worksheet</h2>
    <div class="mp-page-sub">Record actual weights and yields from a carcass.</div>
  </div>
  <a href="<?= base_url('butchery/receive'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-success" style="margin-top:20px;"><?= htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="mp-card-form" style="margin-bottom:0">
  <div class="mp-card-head">
    <h3><?= htmlspecialchars($carcass->carcass_name); ?> — Lot <?= htmlspecialchars($carcass->lot_number ?: '---'); ?></h3>
  </div>
  <div class="mp-card-body">
    <form method="post" action="<?= base_url('butchery/cut/' . $carcass->id); ?>">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

      <table class="mp-batch-items" id="cut-worksheet-table">
        <thead>
          <tr>
            <th>Cut Name</th>
            <th style="width:120px;">Expected (kg)</th>
            <th style="width:120px;">Actual (kg)</th>
            <th style="width:90px;">Packs</th>
            <th style="width:120px;">Waste (kg)</th>
            <th style="width:130px;">Cost</th>
            <th style="width:130px;">Price</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $rows = !empty($records) ? $records : $template_cuts;
          if (empty($rows)) $rows = [null, null, null];
          foreach ($rows as $i => $row):
            $name = $row->cut_name ?? '';
            $expected = isset($row->expected_weight_kg) ? $row->expected_weight_kg : ($row->expected_weight ?? 0);
            $actual = $row->actual_weight ?? 0;
            $packs = $row->packs ?? 1;
            $waste = $row->waste ?? 0;
            $purchase_price = $row->purchase_price ?? 0;
            $sales_price = $row->sales_price ?? 0;
            $notes = $row->notes ?? '';
          ?>
          <tr>
            <td><input type="text" class="mp-form-control" name="cuts[<?= $i; ?>][cut_name]" value="<?= htmlspecialchars($name); ?>" placeholder="Cut name"></td>
            <td><input type="number" step="0.01" class="mp-form-control" name="cuts[<?= $i; ?>][expected_weight]" value="<?= $expected; ?>"></td>
            <td><input type="number" step="0.01" class="mp-form-control" name="cuts[<?= $i; ?>][actual_weight]" value="<?= $actual; ?>"></td>
            <td><input type="number" step="1" class="mp-form-control" name="cuts[<?= $i; ?>][packs]" value="<?= $packs; ?>"></td>
            <td><input type="number" step="0.01" class="mp-form-control" name="cuts[<?= $i; ?>][waste]" value="<?= $waste; ?>"></td>
            <td><input type="number" step="0.01" class="mp-form-control" name="cuts[<?= $i; ?>][purchase_price]" value="<?= $purchase_price; ?>"></td>
            <td><input type="number" step="0.01" class="mp-form-control" name="cuts[<?= $i; ?>][sales_price]" value="<?= $sales_price; ?>"></td>
            <td><input type="text" class="mp-form-control" name="cuts[<?= $i; ?>][notes]" value="<?= htmlspecialchars($notes); ?>" placeholder="Notes"></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div style="margin-top:24px; text-align:right;">
        <button type="submit" class="mp-qa-btn blue"><i class="fa fa-check"></i> Complete Cutting</button>
      </div>
    </form>
  </div>
</div>
