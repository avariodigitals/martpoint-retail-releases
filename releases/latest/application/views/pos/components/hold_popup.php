<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-backdrop" id="holdPopup">
  <div class="modal" style="max-width: 720px;">
    <h3>Recent Holds</h3>
    <p>Held invoices from today. Select one to load or delete.</p>
    <div class="table-responsive" style="max-height: 360px; overflow-y: auto; margin: 16px 0;">
      <table style="width:100%; border-collapse: collapse; font-size: 14px;">
        <thead>
          <tr style="text-align:left; border-bottom: 1px solid var(--mp-border); color: var(--mp-muted);">
            <th style="padding: 10px;">Reference</th>
            <th style="padding: 10px;">Customer</th>
            <th style="padding: 10px;">Total</th>
            <th style="padding: 10px;">Date</th>
            <th style="padding: 10px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($holds) && is_array($holds)) : ?>
            <?php foreach ($holds as $h) : ?>
              <tr style="border-bottom: 1px solid var(--mp-border);" data-hold-id="<?= $h['id'] ?>">
                <td style="padding: 10px;"><?= htmlspecialchars($h['reference_id'] ?? '-') ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($h['customer_name'] ?? 'Walk-in') ?></td>
                <td style="padding: 10px; font-weight: 600;">₦<?= store_number_format($h['grand_total'] ?? 0, true) ?></td>
                <td style="padding: 10px;"><?= show_date($h['sales_date'] ?? '') ?></td>
                <td style="padding: 10px; white-space: nowrap; text-align: right;">
                  <button class="btn-primary" style="padding:6px 12px; font-size:12px;" onclick="loadHold(<?= (int)$h['id'] ?>)">Load</button>
                  <button class="btn-secondary" style="padding:6px 12px; font-size:12px;" onclick="deleteHold(<?= (int)$h['id'] ?>)">Delete</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr><td colspan="5" style="padding: 24px; text-align: center; color: var(--mp-muted);">No recent holds</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="modal-actions">
      <button class="btn-secondary" onclick="closeHoldPopup()">Close</button>
    </div>
  </div>
</div>
