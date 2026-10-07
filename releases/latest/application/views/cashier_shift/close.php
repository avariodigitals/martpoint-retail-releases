<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2>Close Shift</h2>
      <div class="mp-page-sub">Close the open shift</div>
    </div>
  </div>
</div>

    <section class="content">
      <div class="row">
        <div class="col-md-10 col-md-offset-1">

          <div class="cs-card">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
              <div>
                <h3 style="margin:0;font-weight:700;"><i class="fa fa-lock text-danger"></i> Count &amp; Close Shift</h3>
                <div class="cs-mono" style="color:#64748B;"><?=htmlspecialchars($shift->shift_code);?> &middot; opened <?=date('d-m-Y H:i', strtotime($shift->opened_at));?></div>
              </div>
              <div style="text-align:right;">
                <div style="font-size:12px;color:#64748B;">Opening Float</div>
                <div style="font-size:18px;font-weight:700;"><?=store_number_format($shift->opening_float);?></div>
              </div>
            </div>

            <form id="close-form" onkeypress="return event.keyCode != 13;">
              <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
              <input type="hidden" id="shift_id" name="shift_id" value="<?=$shift->id;?>">

              <div style="margin-bottom:6px;color:#64748B;font-size:13px;">
                <i class="fa fa-info-circle"></i> Count the actual cash and slips in the till. Expected amounts are computed from sales attributed to you during this shift.
                <a href="javascript:refreshExpected();" style="margin-left:8px;"><i class="fa fa-refresh"></i> Refresh expected</a>
              </div>

              <div class="cs-method-row" style="border-bottom:2px solid #E2E8F0;font-weight:700;color:#64748B;font-size:12px;text-transform:uppercase;">
                <div>Payment Method</div>
                <div class="text-right">Expected</div>
                <div class="text-right">Counted</div>
                <div class="text-right">Variance</div>
              </div>
              <div id="method-rows"></div>

              <div class="cs-cash-denomination" id="cash-denomination" style="display:none;">
                <h5><i class="fa fa-calculator text-primary"></i> Cash Denomination Count</h5>
                <div class="cs-denom-grid" id="denom-grid"></div>
                <div class="cs-denom-total">Total Counted: <span id="denom-total">0.00</span></div>
              </div>

              <div class="cs-totals">
                <div class="row-line"><span>Total Cash Expected</span><span id="tot-expected-cash"><?=store_number_format($expected['expected_cash']);?></span></div>
                <div class="row-line"><span>Total Cash Counted</span><span id="tot-counted-cash">0.00</span></div>
                <div class="row-line big"><span>Cash Variance</span><span id="tot-cash-variance" class="cs-variance zero">0.00</span></div>
                <div class="row-line" style="margin-top:8px;"><span>Non-Cash Expected</span><span id="tot-expected-other"><?=store_number_format($expected['expected_other']);?></span></div>
                <div class="row-line"><span>Non-Cash Counted</span><span id="tot-counted-other">0.00</span></div>
                <div class="row-line big"><span>Non-Cash Variance</span><span id="tot-other-variance" class="cs-variance zero">0.00</span></div>
              </div>

              <div class="form-group" style="margin-top:18px;">
                <label for="close_note">Notes <small style="color:#94A3B8;">(optional — explain any variance)</small></label>
                <textarea class="form-control" id="close_note" name="close_note" rows="2" placeholder="e.g. Short by 200 — gave wrong change on invoice #12" style="border-radius:8px;"></textarea>
              </div>

              <div class="form-group">
                <label for="manager_pin">Manager Sign-off PIN <small style="color:#94A3B8;">(optional — required if your store mandates sign-off)</small></label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-user-secret"></i></span>
                  <input type="password" class="form-control" id="manager_pin" name="manager_pin" placeholder="Leave blank if not required" style="border-radius:8px;">
                </div>
              </div>

              <button type="button" id="close-btn" class="btn btn-danger btn-block" style="border-radius:8px;padding:12px;font-weight:700;">
                <i class="fa fa-lock"></i> Confirm &amp; Close Shift
              </button>
              <a href="<?=base_url('cashier_shifts/manage');?>" class="btn btn-default btn-block" style="border-radius:8px;margin-top:8px;">Cancel</a>
            </form>
          </div>

        </div>
      </div>
    </section>
<script>
// $CI was used below without ever being defined in this view — any request that
// reached close_form() with an open shift fataled on an undefined variable.
// Define it like the other ported views do.
<?php $CI =& get_instance(); ?>
var base_url = "<?=base_url();?>";
var expectedData = <?=json_encode($expected);?>;
var can_view_report = <?= $CI->permissions('z_report') ? 'true' : 'false' ?>;

function fmt(n){ return (n*1).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }
function num(v){ var n = parseFloat(v); return isNaN(n) ? 0 : n; }

function renderMethods(){
  var rows = '';
  var methods = expectedData.methods || [];
  if(methods.length === 0){
    methods = [{payment_type:'cash', affects_cash:1, expected: expectedData.expected_cash, txn_count:0}];
  }
  methods.forEach(function(m, idx){
    var isCash = (m.affects_cash == 1 || m.affects_cash === '1' || m.affects_cash === true);
    var cashFlag = isCash ? 1 : 0;
    rows += '<div class="cs-method-row" data-cash="'+cashFlag+'" data-idx="'+idx+'">';
    rows += '  <div><div class="lbl">'+htmlspecialchars(m.payment_type)+'</div>';
    rows += '  <div class="sub">'+(isCash ? 'Cash (includes opening float)' : 'Bank / POS')+(m.txn_count ? ' &middot; '+m.txn_count+' txn' : '')+'</div></div>';
    rows += '  <div class="text-right">'+fmt(m.expected)+'</div>';
    rows += '  <div class="text-right"><input type="number" step="0.01" min="0" class="form-control counted-input" data-expected="'+m.expected+'" data-cash="'+cashFlag+'" value="'+(isCash ? parseFloat(m.expected).toFixed(2) : '0.00')+'" style="text-align:right;"></div>';
    rows += '  <div class="text-right cs-variance zero" data-var>0.00</div>';
    rows += '</div>';
  });
  $('#method-rows').html(rows);
  recalc();
  buildDenominationGrid();
}

