<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Sales and orders by table — POS and QR/online</div>
  </div>
</div>

<div class="mp-card">
  <div class="mp-card-body" style="padding:0;">
    <div class="table-responsive">
      <table class="table mp-dt-table" id="report-data">
        <thead>
          <tr>
            <th>Table</th>
            <th class="text-right">POS Orders</th>
            <th class="text-right">POS Total</th>
            <th class="text-right">QR Orders</th>
            <th class="text-right">QR Total</th>
            <th class="text-right">Total Orders</th>
            <th class="text-right">Grand Total</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($rows)): ?>
          <tr><td colspan="7" class="text-center text-muted">No table data found</td></tr>
          <?php else: ?>
            <?php foreach($rows as $r): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($r->table_name); ?></strong>
                <?php if(!empty($r->zone)): ?><span class="text-muted">(<?= htmlspecialchars($r->zone); ?>)</span><?php endif; ?>
              </td>
              <td class="text-right"><?= (int)$r->pos_orders; ?></td>
              <td class="text-right"><?= store_number_format($r->pos_total); ?></td>
              <td class="text-right"><?= (int)$r->online_orders; ?></td>
              <td class="text-right"><?= store_number_format($r->online_total); ?></td>
              <td class="text-right"><?= (int)($r->pos_orders + $r->online_orders); ?></td>
              <td class="text-right"><strong><?= store_number_format($r->pos_total + $r->online_total); ?></strong></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr style="background:var(--mp-surface);font-weight:700;">
            <td>Total</td>
            <td class="text-right"><?= (int)$totals->pos_orders; ?></td>
            <td class="text-right"><?= store_number_format($totals->pos_total); ?></td>
            <td class="text-right"><?= (int)$totals->online_orders; ?></td>
            <td class="text-right"><?= store_number_format($totals->online_total); ?></td>
            <td class="text-right"><?= (int)($totals->pos_orders + $totals->online_orders); ?></td>
            <td class="text-right"><?= store_number_format($totals->pos_total + $totals->online_total); ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
