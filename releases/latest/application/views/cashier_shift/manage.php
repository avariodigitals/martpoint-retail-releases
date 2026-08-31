<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
<style>
  /* ── Cashier Shift — Manage ── */
  .cs-manage {
    max-width: 460px;
    margin: 0 auto;
    padding: 8px 16px 60px;
  }
  .cs-card {
    background: #fff;
    border: 1px solid #E5E7EB;
    border-radius: 18px;
    padding: 30px 28px;
    margin-bottom: 18px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 4px 10px -4px rgba(0,0,0,0.02);
  }
  .cs-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
  }
  .cs-title {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #111827;
    letter-spacing: -0.3px;
  }
  .cs-sub {
    margin: 0 0 24px;
    font-size: 14px;
    color: #6B7280;
    line-height: 1.55;
  }

  /* ── Shift status card (when open) ── */
  .cs-status-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1px;
    background: #E5E7EB;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 24px;
  }
  .cs-status-cell {
    background: #fff;
    padding: 14px 16px;
  }
  .cs-status-cell .k {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #9CA3AF;
    margin-bottom: 4px;
  }
  .cs-status-cell .v {
    font-size: 15px;
    font-weight: 600;
    color: #1F2937;
  }
  .cs-status-cell .v.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 13px;
  }

  /* ── Form ── */
  .cs-form-group { margin-bottom: 20px; }
  .cs-form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
  }
  .cs-form-group label .hint {
    font-weight: 400;
    color: #9CA3AF;
    font-size: 12px;
  }
  .cs-input {
    width: 100%;
    min-height: 48px;
    padding: 12px 14px;
    border: 1px solid #D1D5DB;
    border-radius: 12px;
    font-size: 15px;
    color: #111827;
    background: #fff;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .cs-input:focus {
    outline: none;
    border-color: #2563EB;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.10);
  }
  .cs-input-group {
    display: flex;
    align-items: stretch;
  }
  .cs-input-group .cs-input { border-radius: 0 12px 12px 0; }
  .cs-input-addon {
    display: flex;
    align-items: center;
    padding: 0 14px;
    background: #F3F4F6;
    border: 1px solid #D1D5DB;
    border-right: none;
    border-radius: 12px 0 0 12px;
    color: #6B7280;
    font-size: 14px;
  }

  /* ── Buttons ── */
  .cs-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    min-height: 50px;
    padding: 14px 20px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.05s, background-color 0.15s;
    text-decoration: none;
  }
  .cs-btn:active { transform: scale(0.99); }
  .cs-btn:disabled { opacity: 0.6; cursor: not-allowed; }
  .cs-btn-primary {
    background: #2563EB;
    color: #fff;
    box-shadow: 0 4px 12px rgba(37,99,235,0.25);
  }
  .cs-btn-primary:hover { background: #1D4ED8; color: #fff; }
  .cs-btn-danger  { background: #DC2626; color: #fff; }
  .cs-btn-danger:hover { background: #B91C1C; color: #fff; }

  /* ── Status pill ── */
  .cs-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
  }
  .cs-pill-open   { background: #FEF3C7; color: #B45309; }
  .cs-pill-closed { background: #DCFCE7; color: #15803D; }

  @media (max-width: 640px) {
    .cs-status-grid { grid-template-columns: 1fr; }
    .cs-card { padding: 24px 20px; }
    .cs-title { font-size: 20px; }
  }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include"sidebar.php"; ?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?=$page_title;?><small></small></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?=$page_title;?></li>
      </ol>
    </section>

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
  </div>
  <?php include"footer.php"; ?>
  <div class="control-sidebar-bg"></div>
</div>
<?php include"comman/code_js_sound.php"; ?>
<?php include"comman/code_js.php"; ?>
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
</body>
</html>