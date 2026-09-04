<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-backdrop" id="paymentPopup">
  <div class="modal" style="max-width: 720px;">
    <h3 id="paymentPopupTitle">Payment</h3>
    <input type="hidden" id="paymentPopupMode" value="pay">

    <div class="summary-row total" style="margin: 16px 0; padding: 12px 0; border-top: 1px solid var(--mp-border);">
      <span>Amount Due</span>
      <span id="paymentAmountDue" style="font-size: 20px;">₦0.00</span>
    </div>

    <div id="paymentRows" style="display: flex; flex-direction: column; gap: 10px; margin: 16px 0;"></div>
    <button class="pay-action" id="addPaymentRowBtn" style="width: 100%; margin-top: 8px; display: none;" onclick="addPaymentRow()">+ Add Payment Row</button>

    <div id="bnplSection" class="bnpl-card" style="display: none;">
      <h4 class="bnpl-title">Payment Plan</h4>
      <div class="bnpl-grid">
        <div class="bnpl-field">
          <label>Down Payment %</label>
          <input type="number" id="bnplDownPct" class="bnpl-input" value="30" min="0" max="100" step="1" oninput="updatePaymentBalance()">
        </div>
        <div class="bnpl-field">
          <label>Installments</label>
          <select id="bnplCount" class="bnpl-select" onchange="updatePaymentBalance()">
            <option value="2">2</option>
            <option value="3" selected>3</option>
            <option value="4">4</option>
            <option value="6">6</option>
            <option value="8">8</option>
            <option value="12">12</option>
          </select>
        </div>
        <div class="bnpl-field">
          <label>Frequency</label>
          <select id="bnplFrequency" class="bnpl-select" onchange="updatePaymentBalance()">
            <option value="weekly">Weekly</option>
            <option value="biweekly" selected>Bi-Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>
      </div>
      <div class="bnpl-grid" style="margin-top: 14px;">
        <div class="bnpl-field">
          <label>Down Payment</label>
          <input type="text" id="bnplDownAmt" class="bnpl-input bnpl-computed" readonly>
        </div>
        <div class="bnpl-field">
          <label>Each Installment</label>
          <input type="text" id="bnplEachAmt" class="bnpl-input bnpl-computed" readonly>
        </div>
        <div class="bnpl-field">
          <label>First Due</label>
          <input type="date" id="bnplFirstDue" class="bnpl-input" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
        </div>
      </div>
      <div class="bnpl-field" style="margin-top: 14px;">
        <label>Late Fee / Day</label>
        <input type="number" id="bnplLateFee" class="bnpl-input" value="0" min="0" step="0.01">
      </div>
    </div>

    <div class="summary-row" style="margin-top: 12px; font-size: 15px; font-weight: 600;">
      <span>Total Paid</span>
      <span id="paymentPaid">₦0.00</span>
    </div>
    <div class="summary-row" style="font-size: 15px; font-weight: 700; color: var(--mp-danger);">
      <span>Balance</span>
      <span id="paymentBalance">₦0.00</span>
    </div>

    <div class="modal-actions" style="margin-top: 20px;">
      <button class="btn-secondary" onclick="closePaymentPopup()">Cancel</button>
      <button class="btn-primary" id="paymentSubmitBtn" onclick="processPayment()">Pay</button>
    </div>
  </div>
</div>
<script>
  window.paymentModeOptions = <?= json_encode(get_payment_modes_select_list(get_current_store_id())) ?>;
  window.accountOptions = <?= json_encode(get_accounts_select_list()) ?>;
  window.defaultPaymentMode = <?= json_encode(get_default_payment_mode_code()) ?>;
  window.cashAccountId = <?= json_encode($till_account_id ?? '') ?>;
</script>
