<style>
  .cs-close-page { max-width: 1120px; margin: 0 auto; padding-bottom: 32px; color: #172033; }
  .cs-close-head { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin: 8px 0 20px; }
  .cs-close-kicker { color:#64748b; font-size:12px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; }
  .cs-close-head h2 { margin:5px 0 6px; font-size:27px; font-weight:750; color:#111827; }
  .cs-close-head p { margin:0; color:#64748b; font-size:14px; }
  .cs-close-card { background:#fff; border:1px solid #dfe5ee; border-radius:16px; box-shadow:0 8px 24px rgba(15,23,42,.06); overflow:hidden; }
  .cs-close-card-head { padding:20px 24px; border-bottom:1px solid #e8edf3; display:flex; align-items:center; justify-content:space-between; gap:16px; }
  .cs-close-shift { display:flex; align-items:center; gap:12px; min-width:0; }
  .cs-close-icon { width:44px; height:44px; flex:0 0 44px; display:grid; place-items:center; border-radius:13px; color:#b42318; background:#fff1f0; font-size:18px; }
  .cs-close-shift h3 { margin:0 0 4px; font-size:18px; font-weight:700; color:#172033; }
  .cs-close-shift .meta { color:#64748b; font-size:13px; }
  .cs-close-open-float { text-align:right; padding-left:20px; border-left:1px solid #e8edf3; }
  .cs-close-open-float span { display:block; color:#64748b; font-size:12px; margin-bottom:4px; }
  .cs-close-open-float strong { color:#172033; font-size:19px; font-variant-numeric:tabular-nums; }
  .cs-close-body { padding:22px 24px 24px; }
  .cs-close-guidance { display:flex; align-items:flex-start; gap:10px; margin:0 0 18px; padding:12px 14px; border:1px solid #dbeafe; border-radius:11px; background:#f5f9ff; color:#475569; font-size:13px; line-height:1.5; }
  .cs-close-guidance i { color:#2563eb; margin-top:2px; }
  .cs-close-guidance a { display:inline-block; margin-left:8px; color:#1d4ed8; font-weight:700; white-space:nowrap; }
  .cs-close-grid { border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; }
  .cs-close-grid-head, .cs-method-row { display:grid; grid-template-columns:minmax(170px,1.5fr) minmax(120px,1fr) minmax(170px,1.15fr) minmax(120px,1fr); gap:14px; align-items:center; padding:13px 16px; }
  .cs-close-grid-head { background:#f8fafc; color:#64748b; font-size:11px; font-weight:750; letter-spacing:.07em; text-transform:uppercase; }
  .cs-method-row { background:#fff; border-top:1px solid #edf1f5; min-height:76px; }
  .cs-method-row .lbl { color:#172033; font-size:14px; font-weight:700; text-transform:capitalize; }
  .cs-method-row .sub { margin-top:4px; color:#64748b; font-size:12px; }
  .cs-close-expected { color:#334155; font-size:14px; font-weight:650; font-variant-numeric:tabular-nums; text-align:right; }
  .cs-close-count-wrap { position:relative; }
  .cs-method-row input.counted-input { width:100%; min-height:44px; padding:9px 12px; border:1px solid #cbd5e1; border-radius:9px; background:#fff; color:#111827; font-size:15px; font-weight:650; font-variant-numeric:tabular-nums; text-align:right; box-shadow:none; }
  .cs-mobile-field-label { display:none; }
  .cs-method-row input.counted-input:focus { border-color:#2563eb; outline:0; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
  .cs-variance { text-align:right; font-size:14px; font-weight:750; font-variant-numeric:tabular-nums; }
  .cs-variance.pos { color:#047857; } .cs-variance.neg { color:#b42318; } .cs-variance.zero { color:#64748b; }
  .cs-close-summary { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; margin-top:18px; }
  .cs-close-total-card { border:1px solid #e2e8f0; border-radius:12px; padding:16px; background:#fff; }
  .cs-close-total-card.cash { background:#f8fbff; }
  .cs-close-total-title { display:flex; align-items:center; gap:8px; margin:0 0 12px; color:#334155; font-size:13px; font-weight:750; }
  .cs-close-total-title i { color:#2563eb; }
  .cs-close-total-line { display:flex; justify-content:space-between; gap:12px; padding:7px 0; color:#64748b; font-size:13px; }
  .cs-close-total-line strong { color:#172033; font-weight:700; font-variant-numeric:tabular-nums; }
  .cs-close-total-line.variance { border-top:1px solid #dbe3ed; margin-top:5px; padding-top:12px; color:#334155; font-weight:700; }
  .cs-close-total-line.variance strong { font-size:17px; }
  .cs-cash-denomination { margin-top:18px; padding:17px; border:1px solid #e2e8f0; border-radius:12px; background:#f8fafc; }
  .cs-cash-denomination h5 { margin:0 0 14px; color:#172033; font-size:14px; font-weight:750; }
  .cs-denom-grid { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px; }
  .cs-denom-item { padding:10px; border:1px solid #e2e8f0; border-radius:9px; background:#fff; }
  .cs-denom-item .denom-lbl { margin-bottom:6px; color:#64748b; font-size:12px; font-weight:650; }
  .cs-denom-item input { width:100%; min-height:40px; border:1px solid #cbd5e1; border-radius:7px; padding:7px 9px; text-align:right; }
  .cs-denom-total { display:flex; justify-content:space-between; margin-top:13px; padding-top:12px; border-top:1px solid #dbe3ed; color:#334155; font-size:14px; font-weight:700; }
  .cs-close-fields { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:20px; }
  .cs-close-fields label { display:block; margin-bottom:7px; color:#334155; font-size:13px; font-weight:700; }
  .cs-close-fields label small { color:#64748b; font-weight:400; }
  .cs-close-fields .form-control { min-height:44px; border:1px solid #cbd5e1; border-radius:9px; box-shadow:none; }
  .cs-close-fields .form-control:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
  .cs-close-fields textarea.form-control { min-height:84px; resize:vertical; }
  .cs-close-actions { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-top:22px; padding-top:18px; border-top:1px solid #e8edf3; }
  .cs-close-actions .help { max-width:520px; color:#64748b; font-size:12px; line-height:1.5; }
  .cs-close-actions .buttons { display:flex; gap:10px; }
  .cs-close-actions .btn { min-height:44px; padding:11px 18px; border-radius:9px; font-weight:700; }
  .cs-close-actions .btn-danger { background:#b42318; border-color:#b42318; }
  .cs-close-actions .btn-danger:hover, .cs-close-actions .btn-danger:focus { background:#912018; border-color:#912018; }
  @media(max-width:767px){
    .cs-close-head { margin:4px 0 14px; } .cs-close-head h2 { font-size:22px; }
    .cs-close-card-head { padding:16px; align-items:flex-start; } .cs-close-body { padding:16px; }
    .cs-close-grid-head { display:none; }
    .cs-close-grid .cs-method-row { grid-template-columns:1fr 1fr; gap:10px 14px; padding:13px; }
    .cs-close-grid .cs-method-row > div:first-child { grid-column:1/-1; }
    .cs-close-grid .cs-mobile-field-label { display:block; margin-bottom:4px; color:#64748b; font-size:10px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
    .cs-close-grid .cs-variance { text-align:right; }
    .cs-close-grid .cs-variance:before { content:'Variance'; display:block; margin-bottom:4px; color:#64748b; font-size:10px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
    .cs-close-summary, .cs-close-fields { grid-template-columns:1fr; }
    .cs-denom-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
    .cs-close-actions { align-items:stretch; flex-direction:column; }
    .cs-close-actions .buttons { display:grid; grid-template-columns:1fr 1fr; }
    .cs-close-actions .buttons .btn { white-space:normal; }
    .cs-close-guidance a { display:block; margin:7px 0 0; }
  }
</style>

<div class="mp-section">
  <div class="mp-page-head">
    <div><h2>Close Shift</h2><div class="mp-page-sub">Reconcile your till before ending this shift</div></div>
  </div>
</div>

<section class="content cs-close-page">
  <div class="cs-close-card">
    <div class="cs-close-card-head">
      <div class="cs-close-shift">
        <div class="cs-close-icon"><i class="fa fa-calculator"></i></div>
        <div>
          <div class="cs-close-kicker">Cash reconciliation</div>
          <h3>Count &amp; Close Shift</h3>
          <div class="meta"><strong><?=htmlspecialchars($shift->shift_code);?></strong> &middot; Opened <?=date('d M Y, H:i', strtotime($shift->opened_at));?></div>
        </div>
      </div>
      <div class="cs-close-open-float"><span>Opening cash float</span><strong><?=store_number_format($shift->opening_float);?></strong></div>
    </div>

    <div class="cs-close-body">
      <form id="close-form" onkeypress="return event.keyCode != 13;">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
        <input type="hidden" id="shift_id" name="shift_id" value="<?=$shift->id;?>">

        <div class="cs-close-guidance">
          <i class="fa fa-info-circle"></i>
          <div>Enter the amounts physically counted in the till. Expected values are calculated from this shift’s recorded payments; variance updates as you count.
            <a href="javascript:refreshExpected();"><i class="fa fa-refresh"></i> Refresh expected values</a>
          </div>
        </div>

        <div class="cs-close-grid">
          <div class="cs-close-grid-head"><div>Payment method</div><div class="text-right">Expected</div><div class="text-right">Counted amount</div><div class="text-right">Variance</div></div>
          <div id="method-rows"></div>
        </div>

        <div class="cs-cash-denomination" id="cash-denomination" style="display:none;">
          <h5><i class="fa fa-calculator text-primary"></i> Cash denomination count</h5>
          <div class="cs-denom-grid" id="denom-grid"></div>
          <div class="cs-denom-total"><span>Cash counted by denomination</span><span id="denom-total">0.00</span></div>
        </div>

        <div class="cs-close-summary">
          <div class="cs-close-total-card cash">
            <div class="cs-close-total-title"><i class="fa fa-money"></i> Cash reconciliation</div>
            <div class="cs-close-total-line"><span>Expected cash</span><strong id="tot-expected-cash"><?=store_number_format($expected['expected_cash']);?></strong></div>
            <div class="cs-close-total-line"><span>Counted cash</span><strong id="tot-counted-cash">0.00</strong></div>
            <div class="cs-close-total-line variance"><span>Cash variance</span><strong id="tot-cash-variance" class="cs-variance zero">0.00</strong></div>
          </div>
          <div class="cs-close-total-card">
            <div class="cs-close-total-title"><i class="fa fa-credit-card"></i> Other payment reconciliation</div>
            <div class="cs-close-total-line"><span>Expected non-cash</span><strong id="tot-expected-other"><?=store_number_format($expected['expected_other']);?></strong></div>
            <div class="cs-close-total-line"><span>Counted non-cash</span><strong id="tot-counted-other">0.00</strong></div>
            <div class="cs-close-total-line variance"><span>Non-cash variance</span><strong id="tot-other-variance" class="cs-variance zero">0.00</strong></div>
          </div>
        </div>

        <div class="cs-close-fields">
          <div class="form-group" style="margin:0;">
            <label for="close_note">Close notes <small>(optional; explain any variance)</small></label>
            <textarea class="form-control" id="close_note" name="close_note" rows="3" placeholder="Add a note for the shift record"></textarea>
          </div>
          <div class="form-group" style="margin:0;">
            <label for="manager_pin">Manager sign-off PIN <small>(only required if your store requires approval)</small></label>
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-user-secret"></i></span>
              <input type="password" class="form-control" id="manager_pin" name="manager_pin" placeholder="Enter manager PIN when requested" autocomplete="off">
            </div>
          </div>
        </div>

        <div class="cs-close-actions">
          <div class="help"><i class="fa fa-lock"></i> Closing records the counts and variance for this shift. Check the figures before confirming.</div>
          <div class="buttons">
            <a href="<?=base_url('cashier_shifts/manage');?>" class="btn btn-default">Keep shift open</a>
            <button type="button" id="close-btn" class="btn btn-danger"><i class="fa fa-lock"></i> Confirm &amp; Close Shift</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
<?php $CI =& get_instance(); ?>
var base_url = "<?=base_url();?>";
var expectedData = <?=json_encode($expected);?>;
var can_view_report = <?= $CI->permissions('z_report') ? 'true' : 'false' ?>;

function fmt(n){ return (n*1).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }
function num(v){ var n = parseFloat(v); return isNaN(n) ? 0 : n; }

function renderMethods(){
  var rows = '';
  var methods = expectedData.methods || [];
  if(methods.length === 0){ methods = [{payment_type:'cash', affects_cash:1, expected: expectedData.expected_cash, txn_count:0}]; }
  methods.forEach(function(m, idx){
    var isCash = (m.affects_cash == 1 || m.affects_cash === '1' || m.affects_cash === true);
    var cashFlag = isCash ? 1 : 0;
    rows += '<div class="cs-method-row" data-cash="'+cashFlag+'" data-idx="'+idx+'">';
    rows += '  <div><div class="lbl">'+htmlspecialchars(m.payment_type)+'</div><div class="sub">'+(isCash ? 'Cash · includes opening float' : 'Bank / POS')+(m.txn_count ? ' · '+m.txn_count+' transactions' : '')+'</div></div>';
    rows += '  <div class="cs-close-expected"><span class="cs-mobile-field-label">Expected</span>'+fmt(m.expected)+'</div>';
    rows += '  <div class="cs-close-count-wrap"><span class="cs-mobile-field-label">Counted amount</span><input type="number" step="0.01" min="0" inputmode="decimal" class="form-control counted-input" data-expected="'+m.expected+'" data-cash="'+cashFlag+'" value="'+(isCash ? parseFloat(m.expected).toFixed(2) : '0.00')+'" aria-label="Counted amount for '+htmlspecialchars(m.payment_type)+'"></div>';
    rows += '  <div class="cs-variance zero" data-var><span class="cs-mobile-field-label">Variance</span><span class="variance-value">0.00</span></div></div>';
  });
  $('#method-rows').html(rows); recalc(); buildDenominationGrid();
}

function htmlspecialchars(s){ return String(s).replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]; }); }

function recalc(){
  var totCountedCash = 0, totCountedOther = 0;
  $('.counted-input').each(function(){
    var counted = num($(this).val()), expected = num($(this).attr('data-expected'));
    var isCash = ($(this).attr('data-cash') === '1'), variance = counted - expected;
    var vEl = $(this).closest('.cs-method-row').find('[data-var]');
    vEl.removeClass('pos neg zero').addClass(variance>0.001?'pos':(variance<-0.001?'neg':'zero')).find('.variance-value').text(fmt(variance));
    if(isCash){ totCountedCash += counted; } else { totCountedOther += counted; }
  });
  $('#tot-counted-cash').text(fmt(totCountedCash)); $('#tot-counted-other').text(fmt(totCountedOther));
  var cashVar = totCountedCash - num(expectedData.expected_cash), othVar = totCountedOther - num(expectedData.expected_other);
  $('#tot-cash-variance').removeClass('pos neg zero').addClass(cashVar>0.001?'pos':(cashVar<-0.001?'neg':'zero')).text(fmt(cashVar));
  $('#tot-other-variance').removeClass('pos neg zero').addClass(othVar>0.001?'pos':(othVar<-0.001?'neg':'zero')).text(fmt(othVar));
}

function refreshExpected(){
  $.get(base_url+'cashier_shifts/expected_api', function(r){
    if(r.status === 'success'){ expectedData = r.expected; renderMethods(); toastr.success('Expected amounts refreshed.'); }
    else { toastr.error(r.message || 'Could not refresh.'); }
  }, 'json').fail(function(){ toastr.error('Could not refresh expected values. Please try again.'); });
}

$(document).on('input', '.counted-input', recalc);
var cashDenoms = [1000,500,200,100,50,20,10,5,2,1,0.5,0.2,0.1,0.05];
function buildDenominationGrid(){
  var cashInput = $('#close-form .counted-input[data-cash="1"]').first();
  if(cashInput.length === 0){ $('#cash-denomination').hide(); return; }
  var html = '';
  cashDenoms.forEach(function(d){ html += '<div class="cs-denom-item"><div class="denom-lbl">₦'+fmt(d)+'</div><input type="number" min="0" step="1" inputmode="numeric" class="form-control denom-qty" data-denom="'+d+'" value="0" aria-label="Number of ₦'+fmt(d)+' notes or coins"></div>'; });
  $('#denom-grid').html(html); $('#cash-denomination').show();
}
function updateDenominationTotal(){
  var total = 0;
  $('.denom-qty').each(function(){ total += num($(this).val()) * num($(this).attr('data-denom')); });
  $('#denom-total').text(fmt(total));
  $('#close-form .counted-input[data-cash="1"]').first().val(total.toFixed(2)).trigger('input');
}
$(document).on('input', '.denom-qty', updateDenominationTotal);

$("#close-btn").on("click", function(){
  var btn = $(this); btn.prop('disabled', true);
  var counts = [];
  $('.cs-method-row').each(function(){ counts.push({payment_type: $(this).find('.lbl').text(), counted_amount: num($(this).find('.counted-input').val())}); });
  var data = { "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash();?>", shift_id: $('#shift_id').val(), counts: JSON.stringify(counts), manager_pin: $('#manager_pin').val(), close_note: $('#close_note').val() };
  $.ajax({ type:'POST', url: base_url+'cashier_shifts/close', data:data, dataType:'json',
    success:function(r){
      btn.prop('disabled', false);
      if(r.status === 'success'){
        var msg = 'Shift closed. Cash variance: '+fmt(r.cash_variance);
        if(r.cash_variance > 0.001){ toastr.warning(msg); } else if(r.cash_variance < -0.001){ toastr.error(msg); } else { toastr.success('Shift closed. Cash reconciled perfectly.'); }
        setTimeout(function(){ window.location.href = can_view_report ? base_url+'cashier_shifts/view/'+$('#shift_id').val() : base_url+'cashier_shifts/manage'; }, 1200);
      } else { toastr.error(r.message || 'Could not close shift.'); }
    },
    error:function(){ btn.prop('disabled', false); toastr.error('Server error. Please try again.'); }
  });
});

renderMethods();
</script>
<script>$(".cashier-shift-close-active-li").addClass("active");</script>
