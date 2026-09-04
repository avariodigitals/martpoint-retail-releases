<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-backdrop" id="salesListPopup">
  <div class="modal" style="max-width: 720px; max-height: 80vh; display: flex; flex-direction: column;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
      <h3 style="margin: 0;">Today's Sales</h3>
      <button onclick="window.closeSalesListPopup()" style="background: none; border: none; cursor: pointer; padding: 4px; color: var(--mp-muted);">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div style="flex: 1; overflow-y: auto; border: 1px solid var(--mp-border); border-radius: 12px;">
      <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
        <thead style="background: var(--mp-bg); position: sticky; top: 0;">
          <tr>
            <th style="padding: 12px; text-align: left; font-weight: 600; border-bottom: 1px solid var(--mp-border);">Invoice</th>
            <th style="padding: 12px; text-align: left; font-weight: 600; border-bottom: 1px solid var(--mp-border);">Customer</th>
            <th style="padding: 12px; text-align: right; font-weight: 600; border-bottom: 1px solid var(--mp-border);">Amount</th>
            <th style="padding: 12px; text-align: center; font-weight: 600; border-bottom: 1px solid var(--mp-border);">Time</th>
          </tr>
        </thead>
        <tbody id="salesListBody">
          <tr><td colspan="4" style="padding: 24px; text-align: center; color: var(--mp-muted);">Loading sales...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
