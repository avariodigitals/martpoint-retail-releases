<?php if(!empty($system_accounts['enabled'])): $ctx=$system_accounts; ?>
<div class="pb-box" style="padding:16px;border:1px solid var(--mp-border);border-radius:10px;margin-bottom:16px">
  <h4>Billing account</h4>
  <?php if($ctx['bed']): ?>
    <p>Inpatient · <?= htmlspecialchars($ctx['bed']->ward_name.' / '.$ctx['bed']->bed_label); ?></p>
    <strong><?= htmlspecialchars($ctx['account'] ? $ctx['account']->account_name : 'No active linked account — ask the manager'); ?></strong>
    <input type="hidden" id="physio_account_choice" value="<?= $ctx['account'] ? (int)$ctx['account']->id : 0; ?>">
  <?php else: $chosen=(int)($bill->physio_system_account_id ?? 0); ?>
    <p>Outpatient · choose an account without a bed attached.</p>
    <input type="hidden" id="physio_account_choice" value="<?= $chosen; ?>">
    <details class="mp-field-picker"><summary>Choose billing account</summary>
      <div style="max-height:200px;overflow-y:auto;display:grid;gap:6px;padding:8px">
      <?php foreach($ctx['choices'] as $a): ?>
        <label style="display:flex;gap:8px;align-items:center"><input type="radio" name="physio_account_option" value="<?= (int)$a->id; ?>" <?= $chosen===(int)$a->id ? 'checked' : ''; ?> onchange="document.getElementById('physio_account_choice').value=this.value; document.getElementById('physio_account_label').textContent=this.dataset.label;" data-label="<?= htmlspecialchars($a->account_name,ENT_QUOTES,'UTF-8'); ?>"> <?= htmlspecialchars($a->account_name); ?></label>
      <?php endforeach; ?>
      <?php if(!$ctx['choices']): ?><p>Create an active non-bed account in Accounts first.</p><?php endif; ?>
      </div>
    </details><strong id="physio_account_label"><?php foreach($ctx['choices'] as $a){ if((int)$a->id===$chosen) echo htmlspecialchars($a->account_name); } ?></strong>
  <?php endif; ?>
  <p style="font-size:12px">Direct payments credit this account. Wallet and store-credit settlement apply existing funds.</p>
</div>
<?php endif; ?>