function htmlspecialchars(s){ return String(s).replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]; }); }

function recalc(){
  var totCountedCash = 0, totCountedOther = 0;
  $('.counted-input').each(function(){
    var counted = num($(this).val());
    var expected = num($(this).attr('data-expected'));
    var isCash = ($(this).attr('data-cash') === '1');
    var variance = counted - expected;
    var vEl = $(this).closest('.cs-method-row').find('[data-var]');
    vEl.removeClass('pos neg zero').addClass(variance>0.001?'pos':(variance<-0.001?'neg':'zero')).text(fmt(variance));
    if(isCash){ totCountedCash += counted; } else { totCountedOther += counted; }
  });
  $('#tot-counted-cash').text(fmt(totCountedCash));
  $('#tot-counted-other').text(fmt(totCountedOther));
  var cashVar = totCountedCash - num(expectedData.expected_cash);
  var othVar  = totCountedOther - num(expectedData.expected_other);
  $('#tot-cash-variance').removeClass('pos neg zero').addClass(cashVar>0.001?'pos':(cashVar<-0.001?'neg':'zero')).text(fmt(cashVar));
  $('#tot-other-variance').removeClass('pos neg zero').addClass(othVar>0.001?'pos':(othVar<-0.001?'neg':'zero')).text(fmt(othVar));
}

function refreshExpected(){
  $.get(base_url+'cashier_shifts/expected_api', function(r){
    if(r.status === 'success'){
      expectedData = r.expected;
      renderMethods();
      toastr.success('Expected amounts refreshed.');
    } else {
      toastr.error(r.message || 'Could not refresh.');
    }
  }, 'json');
}

$(document).on('input', '.counted-input', recalc);

var cashDenoms = [1000,500,200,100,50,20,10,5,2,1,0.5,0.2,0.1,0.05];
function buildDenominationGrid(){
  var cashInput = $('#close-form .counted-input[data-cash="1"]').first();
  if(cashInput.length === 0){ $('#cash-denomination').hide(); return; }
  var html = '';
  cashDenoms.forEach(function(d){
    html += '<div class="cs-denom-item">';
    html += '  <div class="denom-lbl">'+d+'</div>';
    html += '  <input type="number" min="0" step="1" class="form-control denom-qty" data-denom="'+d+'" value="0">';
    html += '</div>';
  });
  $('#denom-grid').html(html);
  $('#cash-denomination').show();
}
function updateDenominationTotal(){
  var total = 0;
  $('.denom-qty').each(function(){
    var qty = num($(this).val());
    var denom = num($(this).attr('data-denom'));
    total += qty * denom;
  });
  $('#denom-total').text(fmt(total));
  var cashInput = $('#close-form .counted-input[data-cash="1"]').first();
  cashInput.val(total.toFixed(2)).trigger('input');
}
$(document).on('input', '.denom-qty', updateDenominationTotal);

$("#close-btn").on("click", function(){
  var btn = $(this); btn.attr('disabled', true);
  var counts = [];
  $('.cs-method-row').each(function(){
    var pt = $(this).find('.lbl').text();
    var counted = num($(this).find('.counted-input').val());
    counts.push({payment_type: pt, counted_amount: counted});
  });
  var data = {
    "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>",
    shift_id: $('#shift_id').val(),
    counts: JSON.stringify(counts),
    manager_pin: $('#manager_pin').val(),
    close_note: $('#close_note').val()
  };
  $.ajax({
    type:'POST', url: base_url+'cashier_shifts/close', data: data, dataType:'json',
    success: function(r){
      btn.attr('disabled', false);
      if(r.status === 'success'){
        var msg = 'Shift closed. Cash variance: '+fmt(r.cash_variance);
        if(r.cash_variance > 0.001){ toastr.warning(msg); }
        else if(r.cash_variance < -0.001){ toastr.error(msg); }
        else { toastr.success('Shift closed. Cash reconciled perfectly.'); }
        setTimeout(function(){ window.location.href = can_view_report ? base_url+'cashier_shifts/view/'+$('#shift_id').val() : base_url+'cashier_shifts/manage'; }, 1200);
      } else {
        toastr.error(r.message || 'Could not close shift.');
      }
    },
    error: function(){ btn.attr('disabled', false); toastr.error('Server error.'); }
  });
});

renderMethods();
</script>
<script>$(".cashier-shift-close-active-li").addClass("active");</script>

<style>
/* The page JS appends its loading .overlay to $(".box"), so the .box wrapper
   must stay; only its own frame is stripped so the card is not flush against
   a second border. */
.box.mp-items-box { border: none !important; background: transparent !important; box-shadow: none !important; border-radius: 0 !important; }
.mp-form-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; padding: 16px 20px; border-top: 1px solid var(--mp-border); background: var(--mp-bg); border-radius: 0 0 16px 16px; }
</style>
