<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">How much of a forecourt difference the company accepts without a signature. Both limits apply everywhere at once, so head office sets them and the sites work to them.</div>
  </div>
  <div class="mp-form-actions" style="margin:0;">
    <a href="<?= base_url('oil_gas/forecourt'); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Forecourt</a>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Forecourt loss limits</h3></div>
  <div class="mp-card-body">
    <?= form_open(base_url('oil_gas/save_forecourt_settings')); ?>
    <div class="gas-form">
      <div class="mp-form-group">
        <label for="duty_variance">Duty: gap between the pumps and the till (%)</label>
        <input class="mp-form-control" type="number" step="0.01" min="0" max="100" id="duty_variance" name="duty_variance" value="<?= htmlspecialchars(rtrim(rtrim(number_format((float)$duty_tolerance, 2), '0'), '.')); ?>">
        <div class="loc-sub">A duty that measured 10,000 L and rang up 9,900 L is 1% short. Set this to 1 and it posts on its own; set it to 0.5 and a manager has to accept it.</div>
      </div>
      <div class="mp-form-group">
        <label for="tank_variance">Tank: gap between a dip and the book (% of capacity)</label>
        <input class="mp-form-control" type="number" step="0.01" min="0" max="100" id="tank_variance" name="tank_variance" value="<?= htmlspecialchars(rtrim(rtrim(number_format((float)$tank_tolerance, 2), '0'), '.')); ?>">
        <div class="loc-sub">Measured against the size of the tank, so a 40 L swing in a 20,000 L tank is 0.2% and passes as evaporation.</div>
      </div>
    </div>
    <p class="gas-note">Zero is allowed and means the strictest rule: every difference, however small, waits for a manager.</p>
    <div class="mp-form-actions" style="margin-top:18px;">
      <button type="submit" class="mp-btn-primary"><i class="fa fa-check"></i> Save limits</button>
      <a href="<?= base_url('oil_gas/forecourt'); ?>" class="mp-btn-secondary"><i class="fa fa-times"></i> Close</a>
    </div>
    <?= form_close(); ?>
  </div>
</div>

<script>
  $(function(){ $('.oil_gas-forecourt-active-li').addClass('active'); });
</script>
