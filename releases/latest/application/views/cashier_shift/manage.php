<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2>Manage Cashier Shifts</h2>
      <div class="mp-page-sub">Open, close and review shifts</div>
    </div>
  </div>
</div>

    <section class="content">
      <div class="cs-manage">

        <?php if($open_shift): ?>
        <!-- ── SHIFT OPEN ── -->
        <div class="cs-card">
          <div class="cs-title-row">
            <h2 class="cs-title">Shift is Open</h2>
            <span class="cs-pill cs-pill-open"><i class="fa fa-circle" style="font-size:6px;"></i> Open</span>
          </div>
          <p class="cs-sub">Your till is active. Sales are being tracked against this shift. Count your cash and close when you're done.</p>

          <div class="cs-status-grid">
            <div class="cs-status-cell">
              <div class="k">Shift Code</div>
              <div class="v mono"><?=htmlspecialchars($open_shift->shift_code);?></div>
            </div>
            <div class="cs-status-cell">
              <div class="k">Cashier</div>
              <div class="v"><?=htmlspecialchars($open_shift->cashier_username);?></div>
            </div>
            <div class="cs-status-cell">
              <div class="k">Till</div>
              <div class="v"><?=htmlspecialchars($open_shift->till_name ?: $open_shift->till_label ?: '—');?></div>
            </div>
            <div class="cs-status-cell">
              <div class="k">Account Balance</div>
              <div class="v"><?=store_number_format($open_shift->till_balance ?: 0);?></div>
            </div>
            <div class="cs-status-cell">
              <div class="k">Opening Float</div>
              <div class="v"><?=store_number_format($open_shift->opening_float);?></div>
            </div>
            <div class="cs-status-cell">
              <div class="k">Opened At</div>
              <div class="v"><?=date('d M Y, H:i', strtotime($open_shift->opened_at));?></div>
            </div>
          </div>

          <a href="<?=base_url('cashier_shifts/close_form');?>" class="cs-btn cs-btn-danger">
            <i class="fa fa-lock"></i> Close Shift &amp; Count Cash
          </a>
        </div>

        <?php else: ?>
        <!-- ── NO SHIFT — OPEN FORM ── -->
        <div class="cs-card">
          <h2 class="cs-title">Open a Cashier Shift</h2>
          <p class="cs-sub">Count the cash you're starting the till with and open a shift. Sales will be tracked against this shift, and you'll reconcile (count vs expected) when you close.</p>

          <form id="open-form" onkeypress="return event.keyCode != 13;">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">

            <div class="cs-form-group">
              <label for="till_id">Select Till <span class="hint">(where the cash is counted)</span></label>
              <select class="cs-input" id="till_id" name="till_id" required>
                <option value="">— Choose a till —</option>
                <?php if(count($tills) > 0): foreach($tills as $t): ?>
                <option value="<?=intval($t->id);?>"><?=htmlspecialchars($t->till_name);?> <?=($t->first_name || $t->last_name) ? '— '.htmlspecialchars(trim($t->first_name.' '.$t->last_name)) : ($t->account_name ? '('.$t->account_name.')' : '');?> <?=($t->is_default ? '(Default)' : '');?></option>
                <?php endforeach; else: ?>
                <option value="" disabled>No active tills. Ask an admin to create one.</option>
                <?php endif; ?>
              </select>
            </div>

            <div class="cs-form-group">
              <label for="opening_float">Opening Cash Float</label>
              <div class="cs-input-group">
                <span class="cs-input-addon"><i class="fa fa-money"></i></span>
                <input type="number" step="0.01" min="0" class="cs-input" id="opening_float" name="opening_float" value="0" required>
              </div>
            </div>

            <button type="button" id="open-btn" class="cs-btn cs-btn-primary" <?=count($tills) === 0 ? 'disabled' : '';?>>
              <i class="fa fa-play"></i> Open Shift
            </button>
          </form>
        </div>
        <?php endif; ?>

      </div>
    </section>
<script>
var base_url = "<?=base_url();?>";
$("#open-btn").on("click", function(){
  var btn = $(this); btn.attr('disabled', true);
  var data = new FormData($('#open-form')[0]);
  $.ajax({
    type:'POST', url: base_url+'cashier_shifts/open', data: data,
    cache:false, contentType:false, processData:false, dataType:'json',
    success: function(r){
      btn.attr('disabled', false);
      if(r.status === 'success'){
        toastr.success('Shift '+r.shift_code+' opened successfully.');
        setTimeout(function(){ window.location.href = base_url+'cashier_shifts/manage'; }, 800);
      } else {
        toastr.error(r.message || 'Could not open shift.');
      }
    },
    error: function(){ btn.attr('disabled', false); toastr.error('Server error. Please try again.'); }
  });
});
<?php if(count($tills) === 1): ?>
$("#till_id option:not([value=''])").first().prop('selected', true);
<?php endif; ?>
</script>
<script>$(".cashier-shifts-active-li").addClass("active");</script>

<style>
/* The page JS appends its loading .overlay to $(".box"), so the .box wrapper
   must stay; only its own frame is stripped so the card is not flush against
   a second border. */
.box.mp-items-box { border: none !important; background: transparent !important; box-shadow: none !important; border-radius: 0 !important; }
.mp-form-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; padding: 16px 20px; border-top: 1px solid var(--mp-border); background: var(--mp-bg); border-radius: 0 0 16px 16px; }
</style>
