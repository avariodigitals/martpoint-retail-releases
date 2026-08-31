<!-- Cashier Shift Modals for POS -->
<style>
  .cs-shift-modal .modal-dialog { margin-top: 60px; }
  .cs-shift-modal .modal-content {
    border-radius: 16px;
    border: none;
    box-shadow: 0 20px 40px -10px rgba(0,0,0,0.18);
  }
  .cs-shift-modal .modal-header {
    background: #fff;
    border-bottom: 1px solid #F3F4F6;
    border-radius: 16px 16px 0 0;
    padding: 20px 24px;
  }
  .cs-shift-modal.open-shift .modal-header { border-left: 5px solid #2563EB; }
  .cs-shift-modal.close-shift .modal-header { border-left: 5px solid #DC2626; }
  .cs-shift-modal .modal-title { font-weight: 700; font-size: 18px; color: #111827; }
  .cs-shift-modal .close { font-size: 24px; color: #6B7280; opacity: 1; }
  .cs-shift-modal .close:hover { color: #111827; }
  .cs-shift-modal .modal-body { padding: 24px; }

  .cs-shift-form .form-group { margin-bottom: 20px; }
  .cs-shift-form label { font-weight: 600; font-size: 14px; color: #374151; margin-bottom: 8px; }
  .cs-shift-form .form-control {
    border: 1px solid #D1D5DB;
    border-radius: 10px;
    min-height: 48px;
    padding: 11px 14px;
    font-size: 15px;
    color: #111827;
    box-shadow: none;
  }
  .cs-shift-form .form-control:focus { border-color: #2563EB; box-shadow: 0 0 0 4px rgba(37,99,235,0.10); }
  .cs-shift-form .input-group-addon {
    background: #F9FAFB;
    border: 1px solid #D1D5DB;
    border-right: none;
    border-radius: 10px 0 0 10px;
    color: #6B7280;
    min-width: 46px;
    font-size: 15px;
  }
  .cs-shift-form .input-group .form-control { border-radius: 0 10px 10px 0; }
  .cs-shift-form select.form-control { height: 48px; }

  .cs-shift-close-grid { border: 1px solid #E5E7EB; border-radius: 12px; overflow: hidden; margin-bottom: 18px; }
  .cs-method-row {
    display: grid;
    grid-template-columns: 1.6fr 1fr 1fr 1fr;
    gap: 12px;
    align-items: center;
    padding: 14px 16px;
    border-bottom: 1px solid #F3F4F6;
    background: #fff;
  }
  .cs-method-row:last-child { border-bottom: none; }
  .cs-method-row.header { background: #F9FAFB; font-weight: 700; color: #6B7280; font-size: 11px; text-transform: uppercase; letter-spacing: 0.4px; }
  .cs-method-row .lbl { font-weight: 600; color: #111827; font-size: 14px; }
  .cs-method-row .sub { font-size: 12px; color: #9CA3AF; margin-top: 2px; }
  .cs-method-row .exp { font-size: 14px; color: #111827; font-weight: 500; }
  .cs-method-row input { border-radius: 8px; text-align: right; font-size: 15px; padding: 9px 12px; }
  .cs-variance { text-align: right; font-weight: 700; font-size: 14px; }
  .cs-variance.pos { color: #10B981; }
  .cs-variance.neg { color: #EF4444; }
  .cs-variance.zero { color: #6B7280; }

  .cs-totals { background: #F9FAFB; border-radius: 12px; padding: 18px; }
  .cs-totals .row-line { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; color: #4B5563; }
  .cs-totals .row-line.big { font-size: 17px; font-weight: 700; color: #111827; border-top: 1px solid #E5E7EB; margin-top: 8px; padding-top: 12px; }

  .cs-shift-btn {
    width: 100%;
    min-height: 52px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    padding: 14px;
    border: none;
  }
  .cs-shift-btn-primary { background: #2563EB; color: #fff; }
  .cs-shift-btn-primary:hover { background: #1D4ED8; color: #fff; }
  .cs-shift-btn-danger { background: #DC2626; color: #fff; }
  .cs-shift-btn-danger:hover { background: #B91C1C; color: #fff; }

  .cs-info-note { font-size: 13px; color: #6B7280; margin-bottom: 16px; }
  .cs-info-note a { color: #2563EB; }

  .cs-cash-denomination { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 12px; padding: 18px; margin-top: 18px; }
  .cs-cash-denomination h5 { margin: 0 0 14px; font-weight: 700; color: #111827; }
  .cs-denom-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
  @media (max-width: 768px) { .cs-denom-grid { grid-template-columns: repeat(2, 1fr); } }
  .cs-denom-item { background: #fff; border: 1px solid #E5E7EB; border-radius: 8px; padding: 10px; }
  .cs-denom-item .denom-lbl { font-size: 12px; color: #6B7280; margin-bottom: 4px; }
  .cs-denom-item input { width: 100%; border-radius: 8px; border: 1px solid #D1D5DB; padding: 8px; font-size: 14px; text-align: right; }
  .cs-denom-total { margin-top: 14px; padding-top: 12px; border-top: 1px solid #E5E7EB; font-size: 16px; font-weight: 700; text-align: right; color: #111827; }

  @media (max-width: 768px) {
    .cs-method-row { grid-template-columns: 1fr 1fr; }
    .cs-method-row .lbl-col { grid-column: 1 / -1; }
    .cs-method-row .exp { text-align: left; }
    .cs-method-row input { text-align: left; }
    .cs-variance { text-align: left; }
  }
</style>

<div class="modal fade cs-shift-modal open-shift" id="openShiftModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-play-circle text-blue"></i> Open Cashier Shift</h4>
      </div>
      <div class="modal-body cs-shift-form">
        <form id="pos-open-shift-form" onkeypress="return event.keyCode != 13;">
          <input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
          <div class="form-group">
            <label for="pos_till_id">Select Till</label>
            <select class="form-control" id="pos_till_id" name="till_id" required>
              <option value="">— Choose a till —</option>
              <?php if(!empty($tills) && is_array($tills) && count($tills) > 0): foreach($tills as $t): ?>
              <option value="<?=intval($t->id);?>"><?=htmlspecialchars($t->till_name);?> <?=($t->first_name || $t->last_name) ? '— '.htmlspecialchars(trim($t->first_name.' '.$t->last_name)) : ($t->account_name ? '('.$t->account_name.')' : '');?> <?=($t->is_default ? '(Default)' : '');?></option>
              <?php endforeach; else: ?>
              <option value="" disabled>No active tills. Ask an admin to create one.</option>
              <?php endif; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="pos_opening_float">Opening Cash Float</label>
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-money"></i></span>
              <input type="number" step="0.01" min="0" class="form-control" id="pos_opening_float" name="opening_float" value="0" required>
            </div>
          </div>
          <button type="button" id="posOpenShiftBtn" class="btn cs-shift-btn cs-shift-btn-primary" <?=(!empty($tills) && count($tills) === 0) ? 'disabled' : '';?>><i class="fa fa-play"></i> Open Shift</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php if(!empty($open_shift) && !empty($expected)): ?>
<div class="modal fade cs-shift-modal close-shift" id="closeShiftModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-lock text-red"></i> Count &amp; Close Shift <?=htmlspecialchars($open_shift->shift_code);?></h4>
      </div>
      <div class="modal-body cs-shift-form">
        <div class="cs-info-note">
          <i class="fa fa-info-circle"></i> Count the actual cash and slips in the till. Expected amounts are computed from sales attributed to you during this shift.
          <a href="javascript:refreshShiftExpected();"><i class="fa fa-refresh"></i> Refresh</a>
        </div>
        <input type="hidden" id="pos_close_shift_id" value="<?=intval($open_shift->id);?>">

        <div class="cs-shift-close-grid">
          <div class="cs-method-row header">
            <div>Payment Method</div>
            <div class="text-right">Expected</div>
            <div class="text-right">Counted</div>
            <div class="text-right">Variance</div>
          </div>
          <div id="pos-close-method-rows"></div>
        </div>

        <div class="cs-cash-denomination" id="pos-cash-denomination" style="display:none;">
          <h5><i class="fa fa-calculator text-blue"></i> Cash Denomination Count</h5>
          <div class="cs-denom-grid" id="pos-denom-grid"></div>
          <div class="cs-denom-total">Total Counted: <span id="pos-denom-total">0.00</span></div>
        </div>

        <div class="cs-totals">
          <div class="row-line"><span>Total Cash Expected</span><span id="pos-tot-expected-cash">0.00</span></div>
          <div class="row-line"><span>Total Cash Counted</span><span id="pos-tot-counted-cash">0.00</span></div>
          <div class="row-line big"><span>Cash Variance</span><span id="pos-tot-cash-variance" class="cs-variance zero">0.00</span></div>
          <div class="row-line" style="margin-top:8px;"><span>Non-Cash Expected</span><span id="pos-tot-expected-other">0.00</span></div>
          <div class="row-line"><span>Non-Cash Counted</span><span id="pos-tot-counted-other">0.00</span></div>
          <div class="row-line big"><span>Non-Cash Variance</span><span id="pos-tot-other-variance" class="cs-variance zero">0.00</span></div>
        </div>

        <div class="row" style="margin-top:18px;">
          <div class="col-sm-6">
            <div class="form-group">
              <label for="pos_close_note">Notes <small class="text-muted">(optional)</small></label>
              <textarea class="form-control" id="pos_close_note" rows="2" placeholder="e.g. Short by 200 — gave wrong change on invoice #12" style="border-radius:10px;resize:none;"></textarea>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label for="pos_manager_pin">Manager Sign-off PIN <small class="text-muted">(optional)</small></label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user-secret"></i></span>
                <input type="password" class="form-control" id="pos_manager_pin" placeholder="Leave blank if not required" style="border-radius:0 10px 10px 0;">
              </div>
            </div>
          </div>
        </div>
        <button type="button" id="posCloseShiftBtn" class="btn cs-shift-btn cs-shift-btn-danger"><i class="fa fa-lock"></i> Confirm &amp; Close Shift</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
var shiftExpectedData = <?=json_encode($expected ?? array());?>;
var shiftBaseUrl = '<?=base_url();?>';

function fmt(n){ return (n*1).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }
function num(v){ var n = parseFloat(v); return isNaN(n) ? 0 : n; }

function htmlspecialchars(s){ return String(s).replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]; }); }

function renderShiftMethods(){
  var methods = shiftExpectedData.methods || [];
  if(methods.length === 0){
    methods = [{payment_type:'cash', affects_cash:1, expected: parseFloat(shiftExpectedData.expected_cash || 0), txn_count:0}];
  }
  var html = '';
  methods.forEach(function(m){
    var isCash = (m.affects_cash == 1 || m.affects_cash === '1' || m.affects_cash === true);
    var cashFlag = isCash ? 1 : 0;
    html += '<div class="cs-method-row" data-cash="'+cashFlag+'">';
    html += '  <div class="lbl-col"><div class="lbl">'+htmlspecialchars(m.payment_type)+'</div>';
    html += '  <div class="sub">'+(isCash ? 'Cash (includes opening float)' : 'Bank / POS')+(m.txn_count ? ' &middot; '+m.txn_count+' txn' : '')+'</div></div>';
    html += '  <div class="exp text-right">'+fmt(m.expected)+'</div>';
    html += '  <div><input type="number" step="0.01" min="0" class="form-control counted-input" data-expected="'+m.expected+'" data-cash="'+cashFlag+'" value="'+(isCash ? parseFloat(m.expected).toFixed(2) : '0.00')+'"></div>';
    html += '  <div class="cs-variance zero" data-var>0.00</div>';
    html += '</div>';
  });
  $('#pos-close-method-rows').html(html);
  recalcShift();
  buildDenominationGrid();
}

function recalcShift(){
  var totCountedCash = 0, totCountedOther = 0;
  $('#closeShiftModal .counted-input').each(function(){
    var counted = num($(this).val());
    var expected = num($(this).attr('data-expected'));
    var isCash = ($(this).attr('data-cash') === '1');
    var variance = counted - expected;
    var vEl = $(this).closest('.cs-method-row').find('[data-var]');
    vEl.removeClass('pos neg zero').addClass(variance>0.001?'pos':(variance<-0.001?'neg':'zero')).text(fmt(variance));
    if(isCash){ totCountedCash += counted; } else { totCountedOther += counted; }
  });
  $('#pos-tot-counted-cash').text(fmt(totCountedCash));
  $('#pos-tot-counted-other').text(fmt(totCountedOther));
  var cashVar = totCountedCash - num(shiftExpectedData.expected_cash || 0);
  var othVar  = totCountedOther - num(shiftExpectedData.expected_other || 0);
  $('#pos-tot-cash-variance').removeClass('pos neg zero').addClass(cashVar>0.001?'pos':(cashVar<-0.001?'neg':'zero')).text(fmt(cashVar));
  $('#pos-tot-other-variance').removeClass('pos neg zero').addClass(othVar>0.001?'pos':(othVar<-0.001?'neg':'zero')).text(fmt(othVar));
}

var cashDenoms = [1000,500,200,100,50,20,10,5,2,1,0.5,0.2,0.1,0.05];
function buildDenominationGrid(){
  var cashInput = $('#closeShiftModal .counted-input[data-cash="1"]').first();
  if(cashInput.length === 0){ $('#pos-cash-denomination').hide(); return; }
  var html = '';
  cashDenoms.forEach(function(d){
    html += '<div class="cs-denom-item">';
    html += '  <div class="denom-lbl">'+d+'</div>';
    html += '  <input type="number" min="0" step="1" class="form-control denom-qty" data-denom="'+d+'" value="0">';
    html += '</div>';
  });
  $('#pos-denom-grid').html(html);
  $('#pos-cash-denomination').show();
}
function updateDenominationTotal(){
  var total = 0;
  $('.denom-qty').each(function(){
    var qty = num($(this).val());
    var denom = num($(this).attr('data-denom'));
    total += qty * denom;
  });
  $('#pos-denom-total').text(fmt(total));
  var cashInput = $('#closeShiftModal .counted-input[data-cash="1"]').first();
  cashInput.val(total.toFixed(2)).trigger('input');
}

function refreshShiftExpected(){
  $.get(shiftBaseUrl+'cashier_shifts/expected_api', function(r){
    if(r.status === 'success'){
      shiftExpectedData = r.expected;
      renderShiftMethods();
      toastr.success('Expected amounts refreshed.');
    } else {
      toastr.error(r.message || 'Could not refresh.');
    }
  }, 'json');
}

$(document).on('input', '#closeShiftModal .counted-input', recalcShift);
$(document).on('input', '#closeShiftModal .denom-qty', updateDenominationTotal);

$('#closeShiftModal').on('shown.bs.modal', function(){
  if(shiftExpectedData && shiftExpectedData.methods) renderShiftMethods();
});

$('#posOpenShiftBtn').on('click', function(){
  var btn = $(this); btn.attr('disabled', true);
  var data = new FormData($('#pos-open-shift-form')[0]);
  $.ajax({
    type:'POST', url: shiftBaseUrl+'cashier_shifts/open', data: data,
    cache:false, contentType:false, processData:false, dataType:'json',
    success: function(r){
      btn.attr('disabled', false);
      if(r.status === 'success'){
        toastr.success('Shift '+r.shift_code+' opened.');
        setTimeout(function(){ location.reload(); }, 800);
      } else {
        toastr.error(r.message || 'Could not open shift.');
      }
    },
    error: function(){ btn.attr('disabled', false); toastr.error('Server error.'); }
  });
});

$('#posCloseShiftBtn').on('click', function(){
  var btn = $(this); btn.attr('disabled', true);
  var counts = [];
  $('#closeShiftModal .cs-method-row').each(function(){
    if($(this).hasClass('header')) return;
    var pt = $(this).find('.lbl').text();
    var counted = num($(this).find('.counted-input').val());
    counts.push({payment_type: pt, counted_amount: counted});
  });
  var data = {
    '<?=$this->security->get_csrf_token_name();?>': '<?=$this->security->get_csrf_hash();?>',
    shift_id: $('#pos_close_shift_id').val(),
    counts: JSON.stringify(counts),
    manager_pin: $('#pos_manager_pin').val(),
    close_note: $('#pos_close_note').val()
  };
  $.ajax({
    type:'POST', url: shiftBaseUrl+'cashier_shifts/close', data: data, dataType:'json',
    success: function(r){
      btn.attr('disabled', false);
      if(r.status === 'success'){
        var msg = 'Shift closed. Cash variance: '+fmt(r.cash_variance);
        if(r.cash_variance > 0.001){ toastr.warning(msg); }
        else if(r.cash_variance < -0.001){ toastr.error(msg); }
        else { toastr.success('Shift closed. Cash reconciled perfectly.'); }
        setTimeout(function(){ location.reload(); }, 1200);
      } else {
        toastr.error(r.message || 'Could not close shift.');
      }
    },
    error: function(){ btn.attr('disabled', false); toastr.error('Server error.'); }
  });
});

<?php if(!empty($open_shift) && !empty($expected)): ?>
$(function(){ renderShiftMethods(); });
<?php endif; ?>
<?php if(!empty($tills) && count($tills) === 1): ?>
$('#pos_till_id option:not([value=""])').first().prop('selected', true);
<?php endif; ?>
</script>