<?php
$this->load->view('mobile/gas_head', [
	'gas_title' => 'Forecourt Limits',
	'gas_sub' => 'How much of a difference the company accepts without a signature',
	'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
	'theme_link' => $theme_link,
]);
?>

<div class='callout info'>Both limits apply to every site at once, so head office sets them and the forecourts work to them.</div>

<div class='card'>
  <div class='section-title'>Loss limits</div>
  <?= form_open(base_url('oil_gas/save_forecourt_settings')); ?>
  <div class='form-group'>
    <label>Duty: gap between pumps and till (%)</label>
    <input type='number' step='0.01' min='0' max='100' name='duty_variance' value='<?= htmlspecialchars(rtrim(rtrim(number_format((float)$duty_tolerance, 2), '0'), '.')); ?>'>
    <span class='hint'>A duty that measured 10,000 L and rang up 9,900 L is 1% short. Set 1 and it posts on its own; set 0.5 and a manager has to accept it.</span>
  </div>
  <div class='form-group'>
    <label>Tank: gap between a dip and the book (% of capacity)</label>
    <input type='number' step='0.01' min='0' max='100' name='tank_variance' value='<?= htmlspecialchars(rtrim(rtrim(number_format((float)$tank_tolerance, 2), '0'), '.')); ?>'>
    <span class='hint'>Measured against the size of the tank, so a 40 L swing in a 20,000 L tank is 0.2% and passes as evaporation.</span>
  </div>
  <p class='note'>Zero is allowed and means the strictest rule: every difference, however small, waits for a manager.</p>
  <button type='submit' class='btn btn-primary' style='margin-top:10px;'><i class='fa fa-check'></i> Save limits</button>
  <?= form_close(); ?>
</div>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
